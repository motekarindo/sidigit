<div class="space-y-2">
    <x-breadcrumbs :items="$breadcrumbs" />

    <x-card :title="$title" :description="$description">
        <livewire:admin.orders.trashed-table />
    </x-card>
</div>
