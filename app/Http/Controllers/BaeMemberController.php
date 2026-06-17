<?php

namespace App\Http\Controllers;

use App\Models\BaeMember;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use ZipArchive;

class BaeMemberController extends Controller
{
    public function index(Request $request)
    {
        $query = BaeMember::query();

        if ($request->filled('member_type')) {
            $query->where('member_type', $request->query('member_type'));
        }

        if (!$request->user()) {
            $query->where('is_active', true);
        }

        if ($request->filled('search')) {
            $search = '%' . $request->query('search') . '%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                    ->orWhere('membership_number', 'like', $search)
                    ->orWhere('academic_qualifications', 'like', $search)
                    ->orWhere('email', 'like', $search);
            });
        }

        return response()->json(
            $query->orderBy('sort_order')
                ->orderBy('serial_no')
                ->orderBy('name')
                ->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $this->withReferenceNumber($request->validate($this->rules(true)));
        $member = BaeMember::create($validated);
        return response()->json($member, 201);
    }

    public function show(BaeMember $baeMember)
    {
        return response()->json($baeMember);
    }

    public function update(Request $request, BaeMember $baeMember)
    {
        $validated = $request->validate($this->rules(false));
        $validated = $this->withReferenceNumber($validated, $baeMember);
        $baeMember->update($validated);
        return response()->json($baeMember->fresh());
    }

    public function destroy(BaeMember $baeMember)
    {
        $baeMember->delete();
        return response()->json(null, 204);
    }

    public function import(Request $request)
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx', 'max:10240'],
            'member_type' => ['nullable', 'string', 'in:student,graduate,associate'],
            'replace' => ['nullable', 'boolean'],
        ]);

        $rows = $this->readWorkbook($request->file('file')->getRealPath(), $validated['member_type'] ?? null);

        if (!empty($validated['replace'])) {
            $types = collect($rows)->pluck('member_type')->unique();
            BaeMember::whereIn('member_type', $types)->delete();
        }

        $imported = 0;
        foreach ($rows as $row) {
            BaeMember::updateOrCreate(
                [
                    'member_type' => $row['member_type'],
                    'membership_number' => $row['membership_number'],
                ],
                $row
            );
            $imported++;
        }

        return response()->json(['imported' => $imported]);
    }

    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:bae_members,id'],
            'member_type' => ['required', 'string', 'in:student,graduate,associate'],
        ]);

        $updated = 0;
        BaeMember::whereIn('id', $validated['ids'])->get()->each(function (BaeMember $member) use ($validated, &$updated) {
            $data = $this->withReferenceNumber(['member_type' => $validated['member_type']], $member);
            $targetNumberField = $validated['member_type'] . '_membership_number';
            $targetYearField = $validated['member_type'] . '_membership_year';

            if (!empty($member->{$targetNumberField})) {
                $data['membership_number'] = $member->{$targetNumberField};
            }
            if (!empty($member->{$targetYearField})) {
                $data['membership_year'] = $member->{$targetYearField};
            }

            $member->update($data);
            $updated++;
        });

        return response()->json(['updated' => $updated]);
    }

    private function rules($creating)
    {
        $required = $creating ? 'required' : 'nullable';
        $routeMember = request()->route('bae_member');
        $memberId = $routeMember ? $routeMember->id : null;
        $memberType = request()->input('member_type') ?: ($routeMember ? $routeMember->member_type : null);

        return [
            'member_type' => [$required, 'string', 'in:student,graduate,associate'],
            'serial_no' => ['nullable', 'integer', 'min:0'],
            'name' => [$required, 'string', 'max:255'],
            'academic_qualifications' => ['nullable', 'string', 'max:255'],
            'membership_year' => ['nullable', 'string', 'max:20'],
            'membership_number' => [
                $required,
                'string',
                'max:80',
                Rule::unique('bae_members', 'membership_number')
                    ->where(fn ($query) => $query->where('member_type', $memberType))
                    ->ignore($memberId),
            ],
            'student_membership_number' => ['nullable', 'string', 'max:80'],
            'student_membership_year' => ['nullable', 'string', 'max:20'],
            'graduate_membership_number' => ['nullable', 'string', 'max:80'],
            'graduate_membership_year' => ['nullable', 'string', 'max:20'],
            'associate_membership_number' => ['nullable', 'string', 'max:80'],
            'associate_membership_year' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'contact_no' => ['nullable', 'string', 'max:80'],
            'email' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    private function readWorkbook($path, $onlyType = null)
    {
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            abort(response()->json(['message' => 'Could not open Excel workbook.'], 422));
        }

        $shared = $this->sharedStrings($zip);
        $sheets = $this->sheetTargets($zip);
        $rows = [];

        foreach ($sheets as $sheetName => $target) {
            $memberType = $this->memberTypeFromSheet($sheetName);
            if (!$memberType || ($onlyType && $onlyType !== $memberType)) {
                continue;
            }

            $sheetXml = simplexml_load_string($zip->getFromName($target));
            $rowIndex = 0;
            foreach ($sheetXml->sheetData->row as $row) {
                $rowIndex++;
                if ($rowIndex === 1) {
                    continue;
                }

                $values = $this->rowValues($row, $shared);
                if (count(array_filter($values, fn ($value) => trim((string) $value) !== '')) === 0) {
                    continue;
                }

                $isAssociate = $memberType === 'associate';
                $membershipNumber = trim($values[$isAssociate ? 1 : 4] ?? '');
                $name = trim($values[$isAssociate ? 2 : 1] ?? '');
                if ($name === '') {
                    continue;
                }

                $rows[] = [
                    'member_type' => $memberType,
                    'serial_no' => $this->nullableInt($values[0] ?? null),
                    'name' => $name,
                    'academic_qualifications' => trim($values[$isAssociate ? 3 : 2] ?? '') ?: null,
                    'membership_year' => trim($values[$isAssociate ? 4 : 3] ?? '') ?: null,
                    'membership_number' => $membershipNumber,
                    $memberType . '_membership_number' => $membershipNumber,
                    $memberType . '_membership_year' => trim($values[$isAssociate ? 4 : 3] ?? '') ?: null,
                    'address' => trim($values[$isAssociate ? 5 : 5] ?? '') ?: null,
                    'contact_no' => trim($values[$isAssociate ? 7 : 6] ?? '') ?: null,
                    'email' => trim($values[$isAssociate ? 6 : 7] ?? '') ?: null,
                    'remarks' => trim($values[$isAssociate ? 8 : 8] ?? '') ?: null,
                    'is_active' => true,
                    'sort_order' => $this->nullableInt($values[0] ?? null) ?: 0,
                ];
            }
        }

        $zip->close();
        return $rows;
    }

    private function sharedStrings(ZipArchive $zip)
    {
        $strings = [];
        $xml = $zip->getFromName('xl/sharedStrings.xml');
        if ($xml === false) {
            return $strings;
        }

        foreach (simplexml_load_string($xml)->si as $si) {
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

    private function sheetTargets(ZipArchive $zip)
    {
        $workbook = simplexml_load_string($zip->getFromName('xl/workbook.xml'));
        $rels = simplexml_load_string($zip->getFromName('xl/_rels/workbook.xml.rels'));
        $targets = [];
        foreach ($rels->Relationship as $rel) {
            $attrs = $rel->attributes();
            $targets[(string) $attrs['Id']] = 'xl/' . (string) $attrs['Target'];
        }

        $sheets = [];
        foreach ($workbook->sheets->sheet as $sheet) {
            $attrs = $sheet->attributes();
            $relAttrs = $sheet->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships');
            $sheets[(string) $attrs['name']] = $targets[(string) $relAttrs['id']] ?? null;
        }

        return array_filter($sheets);
    }

    private function rowValues($row, array $shared)
    {
        $values = [];
        foreach ($row->c as $cell) {
            $attrs = $cell->attributes();
            $column = preg_replace('/\d+/', '', (string) $attrs['r']);
            $index = $this->columnIndex($column);
            $value = (string) $cell->v;

            if ((string) $attrs['t'] === 's') {
                $value = $shared[(int) $value] ?? $value;
            }

            $values[$index] = $value;
        }

        ksort($values);
        return $values + array_fill(0, 12, '');
    }

    private function columnIndex($column)
    {
        $index = 0;
        foreach (str_split($column) as $char) {
            $index = $index * 26 + ord($char) - 64;
        }
        return $index - 1;
    }

    private function memberTypeFromSheet($name)
    {
        $normalized = strtolower($name);
        if (strpos($normalized, 'student') !== false) {
            return 'student';
        }
        if (strpos($normalized, 'graduate') !== false) {
            return 'graduate';
        }
        if (strpos($normalized, 'associate') !== false) {
            return 'associate';
        }
        return null;
    }

    private function nullableInt($value)
    {
        return is_numeric($value) ? (int) $value : null;
    }

    private function withReferenceNumber(array $data, BaeMember $existing = null)
    {
        $memberType = $data['member_type'] ?? ($existing ? $existing->member_type : null);
        $currentNumber = array_key_exists('membership_number', $data)
            ? $data['membership_number']
            : ($existing ? $existing->membership_number : null);
        $currentYear = array_key_exists('membership_year', $data)
            ? $data['membership_year']
            : ($existing ? $existing->membership_year : null);

        if ($existing && $memberType && $memberType !== $existing->member_type && !array_key_exists('membership_number', $data)) {
            $targetNumberField = $memberType . '_membership_number';
            if (!empty($existing->{$targetNumberField})) {
                $data['membership_number'] = $existing->{$targetNumberField};
                $currentNumber = $existing->{$targetNumberField};
            }
        }

        if ($existing && $memberType && $memberType !== $existing->member_type && !array_key_exists('membership_year', $data)) {
            $targetYearField = $memberType . '_membership_year';
            if (!empty($existing->{$targetYearField})) {
                $data['membership_year'] = $existing->{$targetYearField};
                $currentYear = $existing->{$targetYearField};
            }
        }

        if ($existing && $existing->member_type && $existing->membership_number) {
            $previousField = $existing->member_type . '_membership_number';
            if (empty($data[$previousField]) && empty($existing->{$previousField})) {
                $data[$previousField] = $existing->membership_number;
            }
        }

        if ($existing && $existing->member_type && $existing->membership_year) {
            $previousYearField = $existing->member_type . '_membership_year';
            if (empty($data[$previousYearField]) && empty($existing->{$previousYearField})) {
                $data[$previousYearField] = $existing->membership_year;
            }
        }

        $numberWasSubmitted = array_key_exists('membership_number', $data);
        $numberChanged = !$existing || ($numberWasSubmitted && $currentNumber !== $existing->membership_number);
        $typeUnchanged = !$existing || $memberType === $existing->member_type;

        if ($memberType && $currentNumber && ($numberChanged || $typeUnchanged)) {
            $currentField = $memberType . '_membership_number';
            if (empty($data[$currentField])) {
                $data[$currentField] = $currentNumber;
            }
        }

        if ($memberType && $currentYear && ($numberChanged || $typeUnchanged || array_key_exists('membership_year', $data))) {
            $currentYearField = $memberType . '_membership_year';
            if (empty($data[$currentYearField])) {
                $data[$currentYearField] = $currentYear;
            }
        }

        return $data;
    }
}
