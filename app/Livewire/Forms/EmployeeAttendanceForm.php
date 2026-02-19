<?php

namespace App\Livewire\Forms;

use App\Models\EmployeeAttendance;
use App\Services\EmployeeAttendanceService;
use Livewire\Form;

class EmployeeAttendanceForm extends Form
{
    public ?int $id = null;
    public ?int $employee_id = null;
    public ?string $attendance_date = null;
    public ?string $check_in = null;
    public ?string $check_out = null;
    public string $status = 'present';
    public ?string $notes = null;

    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'integer', 'exists:mst_employees,id'],
            'attendance_date' => ['required', 'date'],
            'check_in' => ['nullable', 'date_format:H:i'],
            'check_out' => ['nullable', 'date_format:H:i'],
            'status' => ['required', 'string', 'max:24'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function fillFromModel(EmployeeAttendance $attendance): void
    {
        $this->id = $attendance->id;
        $this->employee_id = $attendance->employee_id;
        $this->attendance_date = $attendance->attendance_date?->format('Y-m-d');
        $this->check_in = $attendance->check_in;
        $this->check_out = $attendance->check_out;
        $this->status = $attendance->status;
        $this->notes = $attendance->notes;
    }

    public function store(EmployeeAttendanceService $service): void
    {
        $this->validate();

        $service->store($this->payload());
    }

    public function update(EmployeeAttendanceService $service): void
    {
        $this->validate();

        $service->update($this->id, $this->payload());
    }

    protected function payload(): array
    {
        return [
            'employee_id' => $this->employee_id,
            'attendance_date' => $this->attendance_date,
            'check_in' => $this->check_in,
            'check_out' => $this->check_out,
            'status' => $this->status,
            'notes' => $this->notes,
        ];
    }
}
