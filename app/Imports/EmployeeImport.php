<?php

namespace App\Imports;

use App\Models\Employee;
use App\Models\BiometricUsers;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class EmployeeImport implements ToCollection, WithHeadingRow
{

    protected $branch;
    protected $employee_status;
    protected $employee_service;
    protected $name;
    public function __construct($branch, $employee_status, $employee_service, $name)
    {
        $this->branch = $branch;
        $this->employee_status = $employee_status;
        $this->employee_service = $employee_service;
        $this->name = $name;
    }
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {

            $userId = (string) (int) $row['ac_no'];
            $name = $row['name'];

            if (!$userId){
                \Log::warning('Skip: user_id kosong');
                continue;
            }

            $biometric = BiometricUsers::where('user_id', $userId)->first();

            if (!$biometric) {
                \Log::warning('Skip: user_id tidak ditemukan di biometric_users', [
                    'user_id' => $userId,
                ]);
                continue;
            }
            if (!$biometric)
                continue;

            $employee = Employee::where('user_id', $userId)->first();

            if (!$employee) {

                Employee::create([
                    'user_id' => $userId,
                    'name' => $name,
                    'branch' => $this->branch,
                    'employee_status' => $this->employee_status,
                    'employee_services' => $this->employee_service,
                ]);

                continue;
            }

            if ($employee->branch != $this->branch) {
                continue;
            }

            if ($employee->employee_status != $this->employee_status) {
                continue;
            }

            if ($employee->employee_services != $this->employee_service) {
                continue;
            }

            $employee->update([
                'name' => $name,
            ]);
        }
    }
}