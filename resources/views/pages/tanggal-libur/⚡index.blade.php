<?php

use Livewire\Component;
use App\Models\TanggalLibur;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use Carbon\Carbon;
use Livewire\Attributes\Computed;

new class extends Component
{
    use WithPagination;

    public $tanggal;
    public $keterangan;
    public $selectedId;

    // filter state
    public $filter_tgl;
    public $filter_keterangan;
    public $filter_tahun;

    // Modal state
    public $showModal = false;
    public $isUpdate = false;
    public $editId;

    public function resetFilters()
    {
        $this->reset(['filter_tgl', 'filter_keterangan', 'filter_tahun']);
        $this->resetPage();
    }

    public function updatingFilterTgl()
    {
        $this->resetPage();
    }

    public function updatingFilterKeterangan()
    {
        $this->resetPage();
    }

    public function updatingFilterTahun()
    {
        $this->resetPage();
    }

    #[Computed]
    public function tahunList()
    {
        return TanggalLibur::selectRaw('YEAR(tanggal) as tahun')
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun');
    }

    #[Computed]
    public function tanggalLibur()
    {
        $tanggalLibur = TanggalLibur::when($this->filter_tgl, function ($query) {
            $query->where('tanggal', Carbon::parse($this->filter_tgl)->format('Y-m-d'));
        })
        ->when($this->filter_keterangan, function ($query) {
            $query->where('keterangan', 'like', '%' . $this->filter_keterangan . '%');
        })
        ->when($this->filter_tahun, function ($query) {
            $query->whereYear('tanggal', $this->filter_tahun);
        })
        ->orderBy('tanggal', 'desc')
        ->paginate(10);

        return $tanggalLibur;
    }

    public function save()
    {
        $this->validate([
            'tanggal' => 'required|date',
            'keterangan' => 'required|string|max:255',
        ], [
            'tanggal.required' => 'harus diisi',
            'tanggal.date' => 'harus berupa tanggal',
            'keterangan.required' => 'harus diisi',
            'keterangan.string' => 'harus berupa string',
            'keterangan.max' => 'maksimal 255 karakter',
        ]);

        if ($this->isUpdate) {
            TanggalLibur::find($this->editId)->update([
                'tanggal' => Carbon::parse($this->tanggal)->format('Y-m-d'),
                'keterangan' => $this->keterangan,
            ]);
            $this->closeModal();
            $this->dispatch('toast', ['type' => 'success', 'message' => 'berhasil diperbarui']);
            $this->resetFilters();
            return;
        }

        TanggalLibur::create([
            'tanggal' => Carbon::parse($this->tanggal)->format('Y-m-d'),
            'keterangan' => $this->keterangan,
        ]);

        $this->closeModal();
        $this->dispatch('toast', ['type' => 'success', 'message' => 'berhasil disimpan']);
        $this->resetFilters();
    }

    public function deleteConfirmation($id)
    {
        $this->selectedId = $id;
        $this->dispatch('show-delete-confirmation');
    }

    #[On('delete')]
    public function destroy()
    {
        TanggalLibur::find($this->selectedId)->delete();
        $this->dispatch('toast', ['type' => 'success', 'message' => 'berhasil dihapus']);
    }

    public function edit($id)
    {
        $data = TanggalLibur::find($id);
        $this->tanggal = $data->tanggal;
        $this->keterangan = $data->keterangan;
        $this->editId = $id;
        $this->showModal = true;
        $this->isUpdate = true;
        $this->resetValidation();
        $this->dispatch('modalOpened');
    }

    public function openModal()
    {
        $this->resetValidation();
        $this->reset(['tanggal', 'keterangan', 'editId', 'isUpdate']);
        $this->showModal = true;
        $this->dispatch('modalOpened');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetValidation();
        $this->reset(['tanggal', 'keterangan']);
    }
};
?>
<div>
    <div class="app-page-head">
        <h1 class="app-page-title">Tanggal Libur</h1>
        <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}" wire:navigate>Dashboard</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Tanggal Libur</li>
        </ol>
        </nav>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <button class="btn btn-outline-secondary" wire:click="openModal"><i class="icon-square-plus me-2"></i> Tambah Tanggal Libur</button>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3"><i class="icon-list-filter me-2"></i> Filter</h5>
                    <div class="row mb-2">
                        <div class="col-2">
                            <div class="form-group">
                                <input type="text" class="form-control" wire:model.live="filter_tgl" id="flatpickr_basic" placeholder="Pilih Tanggal" readonly>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="form-group">
                                <select class="form-control" wire:model.live="filter_tahun">
                                    <option value="">Semua Tahun</option>
                                    @foreach ($this->tahunList as $tahun)
                                        <option value="{{ $tahun }}">{{ $tahun }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="form-group">
                                <input type="text" class="form-control" wire:model.live="filter_keterangan" placeholder="Keterangan" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="form-group">
                                <button class="btn btn-subtle-danger" wire:click="resetFilters"><i class="icon-refresh-ccw me-2"></i>Reset</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12">
            <div class="card overflow-hidden">
                <div class="card-header">
                    <h6 class="card-title mb-0">Data Tanggal Libur</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-border-bottom-0 mb-0">
                            <thead style="background: #eef3f8 !important;">
                            <tr>
                                <th class="">#</th>
                                <th class="minw-100px">Tanggal</th>
                                <th class="minw-100px">Keterangan</th>
                                <th class="text-end">Action</th>
                            </tr>
                            </thead>
                            <tbody>
                                @forelse ($this->tanggalLibur as $item)
                                    <tr>
                                        <td>{{ $this->tanggalLibur->firstItem() + $loop->index }}</td>
                                        <td>{{ $item->tanggal }}</td>
                                        <td>{{ $item->keterangan }}</td>
                                        <td class="text-end">
                                            <x-buttons.btn-edit action="edit" id="{{ $item->id }}" />
                                            <x-buttons.btn-delete action="deleteConfirmation" id="{{ $item->id }}" />
                                        </td>
                                </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center text-muted">Tidak ada data</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <x-utils.pagination
                        :hasPages="$this->tanggalLibur->hasPages()"
                        :currentPage="$this->tanggalLibur->currentPage()"
                        :lastPage="$this->tanggalLibur->lastPage()"
                        :onFirstPage="$this->tanggalLibur->onFirstPage()"
                        :hasMorePages="$this->tanggalLibur->hasMorePages()"
                        :getUrlRange="$this->tanggalLibur->getUrlRange(1, $this->tanggalLibur->lastPage())"
                    />
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    @if ($showModal)
    <div class="modal fade show" id="addTanggalLiburModal" tabindex="-1" aria-hidden="true" style="display: block;"
        wire:key="modal-{{ $isUpdate ? 'edit-'.$editId : 'create' }}"
    >
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" style="border: none; border-radius: 10px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
                <div class="modal-header py-3" style="background: linear-gradient(135deg, #667eea 0%, #316aff 100%); border: none; padding: 24px 32px;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center justify-content-center" 
                             style="width: 48px; height: 48px; background: rgba(255,255,255,0.2); border-radius: 12px; backdrop-filter: blur(10px);">
                            @if($isUpdate)
                                <i class="fa-solid fa-pen text-white" style="width: 14px; height: 14px;"></i>
                            @else
                                <i class="fa-solid fa-plus text-white" style="width: 14px; height: 14px;"></i>
                            @endif
                        </div>
                        <div>
                            <h5 class="modal-title text-white fw-bold mb-0" style="font-size: 1.5rem;">
                                {{ $isUpdate ? 'Edit Tanggal Libur' : 'Tambah Tanggal Libur Baru' }}
                            </h5>
                            <p class="text-white-50 mb-0 mt-1" style="font-size: 0.875rem;">
                                {{ $isUpdate ? 'Perbarui informasi tanggal libur' : 'Isi form untuk menambahkan tanggal libur' }}
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
                        <x-form.date label="Tanggal" model="tanggal" />
                        <x-form.input label="Keterangan" model="keterangan" />
                    </form>
                </div>
                <div class="modal-footer">
                    <x-buttons.btn-close text="Batal" action="closeModal" />
                    <x-buttons.btn-save text="{{ $isUpdate ? 'Perbarui' : 'Simpan' }}" action="save" isUpdate="{{ $isUpdate }}" />
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@push('scripts')
    <script>
        Livewire.on('modalOpened', () => {
            setTimeout(() => {
                document.querySelectorAll('[data-flatpickr]').forEach(el => {
                    if (!el._flatpickr) {
                        flatpickr(el, {
                            dateFormat: 'd-m-Y',
                            onChange: function(selectedDates, dateStr) {
                                const model = el.getAttribute('data-model');
                                if (model) {
                                    @this.set(model, dateStr);
                                }
                            }
                        });
                    }
                });
            }, 200);
        });
    </script>
@endpush