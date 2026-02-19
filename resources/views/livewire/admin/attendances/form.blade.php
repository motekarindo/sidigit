<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
    <div class="space-y-4">
        <div>
            <x-forms.searchable-select
                label="Karyawan"
                :options="$this->employeeOptions"
                placeholder="Pilih karyawan"
                wire:model="form.employee_id"
                required
            />
            @error('form.employee_id')
                <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <x-forms.input label="Tanggal" name="form.attendance_date" wire:model.blur="form.attendance_date" type="date" required />

        <x-forms.input label="Check In" name="form.check_in" wire:model.blur="form.check_in" type="time" />
        <x-forms.input label="Check Out" name="form.check_out" wire:model.blur="form.check_out" type="time" />
    </div>

    <div class="space-y-4">
        @php
            $attendanceStatuses = collect([
                ['value' => 'present', 'label' => 'Hadir'],
                ['value' => 'absent', 'label' => 'Tidak Hadir'],
                ['value' => 'sick', 'label' => 'Sakit'],
                ['value' => 'leave', 'label' => 'Izin'],
            ]);
        @endphp
        <div>
            <x-forms.searchable-select
                label="Status"
                :options="$attendanceStatuses"
                optionValue="value"
                optionLabel="label"
                placeholder="Pilih status"
                wire:model="form.status"
            />
            @error('form.status')
                <p class="text-sm text-red-600 dark:text-red-400 mt-2">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Catatan</label>
            <textarea class="form-input mt-3" rows="4" wire:model.blur="form.notes" placeholder="Catatan"></textarea>
            @error('form.notes')
                <p class="text-sm text-red-600 dark:text-red-400 mt-2">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>
