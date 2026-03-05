@props([
    'action' => 'deleteConfirmation',
    'id' => null,
])

<button 
    type="button" 
    wire:click="{{ $action }}({{ $id }})"
    class="btn btn-sm btn-danger"
    title="Hapus"
    data-bs-toggle="tooltip"
    data-bs-placement="top"
>
    <i class="fa-solid fa-trash"></i>
</button>