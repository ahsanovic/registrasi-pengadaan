<?php

use Livewire\Component;
use App\Models\RefBidang;
use App\Models\NotdinKpa;
use App\Models\SpaceNomor;
use App\Models\TanggalLibur;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use Carbon\Carbon;
use App\Services\AgendaNumberService;
use Illuminate\Support\Facades\DB;

new class extends Component
{
    use WithPagination;

    public $bidang_list;
    public $rencana_kegiatan;
    public $rencana_anggaran;
    public $nomor_agenda;
    public $tanggal;
    public $program;
    public $kegiatan;
    public $mata_anggaran;
    public $selectedId;

    // filter state
    public $filter_tgl;
    public $filter_bidang_id;
    public $filter_rencana_kegiatan;

    // Modal state
    public $showModal = false;
    public $isUpdate = false;
    public $editId;

    public function mount()
    {
        $this->bidang_list = RefBidang::where('parent_id', '!=', '1')->get();
        $this->filter_rencana_kegiatan = trim((string) request()->query('search', ''));
    }

    public function resetFilters()
    {
        $this->reset(['filter_bidang_id', 'filter_tgl', 'filter_rencana_kegiatan']);
        $this->resetPage();
    }

    public function with()
    {
        $user = auth()->user();
        $query = NotdinKpa::query();

        if ($user->role != 'admin') {
            $query->where('bidang_id', $user->bidang_id);
        }

        $notdinKpa = $query
            ->when($this->filter_bidang_id, function ($query) {
                $query->where('bidang_id', $this->filter_bidang_id);
            })
            ->when($this->filter_tgl, function ($query) {
                $query->where('tanggal', Carbon::parse($this->filter_tgl)->format('Y-m-d'));
            })
            ->when($this->filter_rencana_kegiatan, function ($query) {
                $query->where('rencana_kegiatan', 'like', '%' . $this->filter_rencana_kegiatan . '%');
            })
            ->orderBy('nomor_agenda', 'desc')
            ->paginate(10);

        return [
            'notdinKpa' => $notdinKpa
        ];
    }

    public function save()
    {
        $this->validate([
            'tanggal' => 'required|date',
            'program' => 'required|string|max:255',
            'kegiatan' => 'required|string|max:255',
            'mata_anggaran' => 'required|string|max:255',
            'rencana_kegiatan' => 'required|string',
            'rencana_anggaran' => 'required|numeric',
        ], [
            'tanggal.required' => 'Tanggal harus diisi',
            'tanggal.date' => 'Tanggal harus berupa tanggal',
            'program.required' => 'harus diisi',
            'program.string' => 'harus berupa string',
            'program.max' => 'maksimal 255 karakter',
            'kegiatan.required' => 'harus diisi',
            'kegiatan.string' => 'harus berupa string',
            'kegiatan.max' => 'maksimal 255 karakter',
            'mata_anggaran.required' => 'harus diisi',
            'mata_anggaran.string' => 'harus berupa string',
            'mata_anggaran.max' => 'maksimal 255 karakter',
            'rencana_kegiatan.required' => 'harus diisi',
            'rencana_kegiatan.string' => 'harus berupa string',
            'rencana_anggaran.required' => 'harus diisi',
            'rencana_anggaran.numeric' => 'harus berupa angka',
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
            NotdinKpa::find($this->editId)->update([
                'tanggal' => Carbon::parse($this->tanggal)->format('Y-m-d'),
                'program' => $this->program,
                'kegiatan' => $this->kegiatan,
                'mata_anggaran' => $this->mata_anggaran,
                'rencana_kegiatan' => $this->rencana_kegiatan,
                'rencana_anggaran' => $this->rencana_anggaran,
            ]);
            $this->closeModal();
            $this->dispatch('toast', ['type' => 'success', 'message' => 'berhasil diperbarui']);
            $this->resetFilters();
            return;
        }

        $nomor_agenda = app(AgendaNumberService::class)
            ->reserveForDate($this->tanggal);

        $this->nomor_agenda = $nomor_agenda;

        NotdinKpa::create([
            'nomor_agenda' => $this->nomor_agenda,
            'bidang_id' => auth()->user()->bidang_id,
            'tanggal' => Carbon::parse($this->tanggal)->format('Y-m-d'),
            'program' => $this->program,
            'kegiatan' => $this->kegiatan,
            'mata_anggaran' => $this->mata_anggaran,
            'rencana_kegiatan' => $this->rencana_kegiatan,
            'rencana_anggaran' => $this->rencana_anggaran,
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
        $notdin_kpa = NotdinKpa::with('notdinPpkom.dokumenPengadaan')->find($this->selectedId);

        if (!$notdin_kpa) {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'data tidak ditemukan']);
            return;
        }

        DB::transaction(function () use ($notdin_kpa) {
            $nomorAgendaPpkom = $notdin_kpa->notdinPpkom
                ->pluck('nomor_agenda')
                ->filter()
                ->unique()
                ->values();

            $nomorAgendaDokumen = $notdin_kpa->notdinPpkom
                ->flatMap(fn ($ppkom) => $ppkom->dokumenPengadaan->pluck('nomor_agenda'))
                ->filter()
                ->unique()
                ->values();

            $nomorAgenda = $nomorAgendaDokumen
                ->merge($nomorAgendaPpkom)
                ->push($notdin_kpa->nomor_agenda)
                ->filter()
                ->unique()
                ->values();

            if ($nomorAgenda->isNotEmpty()) {
                SpaceNomor::whereIn('nomor_agenda', $nomorAgenda)->update(['used_at' => null]);
            }

            // Hapus child terlebih dahulu agar aman dari FK constraint.
            $notdin_kpa->notdinPpkom->each(function ($ppkom) {
                $ppkom->dokumenPengadaan()->delete();
                $ppkom->delete();
            });

            $notdin_kpa->delete();
        });

        $this->dispatch('toast', ['type' => 'success', 'message' => 'berhasil dihapus']);
    }

    public function edit($id)
    {
        $data = NotdinKpa::find($id);
        $this->tanggal = $data->tanggal;
        $this->program = $data->program;
        $this->kegiatan = $data->kegiatan;
        $this->mata_anggaran = $data->mata_anggaran;
        $this->rencana_kegiatan = $data->rencana_kegiatan;
        $this->rencana_anggaran = str_replace('Rp ', '', str_replace('.', '', $data->rencana_anggaran));
        $this->editId = $id;
        $this->showModal = true;
        $this->isUpdate = true;
        $this->resetValidation();
        $this->dispatch('modalOpened');
    }

    public function openModal()
    {
        $this->resetValidation();
        $this->reset(['tanggal', 'program', 'kegiatan', 'mata_anggaran', 'rencana_kegiatan', 'rencana_anggaran', 'editId', 'isUpdate']);
        $this->showModal = true;
        $this->dispatch('modalOpened');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetValidation();
        $this->reset(['tanggal', 'program', 'kegiatan', 'mata_anggaran', 'rencana_kegiatan', 'rencana_anggaran']);
    }
};
?>
<div>
    <div class="app-page-head">
        <h1 class="app-page-title">Nota Dinas KPA</h1>
        <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}" wire:navigate>Dashboard</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Nota Dinas KPA</li>
        </ol>
        </nav>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <button class="btn btn-outline-secondary" wire:click="openModal"><i class="icon-square-plus me-2"></i> Tambah Nota Dinas KPA</button>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3"><i class="icon-list-filter me-2"></i> Filter</h5>
                    <div class="row mb-2">
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
                        <div class="col-2">
                            <div class="form-group">
                                <input type="text" class="form-control" wire:model.live="filter_tgl" id="flatpickr_basic" placeholder="Pilih Tanggal" readonly>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="form-group">
                                <input type="text" class="form-control" wire:model.live="filter_rencana_kegiatan" placeholder="Rencana Kegiatan" autocomplete="off">
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
                    <h6 class="card-title mb-0">Data Nota Dinas KPA</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-border-bottom-0 mb-0">
                            <thead style="background: #eef3f8 !important;">
                                <tr>
                                    <th class="">#</th>
                                    <th class="minw-100px">No. Agenda</th>
                                    <th class="minw-100px">Tanggal</th>
                                    <th class="minw-100px">Bidang</th>
                                    <th class="minw-150px">Program</th>
                                    <th class="minw-150px">Kegiatan</th>
                                    <th class="minw-150px">Mata Anggaran</th>
                                    <th class="minw-200px">Rencana Kegiatan</th>
                                    <th class="minw-150px">Rencana Anggaran</th>
                                    <th class="text-end minw-150px">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($notdinKpa as $item)
                                    <tr>
                                        <td>{{ $notdinKpa->firstItem() + $loop->index }}</td>
                                        <td>{{ $item->nomor_agenda }}</td>
                                        <td>{{ $item->tanggal }}</td>
                                        <td>{{ $item->bidang->nama }}</td>
                                        <td>{{ $item->program }}</td>
                                        <td>{{ $item->kegiatan }}</td>
                                        <td>{{ $item->mata_anggaran }}</td>
                                        <td>{{ $item->rencana_kegiatan }}</td>
                                        <td>{{ $item->rencana_anggaran }}</td>
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
                        :hasPages="$notdinKpa->hasPages()"
                        :currentPage="$notdinKpa->currentPage()"
                        :lastPage="$notdinKpa->lastPage()"
                        :onFirstPage="$notdinKpa->onFirstPage()"
                        :hasMorePages="$notdinKpa->hasMorePages()"
                        :getUrlRange="$notdinKpa->getUrlRange(1, $notdinKpa->lastPage())"
                    />
                </div>
            </div>
        </div>

    <!-- Modal -->
    @if ($showModal)
    <div class="modal fade show" id="addNotdinKpaModal" tabindex="-1" aria-hidden="true" style="display: block;"
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
                                {{ $isUpdate ? 'Edit Nota Dinas KPA' : 'Tambah Nota Dinas KPA Baru' }}
                            </h5>
                            <p class="text-white-50 mb-0 mt-1" style="font-size: 0.875rem;">
                                {{ $isUpdate ? 'Perbarui informasi nota dinas kpa' : 'Isi form untuk menambahkan nota dinas kpa' }}
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
                        <x-form.input label="Program" model="program" required />
                        <x-form.input label="Kegiatan" model="kegiatan" required />
                        <x-form.input label="Mata Anggaran" model="mata_anggaran" required />
                        <x-form.textarea label="Rencana Kegiatan" model="rencana_kegiatan" required />
                        <x-form.input label="Rencana Anggaran" model="rencana_anggaran" required />
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