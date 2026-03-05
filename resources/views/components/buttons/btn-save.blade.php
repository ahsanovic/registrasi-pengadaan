@props([
    'text' => null,
    'isUpdate' => false,
    'action' => 'save',
])

<button 
    type="button" 
    wire:click="{{ $action }}"
    class="btn btn-primary d-flex align-items-center gap-2"
>
    @if($isUpdate)
        <i class="fa-solid fa-pen me-2"></i>
    @else
        <i class="fa-solid fa-floppy-disk me-2"></i>
    @endif
    {{ $text ?? ($isUpdate ? 'Perbarui' : 'Simpan') }}
</button>