<?php

use Livewire\Component;
use App\Models\RefBidang;
use App\Models\DokumenPengadaan;
use App\Models\NotdinPpkom;
use App\Models\SpaceNomor;
use App\Models\RefJenisDokumen;
use App\Models\TanggalLibur;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use Carbon\Carbon;
use App\Services\AgendaNumberService;
use Livewire\Attributes\Computed;

new class extends Component
{
    use WithPagination;

    public $bidang_list;
    public $jenis_dokumen_list;
    public $tanggal;
    public $notdin_ppkom_id;
    public $jenis_dokumen_id;
    public $selectedId;
    public $notdinPpkomSearch = '';
    public $notdinPpkomOptions = [];
    public $showNotdinPpkomSuggestions = false;
    public $isSelectingNotdinPpkom = false;

    // filter state
    public $filter_tgl;
    public $filter_bidang_id;
    public $filter_penyedia;
    public $filter_rencana_kegiatan;
    public $filter_jenis_dokumen_id;

    // Modal state
    public $showModal = false;
    public $isUpdate = false;
    public $editId;

    public function mount()
    {
        $this->bidang_list = RefBidang::where('parent_id', '!=', '1')->get();
        $this->jenis_dokumen_list = RefJenisDokumen::all();
    }

    public function resetFilters()
    {
        $this->reset(['filter_bidang_id', 'filter_tgl', 'filter_penyedia', 'filter_rencana_kegiatan', 'filter_jenis_dokumen_id']);
        $this->resetPage();
    }

    #[Computed]
    public function dokumenPengadaan()
    {
        $user = auth()->user();
        $query = DokumenPengadaan::query();

        if ($user->role != 'admin') {
            $query->whereHas('notdinPpkom.notdinKpa', function ($query) use ($user) {
                $query->where('bidang_id', $user->bidang_id);
            });
        }
        $dokumenPengadaan = $query->with('jenisDokumen', 'notdinPpkom', 'notdinPpkom.notdinKpa')->when($this->filter_bidang_id, function ($query) {
            $query->whereHas('notdinPpkom.notdinKpa', function ($query) {
                $query->where('bidang_id', $this->filter_bidang_id);
            });
        })
        ->when($this->filter_tgl, function ($query) {
            $query->where('tanggal', Carbon::parse($this->filter_tgl)->format('Y-m-d'));
        })
        ->when($this->filter_penyedia, function ($query) {
            $query->whereHas('notdinPpkom', function ($query) {
                $query->where('penyedia', 'like', '%' . $this->filter_penyedia . '%');
            });
        })
        ->when($this->filter_rencana_kegiatan, function ($query) {
            $query->whereHas('notdinPpkom.notdinKpa', function ($query) {
                $query->where('rencana_kegiatan', 'like', '%' . $this->filter_rencana_kegiatan . '%');
            });
        })
        ->when($this->filter_jenis_dokumen_id, function ($query) {
            $query->where('jenis_dokumen_id', $this->filter_jenis_dokumen_id);
        })
        ->orderBy('nomor_agenda', 'desc')
        ->paginate(10);

        return $dokumenPengadaan;
    }

    public function updatedNotdinPpkomSearch($value)
    {
        if ($this->isSelectingNotdinPpkom) {
            return;
        }

        $keyword = trim((string) $value);
        $this->notdin_ppkom_id = null;

        if (mb_strlen($keyword) < 2) {
            $this->notdinPpkomOptions = [];
            $this->showNotdinPpkomSuggestions = false;
            return;
        }

        $user = auth()->user();
        $query = NotdinPpkom::query();

        if ($user->role != 'admin') {
            $query->whereHas('notdinKpa', function ($query) use ($user) {
                $query->where('bidang_id', $user->bidang_id);
            });
        }

        $this->notdinPpkomOptions = $query
            ->with('notdinKpa:id,bidang_id,mata_anggaran,rencana_kegiatan', 'notdinKpa.bidang:id,nama')
            ->whereHas('notdinKpa', function ($query) use ($keyword) {
                $query->where('mata_anggaran', 'like', '%' . $keyword . '%')
                    ->orWhere('rencana_kegiatan', 'like', '%' . $keyword . '%')
                    ->orWhereHas('bidang', function ($subQuery) use ($keyword) {
                        $subQuery->where('nama', 'like', '%' . $keyword . '%');
                    });
            })
            ->orderByDesc('id')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                $mataAnggaran = $item->notdinKpa->mata_anggaran ?? '-';
                $rencanaKegiatan = $item->notdinKpa->rencana_kegiatan ?? '-';
                $penyedia = $item->penyedia ?? '-';
                $bidang = $item->notdinKpa->bidang->nama ?? '-';
                return [
                    'id' => $item->id,
                    'label' => $bidang . ' - ' . $mataAnggaran . ' - ' . $rencanaKegiatan . ' - ' . $penyedia,
                ];
            })
            ->toArray();

        $this->showNotdinPpkomSuggestions = count($this->notdinPpkomOptions) > 0;
    }

    public function selectNotdinPpkom($id)
    {
        $item = NotdinPpkom::with('notdinKpa:id,mata_anggaran,rencana_kegiatan')->find($id);

        if (!$item) {
            return;
        }

        $this->isSelectingNotdinPpkom = true;
        $this->notdin_ppkom_id = (int) $item->id;
        $this->notdinPpkomSearch = ($item->notdinKpa->mata_anggaran ?? '-') . ' - ' . ($item->notdinKpa->rencana_kegiatan ?? '-') . ' - ' . ($item->penyedia ?? '-');
        $this->isSelectingNotdinPpkom = false;
        $this->notdinPpkomOptions = [];
        $this->showNotdinPpkomSuggestions = false;
    }

    public function hideNotdinPpkomSuggestions()
    {
        $this->showNotdinPpkomSuggestions = false;
    }

    public function save()
    {
        $rules = [
            'tanggal' => 'required|date',
            'notdin_ppkom_id' => 'required|integer|exists:notdin_ppkom,id',
        ];

        $messages = [
            'tanggal.required' => 'harus diisi',
            'tanggal.date' => 'harus berupa tanggal',
            'notdin_ppkom_id.required' => 'harus diisi',
            'notdin_ppkom_id.integer' => 'pilihan tidak valid',
            'notdin_ppkom_id.exists' => 'pilihan tidak valid',
            'jenis_dokumen_id.required' => 'harus diisi',
            'jenis_dokumen_id.array' => 'harus berupa pilihan jamak',
            'jenis_dokumen_id.min' => 'minimal pilih satu data',
            'jenis_dokumen_id.integer' => 'pilihan tidak valid',
            'jenis_dokumen_id.exists' => 'pilihan tidak valid',
            'jenis_dokumen_id.*.exists' => 'pilihan tidak valid',
        ];

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
            $rules['jenis_dokumen_id'] = 'required|integer|exists:ref_jenis_dokumen,id';
        } else {
            $rules['jenis_dokumen_id'] = 'required|array|min:1';
            $rules['jenis_dokumen_id.*'] = 'exists:ref_jenis_dokumen,id';
        }

        $this->validate($rules, $messages);

        if ($this->isUpdate) {
            DokumenPengadaan::find($this->editId)->update([
                'notdin_ppkom_id' => $this->notdin_ppkom_id,
                'tanggal' => Carbon::parse($this->tanggal)->format('Y-m-d'),
                'jenis_dokumen_id' => $this->jenis_dokumen_id,
            ]);
            $this->closeModal();
            $this->dispatch('toast', ['type' => 'success', 'message' => 'berhasil diperbarui']);
            $this->resetFilters();
            return;
        }

        foreach ($this->jenis_dokumen_id as $jenisDokumenId) {
            $nomor_agenda = app(AgendaNumberService::class)
                ->reserveForDate($this->tanggal);

            DokumenPengadaan::create([
                'notdin_ppkom_id' => $this->notdin_ppkom_id,
                'nomor_agenda' => $nomor_agenda,
                'tanggal' => Carbon::parse($this->tanggal)->format('Y-m-d'),
                'jenis_dokumen_id' => $jenisDokumenId,
            ]);
        }

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
        $dokumen_pengadaan = DokumenPengadaan::find($this->selectedId);
        SpaceNomor::where('nomor_agenda', $dokumen_pengadaan->nomor_agenda)->update(['used_at' => null]);
        $dokumen_pengadaan->delete();

        $this->dispatch('toast', ['type' => 'success', 'message' => 'berhasil dihapus']);
    }

    public function edit($id)
    {
        $data = DokumenPengadaan::find($id);
        $this->tanggal = Carbon::parse($data->tanggal)->format('d-m-Y');
        $this->notdin_ppkom_id = (int) $data->notdin_ppkom_id;
        $notdinPpkom = NotdinPpkom::with('notdinKpa:id,mata_anggaran,rencana_kegiatan')->find($data->notdin_ppkom_id);
        $this->notdinPpkomSearch = $notdinPpkom
            ? (($notdinPpkom->notdinKpa->mata_anggaran ?? '-') . ' - ' . ($notdinPpkom->notdinKpa->rencana_kegiatan ?? '-'))
            : '';
        $this->notdinPpkomOptions = [];
        $this->showNotdinPpkomSuggestions = false;
        $this->jenis_dokumen_id = $data->jenis_dokumen_id;
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
            'notdin_ppkom_id',
            'notdinPpkomSearch',
            'notdinPpkomOptions',
            'showNotdinPpkomSuggestions',
            'isSelectingNotdinPpkom',
            'editId',
            'isUpdate',
        ]);
        $this->jenis_dokumen_id = [];
        $this->showModal = true;
        $this->dispatch('modalOpened');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetValidation();
        $this->reset([
            'tanggal',
            'notdin_ppkom_id',
            'notdinPpkomSearch',
            'notdinPpkomOptions',
            'showNotdinPpkomSuggestions',
            'isSelectingNotdinPpkom',
        ]);
        $this->jenis_dokumen_id = [];
    }
};
?>
<div>
    <div class="app-page-head">
        <h1 class="app-page-title">Dokumen Pengadaan</h1>
        <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}" wire:navigate>Dashboard</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Dokumen Pengadaan</li>
        </ol>
        </nav>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <button class="btn btn-outline-secondary" wire:click="openModal"><i class="icon-square-plus me-2"></i> Tambah Dokumen Pengadaan</button>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3"><i class="icon-list-filter me-2"></i> Filter</h5>
                    <div class="row mb-2">
                        <div class="col-3">
                            <div class="form-group">
                                <input type="text" class="form-control" wire:model.live="filter_penyedia" placeholder="Penyedia" autocomplete="off">
                            </div>
                        </div>
                        <div class="{{ auth()->user()->role == 'admin' ? 'col-5' : 'col-4' }}">
                            <div class="form-group">
                                <input type="text" class="form-control" wire:model.live="filter_rencana_kegiatan" placeholder="Nama Pekerjaan" autocomplete="off">
                            </div>
                        </div>
                    </div>  
                    <div class="row mb-2 mt-3">
                        @if (auth()->user()->role == 'admin')
                            <div class="col-3">
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
                        <div class="{{ auth()->user()->role == 'admin' ? 'col-2' : 'col-3' }}">
                            <div class="form-group">
                                <input type="text" class="form-control" wire:model.live="filter_tgl" id="flatpickr_basic" placeholder="Pilih Tanggal" readonly>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="form-group">
                                <select class="form-control" wire:model.live="filter_jenis_dokumen_id">
                                    <option value="">Semua Jenis Dokumen</option>
                                    @foreach ($jenis_dokumen_list as $item)
                                        <option value="{{ $item->id }}">{{ $item->jenis_dokumen }}</option>
                                    @endforeach
                                </select>
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
                    <h6 class="card-title mb-0">Data Dokumen Pengadaan</h6>
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
                                    <th>Jenis Dokumen</th>
                                    <th class="text-end minw-150px">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($this->dokumenPengadaan as $item)
                                    <tr>
                                        <td>{{ $this->dokumenPengadaan->firstItem() + $loop->index }}</td>
                                        <td>{{ $item->nomor_agenda }}</td>
                                        <td>{{ $item->tanggal }}</td>
                                        <td>{{ $item->notdinPpkom->notdinKpa->bidang->nama }}</td>
                                        <td>{{ $item->notdinPpkom->notdinKpa->mata_anggaran }}</td>
                                        <td>{{ $item->notdinPpkom->notdinKpa->rencana_kegiatan }}</td>
                                        <td>{{ $item->notdinPpkom->notdinKpa->rencana_anggaran }}</td>
                                        <td>{{ $item->notdinPpkom->penyedia }}</td>
                                        <td>{{ $item->jenisDokumen->jenis_dokumen }}</td>
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
                        :hasPages="$this->dokumenPengadaan->hasPages()"
                        :currentPage="$this->dokumenPengadaan->currentPage()"
                        :lastPage="$this->dokumenPengadaan->lastPage()"
                        :onFirstPage="$this->dokumenPengadaan->onFirstPage()"
                        :hasMorePages="$this->dokumenPengadaan->hasMorePages()"
                        :getUrlRange="$this->dokumenPengadaan->getUrlRange(1, $this->dokumenPengadaan->lastPage())"
                    />
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    @if ($showModal)
    <div class="modal fade show" id="addDokumenPengadaanModal" tabindex="-1" aria-hidden="true" style="display: block;"
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
                                {{ $isUpdate ? 'Edit Dokumen Pengadaan' : 'Tambah Dokumen Pengadaan Baru' }}
                            </h5>
                            <p class="text-white-50 mb-0 mt-1" style="font-size: 0.875rem;">
                                {{ $isUpdate ? 'Perbarui informasi dokumen pengadaan' : 'Isi form untuk menambahkan dokumen pengadaan' }}
                            </p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeModal" class="btn-close btn-close-white" 
                            style="filter: brightness(0) invert(1); opacity: 0.8; transition: opacity 0.2s;"
                            onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.8'">
                    </button>
                </div>
                <div class="modal-body">
                    <x-form.date label="Tanggal" model="tanggal" required disabled="{{ $isUpdate }}" />
                    <div class="mb-3 position-relative">
                        <label for="input-notdin-ppkom-search" class="form-label">
                            Mata Anggaran dan Kegiatan <span class="text-danger">*</span>
                        </label>
                        <input
                            id="input-notdin-ppkom-search"
                            type="text"
                            class="form-control @error('notdin_ppkom_id') is-invalid @enderror"
                            placeholder="Ketik minimal 2 huruf untuk mencari"
                            wire:model.live.debounce.300ms="notdinPpkomSearch"
                            wire:focus="$set('showNotdinPpkomSuggestions', true)"
                            wire:keydown.escape="hideNotdinPpkomSuggestions"
                            autocomplete="off"
                        >

                        @if ($showNotdinPpkomSuggestions && !empty($notdinPpkomOptions))
                            <div class="list-group position-absolute w-100 shadow-sm mt-1" style="z-index: 1056; max-height: 240px; overflow-y: auto;">
                                @foreach ($notdinPpkomOptions as $item)
                                    <button
                                        type="button"
                                        class="list-group-item list-group-item-action text-start"
                                        wire:click="selectNotdinPpkom({{ $item['id'] }})"
                                    >
                                        {{ $item['label'] }}
                                    </button>
                                @endforeach
                            </div>
                        @endif

                        @if (mb_strlen(trim((string) $notdinPpkomSearch)) >= 2 && empty($notdinPpkomOptions) && empty($notdin_ppkom_id))
                            <small class="text-danger d-block mt-1">Data tidak ditemukan.</small>
                        @endif

                        @error('notdin_ppkom_id')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>
                    <x-form.select2
                        label="Jenis Dokumen"
                        model="jenis_dokumen_id"
                        required
                        :multiple="!$isUpdate"
                        placeholder="Pilih Jenis Dokumen"
                        notes="pilih beberapa jenis dokumen sekaligus untuk ditambahkan otomatis secara bersamaan di tanggal yang sama"
                    >
                        @foreach ($jenis_dokumen_list as $item)
                            <option value="{{ $item->id }}">{{ $item->jenis_dokumen }}</option>
                        @endforeach
                    </x-form.select2>
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