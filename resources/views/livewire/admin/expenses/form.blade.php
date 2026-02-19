<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
    <div class="space-y-4">
        @if ($type === 'material')
            <div>
                <x-forms.searchable-select
                    label="Bahan"
                    :options="$this->materialOptions"
                    placeholder="Pilih bahan"
                    wire:model="form.material_id"
                    required
                />
                @error('form.material_id')
                    <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <x-forms.searchable-select
                    label="Supplier (Opsional)"
                    :options="$this->supplierOptions"
                    placeholder="Pilih supplier"
                    wire:model="form.supplier_id"
                />
                @error('form.supplier_id')
                    <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <x-forms.input label="Qty" name="form.qty" placeholder="0" wire:model.blur="form.qty" type="number" step="0.01" min="0" required />
            <x-forms.input label="Harga per Unit" name="form.unit_cost" placeholder="0" wire:model.blur="form.unit_cost" type="number" step="0.01" min="0" required />
            <x-forms.input label="Total (Opsional, akan dihitung jika kosong)" name="form.amount" placeholder="0" wire:model.blur="form.amount" type="number" step="0.01" min="0" />
        @else
            <x-forms.input label="Total" name="form.amount" placeholder="0" wire:model.blur="form.amount" type="number" step="0.01" min="0" required />
        @endif
    </div>

    <div class="space-y-4">
        <div>
            <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Metode Pembayaran</label>
            <select wire:model="form.payment_method" class="form-input mt-3">
                <option value="cash">Cash</option>
                <option value="transfer">Transfer</option>
                <option value="qris">QRIS</option>
            </select>
            @error('form.payment_method')
                <p class="text-sm text-red-600 dark:text-red-400 mt-2">{{ $message }}</p>
            @enderror
        </div>

        <x-forms.input label="Tanggal" name="form.expense_date" wire:model.blur="form.expense_date" type="date" required />

        <div>
            <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Catatan</label>
            <textarea class="form-input mt-3" rows="4" placeholder="Catatan" wire:model.blur="form.notes"></textarea>
            @error('form.notes')
                <p class="text-sm text-red-600 dark:text-red-400 mt-2">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>
