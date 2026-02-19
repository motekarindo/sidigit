<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <div>
        <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Sumber</label>
        <select wire:model.live="filters.source" class="form-input mt-2">
            <option value="all">Semua</option>
            <option value="manual">Manual saja</option>
            <option value="order">Order</option>
            <option value="expense">Expense</option>
        </select>
    </div>
</div>
