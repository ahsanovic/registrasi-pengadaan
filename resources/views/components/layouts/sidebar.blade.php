<aside class="app-menubar" id="appMenubar">
    <div class="app-navbar-brand">
    <a class="navbar-brand-logo" href="">
        <img src="assets/images/logo.svg" alt="Logo">
    </a>
    <a class="navbar-brand-mini visible-light" href="">
        <img src="assets/images/logo-text.svg" alt="Logo">
    </a>
    <a class="navbar-brand-mini visible-dark" href="">
        <img src="assets/images/logo-text-white.svg" alt="Logo">
    </a>
    </div>
    <nav class="app-navbar" data-simplebar>
        <ul class="menubar">
            <li class="menu-item">
                <a class="menu-link" href="{{ route('dashboard') }}" wire:navigate wire:current.exact="active">
                    <i class="fi fi-rr-apps"></i>
                    <span class="menu-label">Dashboard</span>
                </a>
            </li>
            <li class="menu-heading">
                <span class="menu-label">Apps & Pages</span>
            </li>
            <li class="menu-item">
                <a class="menu-link" href="{{ route('notdin-kpa') }}" wire:navigate wire:current.exact="active">
                    <i class="fi fi-rr-envelope"></i>
                    <span class="menu-label">Nota Dinas KPA</span>
                </a>
            </li>
            <li class="menu-item">
                <a class="menu-link" href="{{ route('notdin-ppkom') }}" wire:navigate wire:current.exact="active">
                    <i class="fi fi-rr-envelope"></i>
                    <span class="menu-label">Nota Dinas PPKom</span>
                </a>
            </li>
            <li class="menu-item">
                <a class="menu-link" href="{{ route('dokumen-pengadaan') }}" wire:navigate wire:current.exact="active">
                    <i class="fi fi-rr-envelope"></i>
                    <span class="menu-label">Dokumen Pengadaan</span>
                </a>
            </li>
            @if (auth()->user()->role == 'admin')
            <li class="menu-heading">
                <span class="menu-label">Settings</span>
            </li>
            <li class="menu-item">
                <a class="menu-link" href="{{ route('tanggal-libur') }}" wire:navigate wire:current.exact="active">
                    <i class="fi fi-rr-calendar"></i>
                    <span class="menu-label">Tanggal Libur</span>
                </a>
            </li>
            <li class="menu-item">
                <a class="menu-link" href="{{ route('space-nomor') }}" wire:navigate wire:current.exact="active">
                    <i class="fi fi-rr-list"></i>
                    <span class="menu-label">Space Nomor</span>
                </a>
            </li>
            @endif
        </ul>
    </nav>
    <div class="app-footer">
    <a href="" class="btn btn-outline-light waves-effect btn-shadow btn-app-nav w-100">
        <i class="fi fi-rs-interrogation text-primary"></i>
        <span class="nav-text">Help and Support</span>
    </a>
    </div>
</aside>