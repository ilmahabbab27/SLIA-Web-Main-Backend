<?php

namespace App\Http\Controllers;

use App\Models\SliaMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use ZipArchive;

class SliaMemberController extends Controller
{
    private const PORTAL_ALLOWED_TYPES = ['Associate', 'Fellow', 'Honorary Fellow'];
    private const PASSWORD_OTP_TTL_MINUTES = 10;

    private function sanitizeMember(SliaMember $member): array
    {
        return $member->makeHidden(['password'])->toArray();
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'membership_number' => 'required_without:username|string',
            'username' => 'required_without:membership_number|string',
            'password' => 'required|string',
        ]);

        $identifier = trim($validated['membership_number'] ?? $validated['username']);
        $normalizedIdentifier = mb_strtolower($identifier);
        $member = SliaMember::query()
            ->where(function ($query) use ($normalizedIdentifier) {
                $query->whereRaw('LOWER(TRIM(membership_number)) = ?', [$normalizedIdentifier])
                    ->orWhereRaw('LOWER(TRIM(username)) = ?', [$normalizedIdentifier]);
            })
            ->first();

        if (!$member || empty($member->password) || !Hash::check($validated['password'], $member->password)) {
            return response()->json(['message' => 'Invalid username or membership number, or password.'], 422);
        }

        if (!in_array($member->membership_type, self::PORTAL_ALLOWED_TYPES, true)) {
            return response()->json(['message' => 'This membership type cannot use the portal.'], 403);
        }

        return response()->json([
            'message' => 'Login successful',
            'data' => $this->sanitizeMember($member),
        ]);
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'membership_number' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:8',
            'username' => 'nullable|string',
        ]);

        $member = SliaMember::query()
            ->where('membership_number', $validated['membership_number'])
            ->orWhere('username', $validated['membership_number'])
            ->first();

        if (!$member) {
            return response()->json(['message' => 'No member record found for that membership number.'], 404);
        }

        if (!in_array($member->membership_type, self::PORTAL_ALLOWED_TYPES, true)) {
            return response()->json(['message' => 'Only Associate members and above can sign up for the portal.'], 403);
        }

        if (!empty($member->password)) {
            return response()->json(['message' => 'An account already exists for that membership number.'], 422);
        }

        $member->username = $validated['username'] ?? $validated['membership_number'];
        $member->slia_contact_email = $validated['email'];
        $member->password = Hash::make($validated['password']);
        $member->save();

        $primaryEmail = collect([
            $member->office_email,
            $member->home_email,
            $member->contact_info_1_email,
            $member->contact_info_2_email,
            $member->slia_contact_email,
        ])->first(fn ($value) => !empty($value));

        if ($primaryEmail) {
            Mail::raw(
                "Your SLIA portal account has been created successfully.\n\n"
                . "Membership Number: {$member->membership_number}\n"
                . "Username: {$member->username}\n\n"
                . "SLIA Contact Email: {$member->slia_contact_email}\n\n"
                . "Please keep your password private.",
                function ($message) use ($primaryEmail, $member) {
                    $message->to($primaryEmail)
                        ->cc('sliageneralsec@gmail.com')
                        ->subject('SLIA Portal Signup Confirmation')
                        ->from(config('mail.from.address'), config('mail.from.name'))
                        ->replyTo($member->office_email ?: config('mail.from.address'));
                }
            );
        }

        return response()->json([
            'message' => 'Registration successful',
            'data' => $this->sanitizeMember($member),
        ], 201);
    }

    public function sendRegistrationOtp(Request $request)
    {
        $validated = $request->validate([
            'membership_number' => 'required|string',
            'email' => 'required|email',
        ]);

        $membershipNumber = trim($validated['membership_number']);
        $email = trim($validated['email']);
        $member = SliaMember::query()
            ->whereRaw('LOWER(TRIM(membership_number)) = ?', [mb_strtolower($membershipNumber)])
            ->first();

        if (!$member) {
            return response()->json(['message' => 'No member record found for that membership number.'], 404);
        }

        if (!in_array($member->membership_type, self::PORTAL_ALLOWED_TYPES, true)) {
            return response()->json(['message' => 'Only Associate members and above can use the portal.'], 403);
        }

        if (!empty($member->password)) {
            return response()->json(['message' => 'An account already exists. Please use Sign In or Forgot Password.'], 422);
        }

        $otp = (string) random_int(100000, 999999);
        Cache::put($this->registrationOtpCacheKey($member), [
            'otp' => Hash::make($otp),
            'email' => $email,
        ], now()->addMinutes(self::PASSWORD_OTP_TTL_MINUTES));

        Mail::raw(
            "Your SLIA portal registration code is: {$otp}\n\nThis code expires in " . self::PASSWORD_OTP_TTL_MINUTES . " minutes.",
            function ($message) use ($email, $member) {
                $message->to($email)
                    ->subject('SLIA Portal Registration Code')
                    ->from(config('mail.from.address'), config('mail.from.name'))
                    ->replyTo($member->office_email ?: config('mail.from.address'));
            }
        );

        return response()->json(['message' => 'Registration code sent successfully.']);
    }

    public function verifyRegistrationOtp(Request $request)
    {
        $validated = $request->validate([
            'membership_number' => 'required|string',
            'email' => 'required|email',
            'otp' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $membershipNumber = trim($validated['membership_number']);
        $email = trim($validated['email']);
        $member = SliaMember::query()
            ->whereRaw('LOWER(TRIM(membership_number)) = ?', [mb_strtolower($membershipNumber)])
            ->first();

        if (!$member) {
            return response()->json(['message' => 'No member record found for that membership number.'], 404);
        }

        if (!in_array($member->membership_type, self::PORTAL_ALLOWED_TYPES, true)) {
            return response()->json(['message' => 'Only Associate members and above can use the portal.'], 403);
        }

        if (!empty($member->password)) {
            return response()->json(['message' => 'An account already exists. Please use Sign In or Forgot Password.'], 422);
        }

        $payload = Cache::get($this->registrationOtpCacheKey($member));
        if (!$payload || mb_strtolower($payload['email'] ?? '') !== mb_strtolower($email) || !Hash::check($validated['otp'], $payload['otp'] ?? '')) {
            return response()->json(['message' => 'Invalid or expired registration code.'], 422);
        }

        $member->username = $member->username ?: $member->membership_number;
        $member->slia_contact_email = $email;
        $member->password = Hash::make($validated['password']);
        $member->save();
        Cache::forget($this->registrationOtpCacheKey($member));

        return response()->json([
            'message' => 'Registration successful',
            'data' => $this->sanitizeMember($member),
        ], 201);
    }

    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'membership_number' => 'required|string',
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $member = SliaMember::query()
            ->where('membership_number', $validated['membership_number'])
            ->orWhere('username', $validated['membership_number'])
            ->first();

        if (!$member || empty($member->password) || !Hash::check($validated['current_password'], $member->password)) {
            return response()->json(['message' => 'Invalid membership number or current password.'], 422);
        }

        if (!in_array($member->membership_type, self::PORTAL_ALLOWED_TYPES, true)) {
            return response()->json(['message' => 'This membership type cannot use the portal.'], 403);
        }

        $member->password = Hash::make($validated['password']);
        $member->save();

        return response()->json([
            'message' => 'Password updated successfully',
            'data' => $this->sanitizeMember($member),
        ]);
    }

    public function sendPasswordOtp(Request $request)
    {
        $validated = $request->validate([
            'membership_number' => 'required|string',
            'email' => 'required|email',
        ]);

        $identifier = trim($validated['membership_number']);
        $email = trim($validated['email']);
        $member = SliaMember::query()
            ->where(function ($query) use ($identifier) {
                $normalizedIdentifier = mb_strtolower($identifier);
                $query->whereRaw('LOWER(TRIM(membership_number)) = ?', [$normalizedIdentifier])
                    ->orWhereRaw('LOWER(TRIM(username)) = ?', [$normalizedIdentifier]);
            })
            ->first();

        if (!$member) {
            return response()->json(['message' => 'No member record found for that membership number.'], 404);
        }

        if (!in_array($member->membership_type, self::PORTAL_ALLOWED_TYPES, true)) {
            return response()->json(['message' => 'This membership type cannot use the portal.'], 403);
        }

        $knownEmails = array_filter([
            $member->office_email,
            $member->home_email,
            $member->contact_info_1_email,
            $member->contact_info_2_email,
            $member->slia_contact_email,
        ]);

        $emailMatches = collect($knownEmails)->contains(fn ($knownEmail) => mb_strtolower(trim($knownEmail)) === mb_strtolower($email));
        if (!$emailMatches) {
            return response()->json(['message' => 'The email does not match our records.'], 422);
        }

        $otp = (string) random_int(100000, 999999);
        Cache::put($this->passwordOtpCacheKey($member), [
            'otp' => Hash::make($otp),
            'email' => mb_strtolower($email),
        ], now()->addMinutes(self::PASSWORD_OTP_TTL_MINUTES));

        Mail::raw(
            "Your SLIA password reset OTP is: {$otp}\n\nThis code expires in " . self::PASSWORD_OTP_TTL_MINUTES . " minutes.",
            function ($message) use ($email, $member) {
                $message->to($email)
                    ->subject('SLIA Password Reset OTP')
                    ->from(config('mail.from.address'), config('mail.from.name'))
                    ->replyTo($member->office_email ?: config('mail.from.address'));
            }
        );

        return response()->json([
            'message' => 'OTP sent successfully.',
            'data' => [
                'expires_in_minutes' => self::PASSWORD_OTP_TTL_MINUTES,
            ],
        ]);
    }

    public function verifyPasswordOtp(Request $request)
    {
        $validated = $request->validate([
            'membership_number' => 'required|string',
            'email' => 'required|email',
            'otp' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $identifier = trim($validated['membership_number']);
        $email = trim($validated['email']);
        $member = SliaMember::query()
            ->where(function ($query) use ($identifier) {
                $normalizedIdentifier = mb_strtolower($identifier);
                $query->whereRaw('LOWER(TRIM(membership_number)) = ?', [$normalizedIdentifier])
                    ->orWhereRaw('LOWER(TRIM(username)) = ?', [$normalizedIdentifier]);
            })
            ->first();

        if (!$member) {
            return response()->json(['message' => 'No member record found for that membership number.'], 404);
        }

        if (!in_array($member->membership_type, self::PORTAL_ALLOWED_TYPES, true)) {
            return response()->json(['message' => 'This membership type cannot use the portal.'], 403);
        }

        $payload = Cache::get($this->passwordOtpCacheKey($member));
        if (!$payload || ($payload['email'] ?? null) !== mb_strtolower($email) || !Hash::check($validated['otp'], $payload['otp'] ?? '')) {
            return response()->json(['message' => 'Invalid or expired OTP.'], 422);
        }

        $member->username = $member->username ?: $validated['membership_number'];
        $member->password = Hash::make($validated['password']);
        $member->save();
        Cache::forget($this->passwordOtpCacheKey($member));

        return response()->json([
            'message' => 'Password updated successfully',
            'data' => $this->sanitizeMember($member),
        ]);
    }

    public function index(Request $request)
    {
        $query = SliaMember::query();

        // Search
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('practice_name', 'like', "%{$search}%")
                  ->orWhere('office_email', 'like', "%{$search}%")
                  ->orWhere('location_district', 'like', "%{$search}%");
            });
        }

        // Filter by membership type
        if ($request->has('membership_type') && $request->input('membership_type') !== 'all') {
            $query->where('membership_type', $request->input('membership_type'));
        }

        // Filter by district
        if ($request->has('district') && $request->input('district') !== '') {
            $query->where('location_district', $request->input('district'));
        }

        // Filter by province
        if ($request->has('province') && $request->input('province') !== '') {
            $query->where('location_province', $request->input('province'));
        }

        // Sorting
        $sort = $request->input('sort', 'name');
        switch ($sort) {
            case 'name':
                $query->orderBy('full_name');
                break;
            case 'district':
                $query->orderBy('location_district');
                break;
            case 'newest':
                $query->latest();
                break;
            default:
                $query->orderBy('full_name');
        }

        $members = $query->paginate($request->input('per_page', 20));

        return response()->json($members);
    }

    public function show($id)
    {
        $member = SliaMember::find($id);

        if (!$member) {
            return response()->json(['message' => 'Member not found'], 404);
        }

        return response()->json($member);
    }

    public function getForArchitectFinder(Request $request)
    {
        $query = SliaMember::query();

        // Only show members with office contact info
        $query->whereNotNull('office_email')
              ->whereNotNull('office_address')
              ->whereIn('membership_type', self::PORTAL_ALLOWED_TYPES);

        // Search
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('practice_name', 'like', "%{$search}%")
                  ->orWhere('location_district', 'like', "%{$search}%");
            });
        }

        // Filter by district
        if ($request->has('district') && $request->input('district') !== '') {
            $query->where('location_district', $request->input('district'));
        }

        // Filter by membership type
        if ($request->has('membership_type') && $request->input('membership_type') !== 'all') {
            $query->where('membership_type', $request->input('membership_type'));
        }

        $members = $query->orderBy('full_name')->paginate($request->input('per_page', 12));

        return response()->json($members);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->textMemberRules(true));

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $member = SliaMember::create($validated);

        return response()->json($member, 201);
    }

    public function update(Request $request, $id)
    {
        $member = SliaMember::find($id);

        if (!$member) {
            return response()->json(['message' => 'Member not found'], 404);
        }

        $validated = $request->validate($this->textMemberRules(false));

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $member->update($validated);

        return response()->json($member);
    }

    public function destroy($id)
    {
        $member = SliaMember::find($id);

        if (!$member) {
            return response()->json(['message' => 'Member not found'], 404);
        }

        $member->delete();

        return response()->json(['message' => 'Member deleted successfully']);
    }

    public function import(Request $request)
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx', 'max:10240'],
            'replace' => ['nullable', 'boolean'],
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();
        $extension = strtolower($file->getClientOriginalExtension());
        $rows = $extension === 'xlsx' ? $this->readXlsx($path) : $this->readCsv($path);

        if (!empty($validated['replace'])) {
            SliaMember::query()->delete();
        }

        $imported = 0;
        foreach ($rows as $row) {
            if (empty($row['full_name'])) {
                continue;
            }

            if (!empty($row['password'])) {
                $row['password'] = Hash::make($row['password']);
            }

            if (!empty($row['membership_number'])) {
                SliaMember::updateOrCreate(
                    ['membership_number' => $row['membership_number']],
                    $row
                );
            } elseif (!empty($row['username'])) {
                SliaMember::updateOrCreate(
                    ['username' => $row['username']],
                    $row
                );
            } else {
                SliaMember::create($row);
            }
            $imported++;
        }

        return response()->json([
            'imported' => $imported,
            'parsed' => count($rows),
        ]);
    }

    private function readCsv(string $path): array
    {
        $contents = file_get_contents($path);
        if ($contents === false || trim($contents) === '') {
            return [];
        }

        $contents = preg_replace('/^\xEF\xBB\xBF/', '', $contents);
        $lines = preg_split("/\r\n|\n|\r/", trim($contents)) ?: [];
        if (count($lines) < 2) {
            return [];
        }

        $delimiter = $this->detectCsvDelimiter($lines[0]);
        $headers = str_getcsv(array_shift($lines), $delimiter);
        $headers = array_map(fn ($header) => strtolower(trim((string) preg_replace('/^\xEF\xBB\xBF/', '', (string) $header))), $headers);

        $rows = [];
        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }

            $values = str_getcsv($line, $delimiter);
            $row = [];
            foreach ($headers as $index => $header) {
                $row[$header] = trim((string) ($values[$index] ?? ''));
            }

            if (!empty(array_filter($row, fn ($value) => $value !== ''))) {
                $rows[] = $this->normalizeImportRow($row);
            }
        }

        return $rows;
    }

    private function detectCsvDelimiter(string $line): string
    {
        $delimiters = ["," => substr_count($line, ","), ";" => substr_count($line, ";"), "\t" => substr_count($line, "\t")];
        arsort($delimiters);

        $best = array_key_first($delimiters);
        return $delimiters[$best] > 0 ? $best : ",";
    }

    public function readXlsx(string $path): array
    {
        if (!class_exists(ZipArchive::class)) {
            abort(response()->json(['message' => 'XLSX import requires the PHP zip extension. Enable extension=zip in php.ini and restart the Laravel server.'], 422));
        }

        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            abort(response()->json(['message' => 'Could not open Excel workbook.'], 422));
        }

        $sharedStrings = $this->xlsxSharedStrings($zip);
        $sheetPath = $this->xlsxFirstSheetPath($zip);
        if (!$sheetPath) {
            $zip->close();
            return [];
        }

        $sheetXml = simplexml_load_string($zip->getFromName($sheetPath));
        if ($sheetXml === false) {
            $zip->close();
            abort(response()->json(['message' => 'Could not read Excel worksheet.'], 422));
        }

        $sheetXml->registerXPathNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $rows = [];
        $headers = [];
        foreach ($sheetXml->xpath('//x:row') as $row) {
            $values = $this->xlsxRowValues($row, $sharedStrings);
            if ((int) $row['r'] === 1) {
                $headers = array_map(fn ($header) => strtolower(trim((string) $header)), $values);
                continue;
            }

            $mapped = [];
            foreach ($headers as $i => $header) {
                $mapped[$header] = trim((string) ($values[$i] ?? ''));
            }

            if (!empty(array_filter($mapped, fn ($value) => $value !== ''))) {
                $rows[] = $this->normalizeImportRow($mapped);
            }
        }

        $zip->close();
        return $rows;
    }

    private function xlsxFirstSheetPath(ZipArchive $zip): ?string
    {
        // The directory workbook stores its primary member list in sheet1.
        if ($zip->getFromName('xl/worksheets/sheet1.xml') !== false) {
            return 'xl/worksheets/sheet1.xml';
        }

        $workbook = simplexml_load_string($zip->getFromName('xl/workbook.xml'));
        $rels = simplexml_load_string($zip->getFromName('xl/_rels/workbook.xml.rels'));

        if ($workbook === false || $rels === false || empty($workbook->sheets->sheet)) {
            return null;
        }

        $firstSheet = $workbook->sheets->sheet[0];
        $relId = (string) $firstSheet['r:id'];
        foreach ($rels->Relationship as $relationship) {
            if ((string) $relationship['Id'] === $relId) {
                return 'xl/' . ltrim((string) $relationship['Target'], '/');
            }
        }

        return 'xl/worksheets/sheet1.xml';
    }

    private function xlsxSharedStrings(ZipArchive $zip): array
    {
        $xml = $zip->getFromName('xl/sharedStrings.xml');
        if ($xml === false) {
            return [];
        }

        $strings = [];
        $shared = simplexml_load_string($xml);
        if ($shared === false) {
            return [];
        }

        $shared->registerXPathNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        foreach ($shared->xpath('//x:si') as $si) {
            $parts = [];
            $si->registerXPathNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
            foreach ($si->xpath('./x:t | ./x:r/x:t') as $text) {
                $parts[] = (string) $text;
            }
            $strings[] = implode('', $parts);
        }

        return $strings;
    }

    private function xlsxRowValues($row, array $sharedStrings): array
    {
        $values = [];
        $row->registerXPathNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        foreach ($row->xpath('./x:c') as $cell) {
            $cell->registerXPathNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
            $ref = (string) $cell['r'];
            $index = $this->xlsxColumnIndex($ref);
            $valueNode = $cell->xpath('./x:v');
            $value = $valueNode ? (string) $valueNode[0] : '';
            if ((string) $cell['t'] === 's') {
                $value = $sharedStrings[(int) $value] ?? '';
            }
            $values[$index] = $value;
        }
        ksort($values);
        $maxIndex = $values ? max(array_keys($values)) : -1;
        $normalized = [];
        for ($index = 0; $index <= $maxIndex; $index++) {
            $normalized[$index] = $values[$index] ?? '';
        }
        return $normalized;
    }

    private function xlsxColumnIndex(string $ref): int
    {
        preg_match('/([A-Z]+)(\d+)/', $ref, $matches);
        $letters = $matches[1] ?? 'A';
        $index = 0;
        foreach (str_split($letters) as $char) {
            $index = $index * 26 + (ord($char) - 64);
        }
        return $index - 1;
    }

    private function normalizeImportRow(array $row): array
    {
        $value = fn (array $keys) => collect($keys)
            ->map(fn ($key) => trim((string) ($row[$key] ?? '')))
            ->first(fn ($item) => $item !== '') ?: null;
        $source = $row;
        unset($source['password']);
        $membershipType = $value(['membership_type', 'mcategory']);
        $membershipType = match (strtolower((string) $membershipType)) {
            'student member' => 'Student',
            'graduate member' => 'Graduate',
            'associate member' => 'Associate',
            'fellow member' => 'Fellow',
            'honorary fellow member' => 'Honorary Fellow',
            default => $membershipType,
        };

        return [
            'full_name' => $value(['full_name', 'name', 'fname']),
            'name_with_initials' => $value(['name_with_initials', 'ininame']),
            'yearbook_name' => $value(['yearbook_name', 'displaynameonyearbook']),
            'nic_number' => $value(['nic_number', 'nic']),
            'gender' => $value(['gender']),
            'date_of_birth' => $value(['date_of_birth', 'dateofbirth']),
            'membership_type' => $membershipType,
            'membership_number' => $value(['membership_number', 'memno']),
            'membership_year' => $value(['membership_year', 'memyear']),
            'arb_number' => $value(['arb_number', 'arb']),
            'academic_qualifications' => $value(['academic_qualifications', 'pqaa']),
            'professional_qualifications' => $value(['professional_qualifications', 'aquaf']),
            'practice_name' => $value(['practice_name', 'pname']),
            'practice_description' => $value(['practice_description']),
            'practice_type' => $value(['practice_type', 'practice']),
            'location_district' => $value(['location_district', 'district']),
            'location_province' => $value(['location_province', 'district2']),
            'personal_website' => $value(['personal_website']),
            'office_address' => $value(['office_address', 'oadd']),
            'office_phone' => $value(['office_phone', 'otelone', 'oteltwo', 'otelthree', 'otelfour']),
            'office_fax' => $value(['office_fax', 'offfax']),
            'office_email' => $value(['office_email', 'oemail', 'oemailtwo']),
            'office_website' => $value(['office_website', 'oweb']),
            'home_address' => $value(['home_address', 'radd']),
            'home_phone' => $value(['home_phone', 'rtelone', 'rteltwo', 'rtelthree']),
            'home_fax' => $value(['home_fax', 'refax']),
            'home_email' => $value(['home_email', 'remail']),
            'slia_contact_email' => $value(['slia_contact_email']),
            'contact_info_1_address' => $value(['contact_info_1_address', 'oradd']),
            'contact_info_1_phone' => $value(['contact_info_1_phone', 'ortel']),
            'contact_info_1_email' => $value(['contact_info_1_email', 'oremail']),
            'contact_info_2_address' => $value(['contact_info_2_address', 'postaladdress']),
            'contact_info_2_email' => $value(['contact_info_2_email', 'postalemailone', 'postemailtwo']),
            'positions_held' => $value(['positions_held', 'opheld']),
            'slia_awards' => $value(['slia_awards', 'sliaawards']),
            'other_awards' => $value(['other_awards', 'aoawards']),
            'username' => $value(['username']),
            'password' => $value(['password']),
            'source_data' => json_encode($source, JSON_UNESCAPED_UNICODE),
        ];
    }

    private function textMemberRules(bool $creating): array
    {
        $rules = array_fill_keys(
            (new SliaMember())->getFillable(),
            ['sometimes', 'nullable', 'string', 'max:1000000']
        );
        $rules['full_name'] = $creating
            ? ['required', 'string', 'max:1000000']
            : ['sometimes', 'string', 'max:1000000'];
        unset($rules['source_data']);
        return $rules;
    }

    private function passwordOtpCacheKey(SliaMember $member): string
    {
        return 'slia_member_password_otp_' . $member->id;
    }

    private function registrationOtpCacheKey(SliaMember $member): string
    {
        return 'slia_member_registration_otp_' . $member->id;
    }

}
