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

        $identifier = $validated['membership_number'] ?? $validated['username'];
        $member = SliaMember::query()
            ->where('membership_number', $identifier)
            ->orWhere('username', $identifier)
            ->first();

        if (!$member || empty($member->password) || !Hash::check($validated['password'], $member->password)) {
            return response()->json(['message' => 'Invalid membership number or password.'], 422);
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
        $member->email = $validated['email'];
        $member->password = Hash::make($validated['password']);
        $member->save();

        $primaryEmail = collect([
            $member->office_email,
            $member->home_email,
            $member->contact_info_1_email,
            $member->contact_info_2_email,
            $member->email,
        ])->first(fn ($value) => !empty($value));

        if ($primaryEmail) {
            Mail::raw(
                "Your SLIA portal account has been created successfully.\n\n"
                . "Membership Number: {$member->membership_number}\n"
                . "Username: {$member->username}\n\n"
                . "Email: {$member->email}\n\n"
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

        $member = SliaMember::query()
            ->where('membership_number', $validated['membership_number'])
            ->orWhere('username', $validated['membership_number'])
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
            $member->email,
        ]);

        if (!in_array($validated['email'], $knownEmails, true)) {
            return response()->json(['message' => 'The email does not match our records.'], 422);
        }

        $otp = (string) random_int(100000, 999999);
        Cache::put($this->passwordOtpCacheKey($member), [
            'otp' => Hash::make($otp),
            'email' => $validated['email'],
        ], now()->addMinutes(self::PASSWORD_OTP_TTL_MINUTES));

        Mail::raw(
            "Your SLIA password reset OTP is: {$otp}\n\nThis code expires in " . self::PASSWORD_OTP_TTL_MINUTES . " minutes.",
            function ($message) use ($validated, $member) {
                $message->to($validated['email'])
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

        $member = SliaMember::query()
            ->where('membership_number', $validated['membership_number'])
            ->orWhere('username', $validated['membership_number'])
            ->first();

        if (!$member) {
            return response()->json(['message' => 'No member record found for that membership number.'], 404);
        }

        if (!in_array($member->membership_type, self::PORTAL_ALLOWED_TYPES, true)) {
            return response()->json(['message' => 'This membership type cannot use the portal.'], 403);
        }

        $payload = Cache::get($this->passwordOtpCacheKey($member));
        if (!$payload || ($payload['email'] ?? null) !== $validated['email'] || !Hash::check($validated['otp'], $payload['otp'] ?? '')) {
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
        $validated = $request->validate([
            'full_name' => 'required|string',
            'name_with_initials' => 'nullable|string',
            'yearbook_name' => 'nullable|string',
            'nic_number' => 'nullable|unique:slia_members',
            'gender' => 'nullable|string|in:Male,Female,Other',
            'date_of_birth' => 'nullable|date',
            'photo' => 'nullable|string',
            'membership_type' => 'nullable|string|in:Student,Graduate,Associate,Fellow,Honorary Fellow',
            'membership_number' => 'nullable|unique:slia_members',
            'membership_year' => 'nullable|string',
            'arb_number' => 'nullable|unique:slia_members',
            'academic_qualifications' => 'nullable|string',
            'professional_qualifications' => 'nullable|string',
            'practice_name' => 'nullable|string',
            'practice_description' => 'nullable|string',
            'practice_type' => 'nullable|string',
            'location_district' => 'nullable|string',
            'location_province' => 'nullable|string',
            'personal_website' => 'nullable|string',
            'office_address' => 'nullable|string',
            'office_district' => 'nullable|string',
            'office_province' => 'nullable|string',
            'office_phone' => 'nullable|string',
            'office_fax' => 'nullable|string',
            'office_email' => 'nullable|email|unique:slia_members,office_email',
            'office_website' => 'nullable|string',
            'home_address' => 'nullable|string',
            'home_district' => 'nullable|string',
            'home_province' => 'nullable|string',
            'home_phone' => 'nullable|string',
            'home_fax' => 'nullable|string',
            'home_email' => 'nullable|email|unique:slia_members,home_email',
            'contact_info_1_address' => 'nullable|string',
            'contact_info_1_phone' => 'nullable|string',
            'contact_info_1_email' => 'nullable|email|unique:slia_members,contact_info_1_email',
            'contact_info_2_address' => 'nullable|string',
            'contact_info_2_phone' => 'nullable|string',
            'contact_info_2_email' => 'nullable|email|unique:slia_members,contact_info_2_email',
            'positions_held' => 'nullable|string',
            'slia_awards' => 'nullable|string',
            'other_awards' => 'nullable|string',
            'username' => 'nullable|string',
            'password' => 'nullable|string',
        ]);

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

        $validated = $request->validate([
            'full_name' => 'sometimes|string',
            'name_with_initials' => 'sometimes|nullable|string',
            'yearbook_name' => 'sometimes|nullable|string',
            'nic_number' => 'sometimes|nullable|unique:slia_members,nic_number,' . $id,
            'gender' => 'sometimes|nullable|string|in:Male,Female,Other',
            'date_of_birth' => 'sometimes|nullable|date',
            'photo' => 'sometimes|nullable|string',
            'membership_type' => 'sometimes|nullable|string|in:Student,Graduate,Associate,Fellow,Honorary Fellow',
            'membership_number' => 'sometimes|nullable|unique:slia_members,membership_number,' . $id,
            'membership_year' => 'sometimes|nullable|string',
            'arb_number' => 'sometimes|nullable|unique:slia_members,arb_number,' . $id,
            'academic_qualifications' => 'sometimes|nullable|string',
            'professional_qualifications' => 'sometimes|nullable|string',
            'practice_name' => 'sometimes|nullable|string',
            'practice_description' => 'sometimes|nullable|string',
            'practice_type' => 'sometimes|nullable|string',
            'location_district' => 'sometimes|nullable|string',
            'location_province' => 'sometimes|nullable|string',
            'personal_website' => 'sometimes|nullable|string',
            'office_address' => 'sometimes|nullable|string',
            'office_district' => 'sometimes|nullable|string',
            'office_province' => 'sometimes|nullable|string',
            'office_phone' => 'sometimes|nullable|string',
            'office_fax' => 'sometimes|nullable|string',
            'office_email' => 'sometimes|nullable|email|unique:slia_members,office_email,' . $id,
            'office_website' => 'sometimes|nullable|string',
            'home_address' => 'sometimes|nullable|string',
            'home_district' => 'sometimes|nullable|string',
            'home_province' => 'sometimes|nullable|string',
            'home_phone' => 'sometimes|nullable|string',
            'home_fax' => 'sometimes|nullable|string',
            'home_email' => 'sometimes|nullable|email|unique:slia_members,home_email,' . $id,
            'contact_info_1_address' => 'sometimes|nullable|string',
            'contact_info_1_phone' => 'sometimes|nullable|string',
            'contact_info_1_email' => 'sometimes|nullable|email|unique:slia_members,contact_info_1_email,' . $id,
            'contact_info_2_address' => 'sometimes|nullable|string',
            'contact_info_2_phone' => 'sometimes|nullable|string',
            'contact_info_2_email' => 'sometimes|nullable|email|unique:slia_members,contact_info_2_email,' . $id,
            'positions_held' => 'sometimes|nullable|string',
            'slia_awards' => 'sometimes|nullable|string',
            'other_awards' => 'sometimes|nullable|string',
            'username' => 'sometimes|nullable|string',
            'password' => 'sometimes|nullable|string',
        ]);

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

            if (!empty($row['username'])) {
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

    private function readXlsx(string $path): array
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

        $rows = [];
        $headers = [];
        foreach ($sheetXml->sheetData->row as $index => $row) {
            $values = $this->xlsxRowValues($row, $sharedStrings);
            if ($index === 0) {
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

        foreach ($shared->si as $si) {
            $parts = [];
            if (isset($si->t)) {
                $parts[] = (string) $si->t;
            }
            foreach ($si->r as $run) {
                $parts[] = (string) $run->t;
            }
            $strings[] = implode('', $parts);
        }

        return $strings;
    }

    private function xlsxRowValues($row, array $sharedStrings): array
    {
        $values = [];
        foreach ($row->c as $cell) {
            $ref = (string) $cell['r'];
            $index = $this->xlsxColumnIndex($ref);
            $value = isset($cell->v) ? (string) $cell->v : '';
            if ((string) $cell['t'] === 's') {
                $value = $sharedStrings[(int) $value] ?? '';
            }
            $values[$index] = $value;
        }
        ksort($values);
        return array_values($values);
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
        return [
            'full_name' => $row['full_name'] ?? $row['name'] ?? null,
            'name_with_initials' => $row['name_with_initials'] ?? null,
            'yearbook_name' => $row['yearbook_name'] ?? null,
            'nic_number' => $row['nic_number'] ?? null,
            'gender' => $row['gender'] ?? null,
            'date_of_birth' => $row['date_of_birth'] ?? null,
            'membership_type' => $row['membership_type'] ?? null,
            'membership_number' => $row['membership_number'] ?? null,
            'membership_year' => $row['membership_year'] ?? null,
            'arb_number' => $row['arb_number'] ?? null,
            'academic_qualifications' => $row['academic_qualifications'] ?? null,
            'professional_qualifications' => $row['professional_qualifications'] ?? null,
            'practice_name' => $row['practice_name'] ?? null,
            'practice_description' => $row['practice_description'] ?? null,
            'practice_type' => $row['practice_type'] ?? null,
            'location_district' => $row['location_district'] ?? null,
            'location_province' => $row['location_province'] ?? null,
            'personal_website' => $row['personal_website'] ?? null,
            'office_address' => $row['office_address'] ?? null,
            'office_district' => $row['office_district'] ?? null,
            'office_province' => $row['office_province'] ?? null,
            'office_phone' => $row['office_phone'] ?? null,
            'office_fax' => $row['office_fax'] ?? null,
            'office_email' => $row['office_email'] ?? null,
            'office_website' => $row['office_website'] ?? null,
            'home_address' => $row['home_address'] ?? null,
            'home_district' => $row['home_district'] ?? null,
            'home_province' => $row['home_province'] ?? null,
            'home_phone' => $row['home_phone'] ?? null,
            'home_fax' => $row['home_fax'] ?? null,
            'home_email' => $row['home_email'] ?? null,
            'contact_info_1_address' => $row['contact_info_1_address'] ?? null,
            'contact_info_1_phone' => $row['contact_info_1_phone'] ?? null,
            'contact_info_1_email' => $row['contact_info_1_email'] ?? null,
            'contact_info_2_address' => $row['contact_info_2_address'] ?? null,
            'contact_info_2_phone' => $row['contact_info_2_phone'] ?? null,
            'contact_info_2_email' => $row['contact_info_2_email'] ?? null,
            'positions_held' => $row['positions_held'] ?? null,
            'slia_awards' => $row['slia_awards'] ?? null,
            'other_awards' => $row['other_awards'] ?? null,
            'username' => $row['username'] ?? null,
            'password' => $row['password'] ?? null,
        ];
    }

    private function passwordOtpCacheKey(SliaMember $member): string
    {
        return 'slia_member_password_otp_' . $member->id;
    }

}
