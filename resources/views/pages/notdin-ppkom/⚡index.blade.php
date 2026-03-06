<?php

use Livewire\Component;
use App\Models\RefBidang;
use App\Models\NotdinPpkom;
use App\Models\NotdinKpa;
use App\Models\SpaceNomor;
use App\Models\TanggalLibur;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use Carbon\Carbon;
use App\Services\AgendaNumberService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;

new class extends Component
{
    use WithPagination;

    public $bidang_list;
    public $nomor_agenda;
    public $tanggal;
    public $notdin_kpa_id;
    public $penyedia;
    public $alamat;
    public $npwp;
    public $selectedId;
    public $notdinKpaSearch = '';
    public $notdinKpaOptions = [];
    public $showNotdinKpaSuggestions = false;
    public $isSelectingNotdinKpa = false;
    
    // filter state
    public $filter_tgl;
    public $filter_bidang_id;
    public $filter_penyedia;
    public $filter_rencana_kegiatan;

    // Modal state
    public $showModal = false;
    public $isUpdate = false;
    public $editId;

    public function mount()
    {
        $this->bidang_list = RefBidang::where('parent_id', '!=', '1')->get();
    }

    public function resetFilters()
    {
        $this->reset(['filter_bidang_id', 'filter_tgl', 'filter_penyedia', 'filter_rencana_kegiatan']);
        $this->resetPage();
    }

    #[Computed]
    public function notdinPpkom()
    {
        $user = auth()->user();
        $query = NotdinPpkom::query();

        if ($user->role != 'admin') {
            $query->whereHas('notdinKpa', function ($query) use ($user) {
                $query->where('bidang_id', $user->bidang_id);
            });
        }

        $notdinPpkom = $query->with('notdinKpa')
        ->when($this->filter_bidang_id, function ($query) {
            $query->whereHas('notdinKpa', function ($query) {
                $query->where('bidang_id', $this->filter_bidang_id);
            });
        })
        ->when($this->filter_tgl, function ($query) {
            $query->where('tanggal', Carbon::parse($this->filter_tgl)->format('Y-m-d'));
        })
        ->when($this->filter_penyedia, function ($query) {
            $query->where('penyedia', 'like', '%' . $this->filter_penyedia . '%');
        })
        ->when($this->filter_rencana_kegiatan, function ($query) {
            $query->whereHas('notdinKpa', function ($query) {
                $query->where('rencana_kegiatan', 'like', '%' . $this->filter_rencana_kegiatan . '%');
            });
        })
        ->orderBy('nomor_agenda', 'desc')
        ->paginate(10);

        return $notdinPpkom;
    }

    public function updatedNotdinKpaSearch($value)
    {
        if ($this->isSelectingNotdinKpa) {
            return;
        }

        $keyword = trim((string) $value);
        $this->notdin_kpa_id = null;

        if (mb_strlen($keyword) < 2) {
            $this->notdinKpaOptions = [];
            $this->showNotdinKpaSuggestions = false;
            return;
        }

        $user = auth()->user();
        $query = NotdinKpa::query();

        if ($user->role != 'admin') {
            $query->where('bidang_id', $user->bidang_id);
        }

        $this->notdinKpaOptions = $query->with('bidang:id,nama')
            ->where(function ($query) use ($keyword) {
                $query->where('mata_anggaran', 'like', '%' . $keyword . '%')
                    ->orWhere('rencana_kegiatan', 'like', '%' . $keyword . '%');
            })->orderByDesc('id')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                $bidang = $item->bidang->nama ?? '-';
                return [
                    'id' => $item->id,
                    'label' => $bidang . ' - ' . $item->mata_anggaran . ' - ' . $item->rencana_kegiatan . ' - ' . $item->rencana_anggaran,
                ];
            })->toArray();

        $this->showNotdinKpaSuggestions = count($this->notdinKpaOptions) > 0;
    }

    public function selectNotdinKpa($id)
    {
        $item = NotdinKpa::select('id', 'mata_anggaran', 'rencana_kegiatan', 'rencana_anggaran')->find($id);

        if (!$item) {
            return;
        }

        $this->isSelectingNotdinKpa = true;
        $this->notdin_kpa_id = $item->id;
        $this->notdinKpaSearch = $item->mata_anggaran . ' - ' . $item->rencana_kegiatan . ' - ' . $item->rencana_anggaran;
        $this->isSelectingNotdinKpa = false;
        $this->notdinKpaOptions = [];
        $this->showNotdinKpaSuggestions = false;
    }

    public function hideNotdinKpaSuggestions()
    {
        $this->showNotdinKpaSuggestions = false;
    }

    public function save()
    {
        $this->validate([
            'tanggal' => 'required|date',
            'notdin_kpa_id' => 'required|exists:notdin_kpa,id',
            'penyedia' => 'required|string|max:255',
            'alamat' => 'required|string',
            'npwp' => 'required|string|max:255',
        ], [
            'tanggal.required' => 'harus diisi',
            'tanggal.date' => 'harus berupa tanggal',
            'notdin_kpa_id.required' => 'harus diisi',
            'penyedia.required' => 'harus diisi',
            'penyedia.string' => 'harus berupa string',
            'penyedia.max' => 'maksimal 255 karakter',
            'alamat.required' => 'harus diisi',
            'alamat.string' => 'harus berupa string',
            'npwp.required' => 'harus diisi',
            'npwp.string' => 'harus berupa string',
            'npwp.max' => 'maksimal 255 karakter',
        ]);

        // validasi tanggal libur
        $tgl_libur = TanggalLibur::where('tanggal', Carbon::parse($this->tanggal)->format('Y-m-d'))->first();
        if ($tgl_libur) {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Tanggal ' . $this->tanggal . ' adalah tanggal libur' . ($tgl_libur->keterangan ? ' - ' . $tgl_libur->keterangan : '')]);
            return;
        }

        // validasi libur akhir pekan
        $tgl_akhir_pekan = Carbon::parse($this->tanggal)->isSaturday() || Carbon::parse($this->tanggal)->isSunday();
        if ($tgl_akhir_pekan) {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Tanggal ' . $this->tanggal . ' adalah akhir pekan']);
            return;
        }

        if ($this->isUpdate) {
            NotdinPpkom::find($this->editId)->update([
                'notdin_kpa_id' => $this->notdin_kpa_id,
                'tanggal' => Carbon::parse($this->tanggal)->format('Y-m-d'),
                'penyedia' => $this->penyedia,
                'alamat' => $this->alamat,
                'npwp' => $this->npwp,
            ]);
            $this->closeModal();
            $this->dispatch('toast', ['type' => 'success', 'message' => 'berhasil diperbarui']);
            $this->resetFilters();
            return;
        }

        $nomor_agenda = app(AgendaNumberService::class)
            ->reserveForDate($this->tanggal);

        $this->nomor_agenda = $nomor_agenda;

        NotdinPpkom::create([
            'notdin_kpa_id' => $this->notdin_kpa_id,
            'nomor_agenda' => $this->nomor_agenda,
            'tanggal' => Carbon::parse($this->tanggal)->format('Y-m-d'),
            'penyedia' => $this->penyedia,
            'alamat' => $this->alamat,
            'npwp' => $this->npwp,
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
        $notdin_ppkom = NotdinPpkom::with('dokumenPengadaan')->find($this->selectedId);

        if (!$notdin_ppkom) {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'data tidak ditemukan']);
            return;
        }

        DB::transaction(function () use ($notdin_ppkom) {
            $nomorAgenda = $notdin_ppkom->dokumenPengadaan
                ->pluck('nomor_agenda')
                ->push($notdin_ppkom->nomor_agenda)
                ->filter()
                ->unique()
                ->values();

            if ($nomorAgenda->isNotEmpty()) {
                SpaceNomor::whereIn('nomor_agenda', $nomorAgenda)->update(['used_at' => null]);
            }

            // Hapus child lebih dulu untuk menghindari pelanggaran foreign key.
            $notdin_ppkom->dokumenPengadaan()->delete();
            $notdin_ppkom->delete();
        });

        $this->dispatch('toast', ['type' => 'success', 'message' => 'berhasil dihapus']);
    }

    public function edit($id)
    {
        $data = NotdinPpkom::find($id);
        $this->tanggal = Carbon::parse($data->tanggal)->format('d-m-Y');
        $this->notdin_kpa_id = $data->notdin_kpa_id;
        $notdinKpa = NotdinKpa::select('id', 'mata_anggaran', 'rencana_kegiatan')->find($data->notdin_kpa_id);
        $this->notdinKpaSearch = $notdinKpa
            ? $notdinKpa->mata_anggaran . ' - ' . $notdinKpa->rencana_kegiatan
            : '';
        $this->notdinKpaOptions = [];
        $this->showNotdinKpaSuggestions = false;
        $this->penyedia = $data->penyedia;
        $this->alamat = $data->alamat;
        $this->npwp = $data->npwp;
        $this->editId = $id;
        $this->showModal = true;
        $this->isUpdate = true;
        $this->resetValidation();
        $this->dispatch('modalOpened');
    }

    public function openModal()
    {
        $this->resetValidation();
        $this->reset([
            'tanggal',
            'notdin_kpa_id',
            'notdinKpaSearch',
            'notdinKpaOptions',
            'showNotdinKpaSuggestions',
            'isSelectingNotdinKpa',
            'penyedia',
            'alamat',
            'npwp',
            'editId',
            'isUpdate',
        ]);
        $this->showModal = true;
        $this->dispatch('modalOpened');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetValidation();
        $this->reset(['tanggal', 'notdin_kpa_id', 'notdinKpaSearch', 'notdinKpaOptions', 'showNotdinKpaSuggestions', 'isSelectingNotdinKpa', 'penyedia', 'alamat', 'npwp']);
    }
};
?>
<div>
    <div class="app-page-head">
        <h1 class="app-page-title">Nota Dinas PPKOM</h1>
        <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}" wire:navigate>Dashboard</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Nota Dinas PPKOM</li>
        </ol>
        </nav>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <button class="btn btn-outline-secondary" wire:click="openModal"><i class="icon-square-plus me-2"></i> Tambah Nota Dinas PPKOM</button>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3"><i class="icon-list-filter me-2"></i> Filter</h5>
                    <div class="row mb-2">
                        @if (auth()->user()->role == 'admin')
                            <div class="col-2">
                                <div class="form-group">
                                    <select class="form-control" id="bidang" wire:model.live="filter_bidang_id">
                                        <option value="">Semua Bidang</option>
                                        @foreach ($bidang_list as $item)
                                            <option value="{{ $item->id }}">{{ $item->nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endif
                        <div class="col-2">
                            <div class="form-group">
                                <input type="text" class="form-control" wire:model.live="filter_tgl" id="flatpickr_basic" placeholder="Pilih Tanggal" readonly>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="form-group">
                                <input type="text" class="form-control" wire:model.live="filter_penyedia" placeholder="Penyedia" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="form-group">
                                <input type="text" class="form-control" wire:model.live="filter_rencana_kegiatan" placeholder="Nama Pekerjaan" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-1">
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
                    <h6 class="card-title mb-0">Data Nota Dinas PPKOM</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-border-bottom-0 mb-0">
                            <thead style="background: #eef3f8 !important;">
                                <tr>
                                    <th>#</th>
                                    <th>No. Agenda</th>
                                    <th>Tanggal</th>
                                    <th>Bidang</th>
                                    <th>Mata Anggaran</th>
                                    <th>Nama Pekerjaan</th>
                                    <th>Nilai Pekerjaan</th>
                                    <th>Penyedia</th>
                                    <th>NPWP Penyedia</th>
                                    <th>Alamat</th>
                                    <th class="text-end minw-150px">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($this->notdinPpkom as $item)
                                    <tr>
                                        <td>{{ $this->notdinPpkom->firstItem() + $loop->index }}</td>
                                        <td>{{ $item->nomor_agenda }}</td>
                                        <td>{{ $item->tanggal }}</td>
                                        <td>{{ $item->notdinKpa->bidang->nama }}</td>
                                        <td>{{ $item->notdinKpa->mata_anggaran }}</td>
                                        <td>{{ $item->notdinKpa->rencana_kegiatan }}</td>
                                        <td>{{ $item->notdinKpa->rencana_anggaran }}</td>
                                        <td>{{ $item->penyedia }}</td>
                                        <td class="text-wrap">{{ $item->alamat }}</td>
                                        <td>{{ $item->npwp }}</td>
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
                        :hasPages="$this->notdinPpkom->hasPages()"
                        :currentPage="$this->notdinPpkom->currentPage()"
                        :lastPage="$this->notdinPpkom->lastPage()"
                        :onFirstPage="$this->notdinPpkom->onFirstPage()"
                        :hasMorePages="$this->notdinPpkom->hasMorePages()"
                        :getUrlRange="$this->notdinPpkom->getUrlRange(1, $this->notdinPpkom->lastPage())"
                    />
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    @if ($showModal)
    <div class="modal fade show" id="addNotdinPpkomModal" tabindex="-1" aria-hidden="true" style="display: block;"
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
                                {{ $isUpdate ? 'Edit Nota Dinas PPKOM' : 'Tambah Nota Dinas PPKOM Baru' }}
                            </h5>
                            <p class="text-white-50 mb-0 mt-1" style="font-size: 0.875rem;">
                                {{ $isUpdate ? 'Perbarui informasi nota dinas ppkom' : 'Isi form untuk menambahkan nota dinas ppkom' }}
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
                        <x-form.date label="Tanggal" model="tanggal" required disabled="{{ $isUpdate }}" />
                        <div class="mb-3 position-relative">
                            <label for="input-notdin-kpa-search" class="form-label">
                                Mata Anggaran dan Kegiatan <span class="text-danger">*</span>
                            </label>
                            <input
                                id="input-notdin-kpa-search"
                                type="text"
                                class="form-control @error('notdin_kpa_id') is-invalid @enderror"
                                placeholder="Ketik minimal 2 huruf untuk mencari"
                                wire:model.live.debounce.300ms="notdinKpaSearch"
                                wire:focus="$set('showNotdinKpaSuggestions', true)"
                                wire:keydown.escape="hideNotdinKpaSuggestions"
                                autocomplete="off"
                            >

                            @if ($showNotdinKpaSuggestions && !empty($notdinKpaOptions))
                                <div class="list-group position-absolute w-100 shadow-sm mt-1" style="z-index: 1056; max-height: 240px; overflow-y: auto;">
                                    @foreach ($notdinKpaOptions as $item)
                                        <button
                                            type="button"
                                            class="list-group-item list-group-item-action text-start"
                                            wire:click="selectNotdinKpa({{ $item['id'] }})"
                                        >
                                            {{ $item['label'] }}
                                        </button>
                                    @endforeach
                                </div>
                            @endif

                            @if (mb_strlen(trim((string) $notdinKpaSearch)) >= 2 && empty($notdinKpaOptions) && empty($notdin_kpa_id))
                                <small class="text-danger d-block mt-1">Data tidak ditemukan.</small>
                            @endif

                            @error('notdin_kpa_id')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        <x-form.input label="Penyedia" model="penyedia" required />
                        <x-form.input label="NPWP Penyedia" model="npwp" required />
                        <x-form.textarea label="Alamat" model="alamat" required />
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