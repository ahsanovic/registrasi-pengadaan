<?php

use Livewire\Component;
use App\Models\SpaceNomor;
use Livewire\WithPagination;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;

new class extends Component
{
    use WithPagination;

    public $tanggal;
    public $jumlah_space_nomor;
    public $selectedId;

    // filter state
    public $filter_tgl;
    public $filter_tahun;
    public $filter_status;
    public $filter_nomor_agenda;

    // Modal state
    public $showModal = false;
    public $isUpdate = false;
    public $editId;

    #[Computed]
    public function tahunList()
    {
        return SpaceNomor::selectRaw('YEAR(tanggal) as tahun')
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun');
    }

    public function resetFilters()
    {
        $this->reset(['filter_tgl', 'filter_tahun', 'filter_status', 'filter_nomor_agenda']);
        $this->resetPage();
    }

    #[Computed]
    public function spaceNomor()
    {
        $spaceNomor = SpaceNomor::when($this->filter_tgl, function ($query) {
            $query->where('tanggal', Carbon::parse($this->filter_tgl)->format('Y-m-d'));
        })
        ->when($this->filter_status, function ($query) {
            if ($this->filter_status == 'terpakai') {
                $query->where('used_at', '!=', null);
            } else {
                $query->where('used_at', null);
            }
        })
        ->when($this->filter_nomor_agenda, function ($query) {
            $query->where('nomor_agenda', $this->filter_nomor_agenda);
        })
        ->when($this->filter_tahun, function ($query) {
            $query->whereYear('tanggal', $this->filter_tahun);
        })
        ->orderBy('tanggal', 'desc')
        ->orderBy('nomor_agenda', 'asc')
        ->paginate(10);

        return $spaceNomor;
    }

    public function save()
    {
        $this->validate([
            'tanggal' => 'required|date',
            'jumlah_space_nomor' => 'required|integer|min:1',
        ], [
            'tanggal.required' => 'harus diisi',
            'tanggal.date' => 'harus berupa tanggal',
            'jumlah_space_nomor.required' => 'harus diisi',
            'jumlah_space_nomor.integer' => 'harus berupa angka',
            'jumlah_space_nomor.min' => 'minimal 1',
        ]);

        $tanggal = Carbon::parse($this->tanggal)->format('Y-m-d');
        $tahun = Carbon::parse($this->tanggal)->format('Y');

        if ($this->isUpdate) {
            SpaceNomor::find($this->editId)->update([
                'tahun' => $tahun,
                'tanggal' => $tanggal,
                'nomor_agenda' => $this->jumlah_space_nomor,
            ]);
            $this->closeModal();
            $this->dispatch('toast', ['type' => 'success', 'message' => 'berhasil diperbarui']);
            $this->resetFilters();
            return;
        }

        DB::transaction(function () use ($tahun, $tanggal) {
            $lastNomorAgenda = SpaceNomor::where('tahun', $tahun)
                ->where('tanggal', $tanggal)
                ->max('nomor_agenda') ?? 0;

            $now = now();
            $records = [];

            for ($i = 1; $i <= (int) $this->jumlah_space_nomor; $i++) {
                $records[] = [
                    'tahun' => $tahun,
                    'tanggal' => $tanggal,
                    'nomor_agenda' => $lastNomorAgenda + $i,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            SpaceNomor::insert($records);
        });

        $this->closeModal();
        $this->dispatch('toast', ['type' => 'success', 'message' => 'berhasil disimpan']);
        $this->resetFilters();
    }

    public function openModal()
    {
        $this->resetValidation();
        $this->reset(['tanggal', 'jumlah_space_nomor', 'editId', 'isUpdate']);
        $this->showModal = true;
        $this->dispatch('modalOpened');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetValidation();
        $this->reset(['tanggal', 'jumlah_space_nomor']);
    }
};
?>
<div>
    <div class="app-page-head">
        <h1 class="app-page-title">Space Nomor</h1>
        <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}" wire:navigate>Dashboard</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Space Nomor</li>
        </ol>
        </nav>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <button class="btn btn-outline-secondary" wire:click="openModal"><i class="icon-square-plus me-2"></i> Tambah Space Nomor</button>
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
                        <div class="col-2">
                            <div class="form-group">
                                <select class="form-control" wire:model.live="filter_status">
                                    <option value="">Semua Status</option>
                                    <option value="terpakai">Terpakai</option>
                                    <option value="tersedia">Tersedia</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="form-group">
                                <input type="text" class="form-control" wire:model.live="filter_nomor_agenda" placeholder="Nomor Agenda" autocomplete="off">
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
                    <h6 class="card-title mb-0">Data Space Nomor</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-border-bottom-0 mb-0">
                            <thead style="background: #eef3f8 !important;">
                            <tr>
                                <th class="">#</th>
                                <th class="minw-100px">Tanggal</th>
                                <th class="minw-100px">Nomor Agenda</th>
                                <th class="minw-100px">Status</th>
                            </tr>
                            </thead>
                            <tbody>
                                @forelse ($this->spaceNomor as $item)
                                    <tr>
                                        <td>{{ $this->spaceNomor->firstItem() + $loop->index }}</td>
                                        <td>{{ $item->tanggal }}</td>
                                        <td>{{ $item->nomor_agenda }}</td>
                                        <td>
                                            @if ($item->used_at)
                                                <span class="badge bg-danger d-inline-flex align-items-center px-3 py-2" style="font-size: 0.95em; font-weight: 600; border-radius: 20px;">
                                                    <i class="fa-solid fa-circle-xmark me-2"></i> Terpakai
                                                </span>
                                                <small class="text-muted ms-2" style="font-size: 0.88em;">
                                                    <i class="fa-regular fa-clock me-1"></i>
                                                    {{ \Carbon\Carbon::parse($item->used_at)->translatedFormat('d M Y H:i') }}
                                                </small>
                                            @else
                                                <span class="badge bg-success d-inline-flex align-items-center px-3 py-2" style="font-size: 0.95em; font-weight: 600; border-radius: 20px;">
                                                    <i class="fa-solid fa-circle-check me-2"></i> Tersedia
                                                </span>
                                            @endif
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
                        :hasPages="$this->spaceNomor->hasPages()"
                        :currentPage="$this->spaceNomor->currentPage()"
                        :lastPage="$this->spaceNomor->lastPage()"
                        :onFirstPage="$this->spaceNomor->onFirstPage()"
                        :hasMorePages="$this->spaceNomor->hasMorePages()"
                        :getUrlRange="$this->spaceNomor->getUrlRange(1, $this->spaceNomor->lastPage())"
                    />
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    @if ($showModal)
    <div class="modal fade show" id="addSpaceNomorModal" tabindex="-1" aria-hidden="true" style="display: block;"
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
                                {{ $isUpdate ? 'Edit Space Nomor' : 'Tambah Space Nomor Baru' }}
                            </h5>
                            <p class="text-white-50 mb-0 mt-1" style="font-size: 0.875rem;">
                                {{ $isUpdate ? 'Perbarui informasi space nomor' : 'Isi form untuk menambahkan space nomor' }}
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
                        <x-form.input label="Jumlah Space Nomor" model="jumlah_space_nomor" />
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