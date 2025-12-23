@props([
    'label' => null,
    'name' => null,
    'type' => 'text',
    'placeholder' => null,
])

<div>
    @if ($label)
        <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
            {{ $label }}
        </label>
    @endif

    <input type="{{ $type }}" @if ($name) name="{{ $name }}" @endif
        {{ $attributes->merge([
            'class' => 'form-input mt-3',
            'placeholder' => $placeholder,
        ]) }}>

    @if ($name)
        @error($name)
            <p class="text-sm text-red-600 dark:text-red-400 mt-2">{{ $message }}</p>
        @enderror
    @endif
</div>
