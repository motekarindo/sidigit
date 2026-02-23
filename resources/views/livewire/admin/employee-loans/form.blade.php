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

        <div>
            <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Jumlah</label>
            <div class="mt-2" x-data="rupiahField(@entangle('form.amount').live)">
                <input type="text" inputmode="numeric" x-model="display" @input="onInput" class="form-input" />
            </div>
            @error('form.amount')
                <p class="text-sm text-red-600 dark:text-red-400 mt-2">{{ $message }}</p>
            @enderror
        </div>
        <x-forms.input label="Tanggal Kasbon" name="form.loan_date" wire:model.blur="form.loan_date" type="date" required />
    </div>

    <div class="space-y-4">
        @php
            $loanStatuses = collect([
                ['value' => 'open', 'label' => 'Belum Lunas'],
                ['value' => 'paid', 'label' => 'Lunas'],
            ]);
        @endphp
        <div>
            <x-forms.searchable-select
                label="Status"
                :options="$loanStatuses"
                optionValue="value"
                optionLabel="label"
                placeholder="Pilih status"
                wire:model="form.status"
            />
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
