@props([
    'action' => 'edit',
    'id' => null,
])

<button 
    type="button" 
    wire:click="{{ $action }}({{ $id }})"
    class="btn btn-sm btn-success"
    title="Edit"
    data-bs-toggle="tooltip"
    data-bs-placement="top"
>
    <i class="fa-solid fa-pen"></i>
</button>