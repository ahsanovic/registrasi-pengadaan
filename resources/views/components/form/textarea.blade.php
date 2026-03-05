@props([
    'label',
    'model',
    'rows' => 3,
    'required' => false,
])

<div class="mb-3">
    <label for="{{ 'textarea-' . $model }}" class="form-label">{{ $label }} @if($required) <span class="text-danger">*</span> @endif</label>
    <textarea 
            id="{{ 'textarea-' . $model }}"
            wire:model="{{ $model }}" 
            class="form-control @error($model) is-invalid @enderror" 
            rows="{{ $rows }}"
        >
    </textarea>
    @error($model)
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>