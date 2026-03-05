@props([
    'label',
    'model',
    'required' => false,
    'placeholder' => '- pilih -',
    'multiple' => false,
    'notes' => null,
])

@php
    $id = 'select2-' . str_replace('.', '-', $model);
@endphp

<div
    class="mb-4"
    x-data="{
        value: @entangle($model)
    }"
    x-init="
        () => {
            const el = $refs.select;

            $(el).select2({
                placeholder: '{{ $placeholder }}',
                allowClear: {{ $multiple ? 'false' : 'true' }},
                width: '100%',
                dropdownParent: $(el).closest('.modal') ?? $('body'),
            });

            // set value (edit)
            if (value) {
                $(el).val(value).trigger('change.select2');
            }

            // sync to Livewire
            $(el).on('change', () => {
                value = $(el).val();
            });
        }
    "
>
    <label class="form-label">
        <span class="d-flex align-items-center gap-2">
            {{ $label }}
            @if($required) <span class="text-danger">*</span> @endif
        </span>
    </label>

    <div wire:ignore>
        <select
            x-ref="select"
            class="form-select @error($model) is-invalid @enderror"
            style="padding: 12px 16px; border-radius: 10px; border: 2px solid #e0e0e0; transition: all 0.3s ease; font-size: 0.95rem;"
            onfocus="this.style.borderColor='#667eea'; this.style.boxShadow='0 0 0 4px rgba(102, 126, 234, 0.1)'"
            onblur="this.style.borderColor='#e0e0e0'; this.style.boxShadow='none'"
            @if($multiple) multiple @endif
        >
            @if(!$multiple)
                <option value="">{{ $placeholder }}</option>
            @endif
            {{ $slot }}
        </select>
        @if($notes)
            <small class="text-success d-block mt-1">{{ $notes }}</small>
        @endif
    </div>

    @error($model)
        <div class="invalid-feedback d-block">
            {{ $message }}
        </div>
    @enderror
</div>

