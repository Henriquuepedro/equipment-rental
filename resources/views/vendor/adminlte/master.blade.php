<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    {{-- Base Meta Tags --}}
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" href="{{ asset('assets/vendors/iconfonts/mdi/css/materialdesignicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/iconfonts/ionicons/dist/css/ionicons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/iconfonts/flag-icon-css/css/flag-icon.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/css/vendor.bundle.addons.css') }}">

    <!-- inject:css -->
    <link rel="stylesheet" href="{{ asset('assets/css/shared/style.css') }}">
    <!-- endinject -->
    <!-- Layout styles -->
    <link rel="stylesheet" href="{{ asset('assets/css/demo_1/style.css') }}">

    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}" />

    {{-- Custom Meta Tags --}}
    @yield('meta_tags')

    {{-- Title --}}
    <title>
        @yield('title_prefix', config('adminlte.title_prefix', ''))
        @yield('title', config('adminlte.title', 'AdminLTE 3'))
        @yield('title_postfix', config('adminlte.title_postfix', ''))
    </title>

    {{-- Custom stylesheets (pre AdminLTE) --}}
    @yield('adminlte_css_pre')

    {{-- Base Stylesheets --}}
{{--    @if(!config('adminlte.enabled_laravel_mix'))--}}
{{--        <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">--}}
{{--        <link rel="stylesheet" href="{{ asset('vendor/overlayScrollbars/css/OverlayScrollbars.min.css') }}">--}}

{{--        --}}{{-- Configured Stylesheets --}}
{{--        @include('adminlte::plugins', ['type' => 'css'])--}}

{{--        <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">--}}
{{--        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">--}}
{{--    @else--}}
{{--        <link rel="stylesheet" href="{{ mix(config('adminlte.laravel_mix_css_path', 'css/app.css')) }}">--}}
{{--    @endif--}}

    {{-- Livewire Styles --}}
{{--    @if(config('adminlte.livewire'))--}}
{{--        @if(app()->version() >= 7)--}}
{{--            @livewireStyles--}}
{{--        @else--}}
{{--            <livewire:styles />--}}
{{--        @endif--}}
{{--    @endif--}}

    {{-- Custom Stylesheets (post AdminLTE) --}}
    @yield('adminlte_css')

    {{-- Favicon --}}
{{--    @if(config('adminlte.use_ico_only'))--}}
{{--        <link rel="shortcut icon" href="{{ asset('favicons/favicon.ico') }}" />--}}
{{--    @elseif(config('adminlte.use_full_favicon'))--}}
{{--        <link rel="shortcut icon" href="{{ asset('favicons/favicon.ico') }}" />--}}
{{--        <link rel="apple-touch-icon" sizes="57x57" href="{{ asset('favicons/apple-icon-57x57.png') }}">--}}
{{--        <link rel="apple-touch-icon" sizes="60x60" href="{{ asset('favicons/apple-icon-60x60.png') }}">--}}
{{--        <link rel="apple-touch-icon" sizes="72x72" href="{{ asset('favicons/apple-icon-72x72.png') }}">--}}
{{--        <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('favicons/apple-icon-76x76.png') }}">--}}
{{--        <link rel="apple-touch-icon" sizes="114x114" href="{{ asset('favicons/apple-icon-114x114.png') }}">--}}
{{--        <link rel="apple-touch-icon" sizes="120x120" href="{{ asset('favicons/apple-icon-120x120.png') }}">--}}
{{--        <link rel="apple-touch-icon" sizes="144x144" href="{{ asset('favicons/apple-icon-144x144.png') }}">--}}
{{--        <link rel="apple-touch-icon" sizes="152x152" href="{{ asset('favicons/apple-icon-152x152.png') }}">--}}
{{--        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicons/apple-icon-180x180.png') }}">--}}
{{--        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicons/favicon-16x16.png') }}">--}}
{{--        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicons/favicon-32x32.png') }}">--}}
{{--        <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('favicons/favicon-96x96.png') }}">--}}
{{--        <link rel="icon" type="image/png" sizes="192x192"  href="{{ asset('favicons/android-icon-192x192.png') }}">--}}
{{--        <link rel="manifest" href="{{ asset('favicons/manifest.json') }}">--}}
{{--        <meta name="msapplication-TileColor" content="#ffffff">--}}
{{--        <meta name="msapplication-TileImage" content="{{ asset('favicon/ms-icon-144x144.png') }}">--}}
{{--    @endif--}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css"
          integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A=="
          crossorigin=""/>
    <link rel="stylesheet" href="{{ asset('vendor/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.0/css/all.css">
    <link rel="stylesheet" href="{{ asset('dist/css/styles.css') }}?v=1.00">

</head>

<body>

    {{-- Body Content --}}
    @yield('body')

    {{-- Base Scripts --}}
{{--    @if(!config('adminlte.enabled_laravel_mix'))--}}
{{--        <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>--}}
{{--        <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>--}}
{{--        <script src="{{ asset('vendor/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>--}}

{{--        --}}{{-- Configured Scripts --}}
{{--        @include('adminlte::plugins', ['type' => 'js'])--}}

{{--        <script src="{{ asset('vendor/adminlte/dist/js/adminlte.min.js') }}"></script>--}}
{{--    @else--}}
{{--        <script src="{{ mix(config('adminlte.laravel_mix_js_path', 'js/app.js')) }}"></script>--}}
{{--    @endif--}}

    {{-- Livewire Script --}}
{{--    @if(config('adminlte.livewire'))--}}
{{--        @if(app()->version() >= 7)--}}
{{--            @livewireScripts--}}
{{--        @else--}}
{{--            <livewire:scripts />--}}
{{--        @endif--}}
{{--    @endif--}}




    <!-- plugins:js -->
    <script src="{{ asset('assets/vendors/js/vendor.bundle.base.js') }}"></script>
    <script src="{{ asset('assets/vendors/js/vendor.bundle.addons.js') }}"></script>
    {{-- Libs --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.2/jquery.validate.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.2/additional-methods.min.js"></script>
    <script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/pipeline.js') }}" type="text/javascript"></script>
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"
            integrity="sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA=="
            crossorigin=""></script>
    <!-- inject:js -->
    <script src="{{ asset('assets/js/shared/off-canvas.js') }}"></script>
    <script src="{{ asset('assets/js/shared/hoverable-collapse.js') }}"></script>
    <script src="{{ asset('assets/js/shared/misc.js') }}"></script>
    <script src="{{ asset('assets/js/shared/settings.js') }}"></script>
    <script src="{{ asset('assets/js/shared/todolist.js') }}"></script>
    <script src="{{ asset('dist/js/demo_.js') }}?v=1.00"></script>
    {{-- Custom Scripts --}}
    @yield('adminlte_js')

</body>

</html>
