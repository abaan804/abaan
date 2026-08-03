<?php

namespace Modules\Ledger\Imports;

use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Modules\Ledger\Models\LedgerCustomer;

class CustomersImport implements ToCollection, WithHeadingRow
{
    public array $imported = [];
    public array $failed = [];

    public function __construct(protected int $companyId, protected ?int $userId = null)
    {
    }

    public function collection(\Illuminate\Support\Collection $rows): void
    {
        foreach ($rows as $i => $row) {
            $rowNumber = $i + 2; // +2 because row 1 is the header, collection is 0-indexed

            $data = [
                'name' => trim($row['name'] ?? ''),
                'mobile' => trim($row['mobile'] ?? ''),
                'email' => trim($row['email'] ?? ''),
                'city' => trim($row['city'] ?? ''),
                'address' => trim($row['address'] ?? ''),
                'opening_balance' => is_numeric($row['opening_balance'] ?? null) ? $row['opening_balance'] : 0,
            ];

            $validator = Validator::make($data, [
                'name' => 'required|string|max:255',
                'mobile' => 'nullable|string|max:30',
                'email' => 'nullable|email|max:255',
                'city' => 'nullable|string|max:100',
                'address' => 'nullable|string|max:1000',
                'opening_balance' => 'nullable|numeric',
            ]);

            if ($validator->fails()) {
                $this->failed[] = [
                    'row' => $rowNumber,
                    'name' => $data['name'] ?: '(empty)',
                    'errors' => $validator->errors()->all(),
                ];
                continue;
            }

            $data['company_id'] = $this->companyId;
            $data['status'] = 'active';
            $data['created_by'] = $this->userId;

            $customer = LedgerCustomer::create($data);
            $this->imported[] = $customer->name;
        }
    }
}