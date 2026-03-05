<header class="app-header">
    <div class="app-header-inner">
        <button class="app-toggler" type="button" aria-label="Toggle sidebar" aria-controls="appMenubar" aria-expanded="true">
            <span></span>
            <span></span>
            <span></span>
        </button>
        <div class="app-header-start d-none d-md-flex">
            <form class="d-flex align-items-center h-100 w-lg-250px w-xxl-300px position-relative" action="#">
                <button type="button" class="btn btn-sm border-0 position-absolute start-0 ms-3 p-0">
                    <i class="fi fi-rr-search"></i>
                </button>
                <input type="text" class="form-control rounded-5 ps-5" id="headerSearchInput" placeholder="Cari rencana kegiatan..." data-bs-toggle="modal" data-bs-target="#searchResultsModal">
            </form>
        </div>
        <div class="app-header-end">
            <div class="dropdown text-end ms-sm-3 ms-2 ms-lg-4">
            <a href="#" class="d-flex align-items-center py-2" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                <div class="text-end me-2 d-none d-lg-inline-block">
                <div class="fw-bold text-dark">{{ auth()->user()->username ?? 'User' }}</div>
                    <small class="text-body d-block lh-sm">
                        <i class="fi fi-rr-angle-down text-3xs me-1"></i> {{ auth()->user()->role ?? '-' }}
                    </small>
                </div>
                <div class="avatar avatar-sm rounded-circle avatar-status-success">
                    <img src="assets/images/avatar/avatar1.webp" alt="">
                </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end w-225px mt-1">
                <li class="d-flex align-items-center p-2">
                <div class="avatar avatar-sm rounded-circle">
                    <img src="assets/images/avatar/avatar1.webp" alt="">
                </div>
                <div class="ms-2">
                    <div class="fw-bold text-dark">{{ auth()->user()->username ?? 'User' }}</div>
                    <small class="text-body d-block lh-sm">{{ auth()->user()->role ?? '-' }}</small>
                </div>
                </li>
                <li>
                    <div class="dropdown-divider my-1"></div>
                </li>
                <li>
                    <button
                        type="button"
                        class="dropdown-item d-flex align-items-center gap-2"
                        onclick="window.Livewire?.dispatch('open-ubah-password-modal')"
                    >
                        <i class="fi fi-rr-settings scale-1x"></i> Ubah Password
                    </button>
                </li>
                <li>
                    <div class="dropdown-divider my-1"></div>
                </li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item d-flex align-items-center gap-2 text-danger">
                            <i class="fi fi-sr-exit scale-1x"></i> Log Out
                        </button>
                    </form>
                </li>
            </ul>
            </div>
        </div>
    </div>
</header>