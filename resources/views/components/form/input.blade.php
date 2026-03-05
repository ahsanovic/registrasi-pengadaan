@props([
    'label',
    'model',
    'type' => 'text',
    'required' => false,
    'disabled' => false,
    'maxlength' => null,
])

<div class="mb-3">
    <label for="{{ 'input-' . $model }}" class="form-label">{{ $label }} @if($required) <span class="text-danger">*</span> @endif</label>
    <input 
            id="{{ 'input-' . $model }}"
            type="{{ $type }}" 
            wire:model="{{ $model }}" 
            class="form-control @error($model) is-invalid @enderror" 
            @if($disabled) disabled @endif
            @if($maxlength) maxlength="{{ $maxlength }}" @endif
        >
    @error($model)
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>