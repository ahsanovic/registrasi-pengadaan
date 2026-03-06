<!DOCTYPE html>
<html lang="en">

<head>

  <base href="../">

  <!-- begin::GXON Meta Basic -->
  <meta charset="utf-8">
  <meta name="theme-color" content="#316AFF">
  <meta name="robots" content="index, follow">
  <meta name="author" content="BKD Provinsi Jawa Timur">
  <meta name="format-detection" content="telephone=no">
  <meta name="keywords" content="Sistem Informasi Registrasi Kontrak Pengadaan">
  <meta name="description" content="Sistem Informasi Registrasi Kontrak Pengadaan adalah sistem informasi yang dirancang untuk mengelola pendaftaran kontrak pengadaan.">
  <!-- end::GXON Meta Basic -->

  <!-- begin::GXON Website Page Title -->
  <title>Login | Sistem Informasi Registrasi Kontrak Pengadaan</title>
  <!-- end::GXON Website Page Title -->

  <!-- begin::GXON Mobile Specific -->
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- end::GXON Mobile Specific -->

  <!-- begin::GXON Favicon Tags -->
  <link rel="icon" type="image/png" href="{{ asset('assets/images/favicon.png') }}">
  <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/images/apple-touch-icon.png') }}">
  <!-- end::GXON Favicon Tags -->

  <!-- begin::GXON Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">
  <!-- end::GXON Google Fonts -->

  <!-- begin::GXON Required Stylesheet -->
  <link rel="stylesheet" href="{{ asset('assets/libs/flaticon/css/all/all.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/libs/lucide/lucide.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/libs/fontawesome/css/all.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/libs/simplebar/simplebar.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/libs/node-waves/waves.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/libs/bootstrap-select/css/bootstrap-select.min.css') }}">
  <!-- end::GXON Required Stylesheet -->

  <!-- begin::GXON CSS Stylesheet -->
  <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
  <!-- end::GXON CSS Stylesheet -->

</head>

<body>
  <div class="page-layout">

    <div class="auth-cover-wrapper">
      <div class="row g-0">
        <div class="col-lg-6">
          <div class="auth-cover" style="background-image: url({{ asset('assets/images/auth/auth-cover-bg.png') }});">
            <div class="clearfix">
              <img src="{{ asset('assets/images/auth/auth.png') }}" alt="" class="img-fluid cover-img ms-5">
              <div class="auth-content">
                <h1 class="display-6 fw-bold">Selamat Datang!</h1>
                <p>Sistem Informasi Registrasi Kontrak Pengadaan adalah sistem informasi yang dirancang untuk mengelola pendaftaran kontrak pengadaan di BKD Provinsi Jawa Timur.</p>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-6 align-self-center">
          <div class="p-3 p-sm-5 maxw-450px m-auto auth-inner" data-simplebar>
            <div class="mb-4 text-center">
              <a href="{{ route('login') }}" aria-label="GXON logo">
                <img class="visible-light" src="{{ asset('assets/images/logo-full.svg') }}" alt="GXON logo">
                <img class="visible-dark" src="{{ asset('assets/images/logo-full-white.svg') }}" alt="GXON logo">
              </a>
            </div>
            <div class="text-center mb-5">
              <h5 class="mb-1">Sistem Informasi <br />Registrasi Kontrak Pengadaan</h5>
              <p>Sign in to access your secure account.</p>
            </div>
            <form method="POST" action="{{ route('login.attempt') }}">
              @csrf
              <div class="mb-4">
                <label class="form-label" for="username">Username</label>
                <input type="text" class="form-control" id="username" name="username" placeholder="username" value="{{ old('username') }}" autofocus>
                @error('username')
                  <span class="text-danger">{{ $message }}</span>
                @enderror
              </div>
              <div class="mb-4">
                <label class="form-label" for="loginPassword">Password</label>
                <div class="input-group">
                  <input type="password" class="form-control" id="loginPassword" name="password" placeholder="********">
                  <button class="btn btn-outline-secondary" type="button" id="togglePassword" tabindex="-1" style="border: 1px solid #ced4da; background: #fff;">
                    <i class="fa-solid fa-eye" id="togglePasswordIcon"></i>
                  </button>
                </div>
                @error('password')
                  <span class="text-danger">{{ $message }}</span>
                @enderror
              </div>
              <script>
                document.addEventListener('DOMContentLoaded', function () {
                  const passwordInput = document.getElementById('loginPassword');
                  const togglePasswordBtn = document.getElementById('togglePassword');
                  const togglePasswordIcon = document.getElementById('togglePasswordIcon');
                  if (togglePasswordBtn && passwordInput && togglePasswordIcon) {
                    togglePasswordBtn.addEventListener('click', function () {
                      const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                      passwordInput.setAttribute('type', type);
                      togglePasswordIcon.className = type === 'password' ? 'fa-solid fa-eye' : 'fa-solid fa-eye-slash';
                    });
                  }
                });
              </script>
              <div class="mb-3">
                <button type="submit" class="btn btn-primary waves-effect waves-light w-100">Login</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

  </div>
  <!-- begin::GXON Page Scripts -->
  <script src="{{ asset('assets/libs/global/global.min.js') }}"></script>
  <script src="{{ asset('assets/js/appSettings.js') }}"></script>
  <script src="{{ asset('assets/js/main.js') }}"></script>
  <script>
    window.addEventListener('pageshow', function (event) {
      if (event.persisted) {
        window.location.reload();
      }
    });
  </script>
  <!-- end::GXON Page Scripts -->
</body>

</html>