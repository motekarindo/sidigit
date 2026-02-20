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

    protected function messages(): array
    {
        return [
            'employee_id.required' => 'Karyawan wajib dipilih.',
            'employee_id.integer' => 'Karyawan tidak valid.',
            'employee_id.exists' => 'Karyawan yang dipilih tidak valid.',
            'attendance_date.required' => 'Tanggal absensi wajib diisi.',
            'attendance_date.date' => 'Tanggal absensi tidak valid.',
            'check_in.date_format' => 'Format jam check in tidak valid.',
            'check_out.date_format' => 'Format jam check out tidak valid.',
            'status.required' => 'Status wajib dipilih.',
            'status.max' => 'Status maksimal 24 karakter.',
            'notes.string' => 'Catatan harus berupa teks.',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'employee_id' => 'Karyawan',
            'attendance_date' => 'Tanggal absensi',
            'check_in' => 'Check in',
            'check_out' => 'Check out',
            'status' => 'Status',
            'notes' => 'Catatan',
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
