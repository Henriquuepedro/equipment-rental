@extends('adminlte::master')

@inject('layoutHelper', \JeroenNoten\LaravelAdminLte\Helpers\LayoutHelper)

@if($layoutHelper->isLayoutTopnavEnabled())
    @php( $def_container_class = 'container' )
@else
    @php( $def_container_class = 'container-fluid' )
@endif

@section('adminlte_css')
    @stack('css')
    @yield('css')
@stop

@section('classes_body', $layoutHelper->makeBodyClasses())

@section('body_data', $layoutHelper->makeBodyData())

@section('body')

    <div class="container-scroller">
        @include('vendor.adminlte.partials.navbar.navbar')
        <div class="container-fluid page-body-wrapper">
            @include('vendor.adminlte.partials.navbar.sidebar')

            <div class="main-panel">
                <div class="content-wrapper">
                    <div class="row block-screen-load"><img src="{{ asset('assets/images/system/load.gif') }}" alt="loading..."/></div>
                    @yield('content')
                </div>
                @include('adminlte::partials.footer.footer')
            </div>
        </div>
    </div>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @if(config('adminlte.logout_method'))
            {{ method_field(config('adminlte.logout_method')) }}
        @endif
        {{ csrf_field() }}
    </form>

@stop

@section('adminlte_js')
    @stack('js')
    @yield('js')
@stop
