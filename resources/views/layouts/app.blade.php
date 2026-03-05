<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? config('app.name') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">

        <!-- begin::GXON Required Stylesheet -->
        <link rel="stylesheet" href="{{ asset('assets/libs/flaticon/css/all/all.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/libs/lucide/lucide.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/libs/fontawesome/css/all.min.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/libs/simplebar/simplebar.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/libs/node-waves/waves.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/libs/bootstrap-select/css/bootstrap-select.min.css') }}">
        <!-- end::GXON Required Stylesheet -->
        <!-- begin::GXON CSS Stylesheet -->
        <link rel="stylesheet" href="{{ asset('assets/libs/flatpickr/flatpickr.min.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/libs/sweetalert2/sweetalert2.min.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/libs/select2/select2.min.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
        <!-- end::GXON CSS Stylesheet -->

        <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
    </head>
    <body>
        <div class="page-layout">
            <x-layouts.header />
            <x-layouts.search-modal />
            @livewire('pages::ubah-password')
            <x-layouts.sidebar />

            <main class="app-wrapper">
                <div class="container-fluid px-6">
                    {{ $slot }}
                </div>
            </main>

            <x-layouts.footer />
        </div>

        @livewireScripts

        <script src="{{ asset('assets/libs/global/global.min.js') }}" data-navigate-once></script>
        <script src="{{ asset('assets/libs/chartjs/chart.js') }}" data-navigate-once></script>
        <script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}" data-navigate-once></script>
        <script src="{{ asset('assets/libs/flatpickr/flatpickr.min.js') }}" data-navigate-once></script>
        <script src="{{ asset('assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>
        <script src="{{ asset('assets/libs/select2/select2.min.js') }}" data-navigate-once></script>
        <script src="{{ asset('assets/js/flatpickr.js') }}" data-navigate-once></script>
        <script src="{{ asset('assets/js/sweet-alert.js') }}" data-navigate-once></script>
        <script src="{{ asset('assets/js/select2.js') }}" data-navigate-once></script>
        <script src="{{ asset('assets/js/dashboard.js') }}" data-navigate-once></script>
        <script src="{{ asset('assets/js/appSettings.js') }}" data-navigate-once></script>
        <script src="{{ asset('assets/js/main.js') }}" data-navigate-once></script>
        <!--Toastr-->
        <script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
        @if (session()->has('toast'))
            <script>
                toastr.options = {
                    positionClass: 'toast-top-center'
                };
                toastr.{{ session('toast.type') }}('{{ session('toast.message') }}');
            </script>
        @endif
        <script>
            window.addEventListener('livewire:init', function() {
                Livewire.on('toast', data => {
                    toastr[data[0].type](data[0].message, null, {
                        positionClass: 'toast-top-center'
                    });
                });
            });
            
            window.addEventListener('show-delete-confirmation', data => {
                Swal.fire({
                    title: 'Apakah anda yakin?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Livewire.dispatch('delete');
                    }
                });
            });
        </script>
        <script>
            document.addEventListener('livewire:initialized', () => {
            
                const initFlatpickr = (input) => {
            
                    input._flatpickr?.destroy();
            
                    const model = input.dataset.model;
            
                    // tunggu Livewire hydrate value
                    requestAnimationFrame(() => {
            
                        const fieldWrapper = input.closest('.mb-3, .mb-4');
                        const hiddenInput = fieldWrapper?.querySelector(
                            `input[type="hidden"][wire\\:model="${model}"]`
                        );
            
                        const value = hiddenInput?.value || null;
            
                        input._flatpickr = flatpickr(input, {
                            dateFormat: "d-m-Y",
                            allowInput: false,
                            defaultDate: value,
            
                            onChange: (_, dateStr) => {
                                if (!hiddenInput) return;
                                hiddenInput.value = dateStr;
                                hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
                            }
                        });
                    });
                };
            
                // SAAT MODAL / DOM DITAMBAHKAN
                Livewire.hook('morph.added', ({ el }) => {
                    el.querySelectorAll('[data-flatpickr]').forEach(initFlatpickr);
                });
            
            });
        </script>
        @stack('scripts')
    </body>
</html>
