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

        <x-forms.input label="Jumlah" name="form.amount" placeholder="0" wire:model.blur="form.amount" type="number" step="0.01" min="0" required />
        <x-forms.input label="Tanggal Kasbon" name="form.loan_date" wire:model.blur="form.loan_date" type="date" required />
    </div>

    <div class="space-y-4">
        <div>
            <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Status</label>
            <select wire:model="form.status" class="form-input mt-3">
                <option value="open">Belum Lunas</option>
                <option value="paid">Lunas</option>
            </select>
            @error('form.status')
                <p class="text-sm text-red-600 dark:text-red-400 mt-2">{{ $message }}</p>
            @enderror
        </div>

        <x-forms.input label="Tanggal Lunas (Opsional)" name="form.paid_at" wire:model.blur="form.paid_at" type="date" />

        <div>
            <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Catatan</label>
            <textarea class="form-input mt-3" rows="4" wire:model.blur="form.notes" placeholder="Catatan"></textarea>
            @error('form.notes')
                <p class="text-sm text-red-600 dark:text-red-400 mt-2">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>
