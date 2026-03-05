@props([
    'text' => 'Batal',
    'action' => 'closeModal',
])

<button 
    type="button" 
    wire:click="{{ $action }}" 
    class="btn btn-secondary d-flex align-items-center gap-2"
>
    <i class="fa-solid fa-xmark me-2"></i>
    {{ $text }}
</button>
