<!DOCTYPE html>

<!-- =========================================================
* Sneat - Bootstrap 5 HTML Admin Template - Pro | v1.0.0
==============================================================

* Product Page: https://themeselection.com/products/sneat-bootstrap-html-admin-template/
* Created by: ThemeSelection
* License: You must have a valid license purchased in order to legally use the theme for your project.
* Copyright ThemeSelection (https://themeselection.com)

=========================================================
 -->
<!-- beautify ignore:start -->
  <html
    lang="en"
    class="light-style layout-menu-fixed"
    dir="ltr"
    data-theme="theme-default"
    data-assets-path="{{ asset('assets') }}/"
    data-template="vertical-menu-template-free"
  >
  <head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
    />

    <title>@yield('title', 'Dashboard - SimLabKom')</title>

    <meta name="description" content="" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets') }}/img/favicon/favicon.ico" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
      rel="stylesheet"
    />

    <!-- Icons. Uncomment required icon fonts -->
    <link rel="stylesheet" href="{{ asset('assets') }}/vendor/fonts/boxicons.css" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('assets') }}/vendor/css/core.css" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('assets') }}/vendor/css/theme-default.css" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{ asset('assets') }}/css/demo.css" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{ asset('assets') }}/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />

    <link rel="stylesheet" href="{{ asset('assets') }}/vendor/libs/apex-charts/apex-charts.css" />

    <!-- Page CSS -->

    <style>
        .badge-notifications {
            position: absolute;
            top: -4px;
            right: -6px;
            font-size: 10px;
            padding: 2px 6px;
        }
        .nav-link {
            position: relative;
        }
        .d-none {
            display: none !important;
        }

        .custom-pagination {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 0;
            margin: 2rem 0 0;
            list-style: none;
            flex-wrap: wrap;
        }
        .custom-pagination-item {
            display: inline-flex;
        }
        .custom-pagination-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            min-width: 38px;
            height: 38px;
            padding: 0 14px;
            font-size: 0.875rem;
            font-weight: 500;
            line-height: 1;
            color: #566a7f;
            background-color: #fff;
            border: 1px solid #d9dee3;
            border-radius: 0.5rem;
            text-decoration: none;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            user-select: none;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02);
        }
        .custom-pagination-link:hover {
            color: #059669;
            background-color: #f8f9fc;
            border-color: #059669;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(105, 108, 255, 0.15);
        }
        .custom-pagination-item.active .custom-pagination-link {
            color: #fff;
            background-color: #059669;
            border-color: #059669;
            box-shadow: 0 4px 12px rgba(105, 108, 255, 0.35);
            transform: translateY(-1px);
        }
        .custom-pagination-item.disabled .custom-pagination-link {
            color: #a5a9ae;
            background-color: #f5f5f9;
            border-color: #e9e9f0;
            cursor: not-allowed;
            pointer-events: none;
            box-shadow: none;
        }
        .custom-pagination-arrow {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            line-height: 1;
        }

        .custom-pagination-simple {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin: 2rem 0 0;
        }
        .custom-pagination-simple-item {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 20px;
            font-size: 0.875rem;
            font-weight: 500;
            line-height: 1;
            color: #566a7f;
            background-color: #fff;
            border: 1px solid #d9dee3;
            border-radius: 0.5rem;
            text-decoration: none;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02);
        }
        .custom-pagination-simple-item:hover {
            color: #059669;
            background-color: #f8f9fc;
            border-color: #059669;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(105, 108, 255, 0.15);
        }
        .custom-pagination-simple-item.disabled {
            color: #a5a9ae;
            background-color: #f5f5f9;
            border-color: #e9e9f0;
            cursor: not-allowed;
            pointer-events: none;
            box-shadow: none;
        }
        .custom-pagination-arrow {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            line-height: 1;
        }
    </style>

    <!-- Helpers -->
    <script src="{{ asset('assets') }}/vendor/js/helpers.js"></script>

    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="{{ asset('assets') }}/js/config.js"></script>
  </head>

  <body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
      <div class="layout-container">
        <!-- Menu -->

        @include('partials.sidebar')
        <!-- / Menu -->

        <!-- Layout container -->
        <div class="layout-page">
          <!-- Navbar -->
          @include('partials.navbar')

          <!-- / Navbar -->

          @yield('content')

            <!-- Footer -->
            @include('partials.footer')
            <!-- / Footer -->

            <div class="content-backdrop fade"></div>
          </div>
          <!-- Content wrapper -->
        </div>
        <!-- / Layout page -->
      </div>

      <!-- Overlay -->
      <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    <!-- / Layout wrapper -->

    <!-- Core JS -->
    <!-- build:js assets/vendor/js/core.js -->
    <script src="{{ asset('assets') }}/vendor/libs/jquery/jquery.js"></script>
    <script src="{{ asset('assets') }}/vendor/libs/popper/popper.js"></script>
    <script src="{{ asset('assets') }}/vendor/js/bootstrap.js"></script>
    <script src="{{ asset('assets') }}/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>

    <script src="{{ asset('assets') }}/vendor/js/menu.js"></script>
    <!-- endbuild -->

    <!-- Vendors JS -->
    <script src="{{ asset('assets') }}/vendor/libs/apex-charts/apexcharts.js"></script>

    <!-- Main JS -->
    <script src="{{ asset('assets') }}/js/main.js"></script>

    <!-- Page JS -->
    <script src="{{ asset('assets') }}/js/dashboards-analytics.js"></script>

    @stack('scripts')

    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Konfirmasi Hapus</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            Apakah Anda yakin ingin menghapus data ini?
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
            <form id="deleteModalForm" method="POST" class="d-inline">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn btn-danger">Hapus</button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <script>
      document.addEventListener('DOMContentLoaded', function () {
        var deleteModal = document.getElementById('deleteModal');
        var deleteModalForm = document.getElementById('deleteModalForm');
        if (deleteModal && deleteModalForm) {
          deleteModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var action = button.getAttribute('data-delete-action');
            if (action) {
              deleteModalForm.setAttribute('action', action);
            }
          });
        }
      });
    </script>

    <!-- Place this tag in your head or just before your close body tag. -->
    <script async defer src="https://buttons.github.io/buttons.js"></script>
  </body>
</html>
