<div class="space-y-4">
    @include('livewire.components.table.toolbar')

    @include('livewire.components.table.table')

    @include('livewire.components.table.pagination', ['paginator' => $rows])

    @include('livewire.components.modal.form')

    @include('livewire.components.modal.delete')

    @include('livewire.components.modal.bulk-delete')
</div>
