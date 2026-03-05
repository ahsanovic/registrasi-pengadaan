<?php

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Hash;

new class extends Component
{
    public $old_password;
    public $new_password;
    public $confirm_password;

    // Modal state
    public $showModal = false;

    public function save()
    {
        $this->validate([
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:8',
            'confirm_password' => 'required|string|min:8|same:new_password',
        ], [
            'old_password.required' => 'harus diisi',
            'old_password.string' => 'harus berupa string',
            'new_password.required' => 'harus diisi',
            'new_password.string' => 'harus berupa string',
            'new_password.min' => 'minimal 8 karakter',
            'confirm_password.required' => 'harus diisi',
            'confirm_password.string' => 'harus berupa string',
            'confirm_password.min' => 'minimal 8 karakter',
            'confirm_password.same' => 'password tidak sama',
        ]);

        if (! Hash::check($this->old_password, auth()->user()->password)) {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'password lama tidak valid']);
            return;
        }

        auth()->user()->update([
            'password' => Hash::make($this->new_password),
        ]);

        $this->closeModal();
        $this->dispatch('toast', ['type' => 'success', 'message' => 'berhasil diperbarui']);
    }

    #[On('open-ubah-password-modal')]
    public function openModal()
    {
        $this->resetValidation();
        $this->reset(['old_password', 'new_password', 'confirm_password']);
        $this->showModal = true;
        $this->dispatch('modalOpened');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetValidation();
        $this->reset(['old_password', 'new_password', 'confirm_password']);
    }
};
?>
<div>
    <!-- Modal -->
    @if ($showModal)
    <div class="modal fade show" id="ubahPasswordModal" tabindex="-1" aria-hidden="true" style="display: block;">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" style="border: none; border-radius: 10px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
                <div class="modal-header py-3" style="background: linear-gradient(135deg, #667eea 0%, #316aff 100%); border: none; padding: 24px 32px;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center justify-content-center" 
                             style="width: 48px; height: 48px; background: rgba(255,255,255,0.2); border-radius: 12px; backdrop-filter: blur(10px);">
                            <i class="fa-solid fa-pen text-white" style="width: 14px; height: 14px;"></i>
                        </div>
                        <div>
                            <h5 class="modal-title text-white fw-bold mb-0" style="font-size: 1.5rem;">
                                Ubah Password
                            </h5>
                            <p class="text-white-50 mb-0 mt-1" style="font-size: 0.875rem;">
                                Isi form untuk mengubah password
                            </p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeModal" class="btn-close btn-close-white" 
                            style="filter: brightness(0) invert(1); opacity: 0.8; transition: opacity 0.2s;"
                            onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.8'">
                    </button>
                </div>
                <div class="modal-body">
                    <form>
                        <x-form.inputpassword label="Password Lama" model="old_password" required />
                        <x-form.inputpassword label="Password Baru" model="new_password" required />
                        <x-form.inputpassword label="Konfirmasi Password" model="confirm_password" required />
                    </form>
                </div>
                <div class="modal-footer">
                    <x-buttons.btn-close text="Batal" action="closeModal" />
                    <x-buttons.btn-save text="Ubah Password" action="save" />
                </div>
            </div>
        </div>
    </div>
    @endif
</div>