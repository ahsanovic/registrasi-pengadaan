@props([
    'label',
    'model',
    'type' => 'password',
    'required' => false,
    'disabled' => false,
    'maxlength' => null,
])

<div class="mb-3">
    <label for="{{ 'input-' . $model }}" class="form-label">{{ $label }} @if($required) <span class="text-danger">*</span> @endif</label>
    <div class="position-relative">
        <input
            id="{{ 'input-' . $model }}"
            type="{{ $type }}"
            wire:model="{{ $model }}"
            class="form-control pe-5 @error($model) is-invalid @enderror"
            @if($disabled) disabled @endif
            @if($maxlength) maxlength="{{ $maxlength }}" @endif
        >
        <button
            type="button"
            class="btn d-flex align-items-center justify-content-center text-muted position-absolute top-50 end-0 translate-middle-y p-0 border-0 bg-transparent"
            style="width: 40px; height: 40px; right: 4px;"
            onclick="
                const input = document.getElementById('{{ 'input-' . $model }}');
                const icon = this.querySelector('i');
                const isPassword = input.type === 'password';
                input.type = isPassword ? 'text' : 'password';
                icon.classList.toggle('fa-eye', !isPassword);
                icon.classList.toggle('fa-eye-slash', isPassword);
            "
            @if($disabled) disabled @endif
            aria-label="Toggle password visibility"
            title="Tampilkan atau sembunyikan password"
        >
            <i class="fa-solid fa-eye"></i>
        </button>
        @error($model)
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>
</div>