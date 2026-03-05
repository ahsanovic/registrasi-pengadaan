@props([
    'label',
    'icon' => 'calendar',
    'model',
    'required' => false,
    'disabled' => false,
])

@php
    $id = 'datepicker-' . str_replace('.', '-', $model);
@endphp

<div class="mb-3">
    <label for="{{ $id }}" class="form-label">{{ $label }} @if($required) <span class="text-danger">*</span> @endif</label>
    <input type="hidden" wire:model="{{ $model }}">
    <input 
            id="{{ $id }}"
            data-flatpickr
            type="text" 
            class="form-control @error($model) is-invalid @enderror"
            data-model="{{ $model }}"
            readonly="readonly"
            @if($disabled) disabled @endif
        >
    @error($model)
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>