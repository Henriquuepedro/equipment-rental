@extends('adminlte::master')

@inject('layoutHelper', '\JeroenNoten\LaravelAdminLte\Helpers\LayoutHelper')

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
            @include('vendor.adminlte.partials.sidebar.sidebar')

            <div class="main-panel">
                <div class="content-wrapper">
                    <div class="row block-screen-load"><img src="{{ asset('assets/images/system/load.gif') }}" alt="loading..."/></div>
                    @yield('content')
                </div>
                @include('adminlte::partials.footer.footer')
            </div>
        </div>
    </div>
    <div class="right-sidebar-toggler-wrapper">
        <div class="sidebar-toggler" id="chat-toggler"><i class="mdi mdi-chat-processing"></i></div>
    </div>
    <div id="right-sidebar" class="settings-panel">
        <i class="settings-close mdi mdi-close"></i>
        <div class="d-flex align-items-center justify-content-between border-bottom">
            <p class="settings-heading font-weight-bold border-top-0 mb-3 pl-3 pt-0 border-bottom-0 pb-0">Friends</p>
        </div>
        <ul class="chat-list">
            <li class="list active">
                <div class="profile">
                    <img src="{{ asset('assets/images/faces/face1.jpg') }}" alt="image">
                    <span class="online"></span>
                </div>
                <div class="info">
                    <p>Thomas Douglas</p>
                    <p>Available</p>
                </div>
                <small class="text-muted my-auto">19 min</small>
            </li>
            <li class="list">
                <div class="profile">
                    <img src="{{ asset('assets/images/faces/face2.jpg') }}" alt="image">
                    <span class="offline"></span>
                </div>
                <div class="info">
                    <div class="wrapper d-flex">
                        <p>Catherine</p>
                    </div>
                    <p>Away</p>
                </div>
                <div class="badge badge-success badge-pill my-auto mx-2">4</div>
                <small class="text-muted my-auto">23 min</small>
            </li>
            <li class="list">
                <div class="profile">
                    <img src="{{ asset('assets/images/faces/face3.jpg') }}" alt="image">
                    <span class="online"></span>
                </div>
                <div class="info">
                    <p>Daniel Russell</p>
                    <p>Available</p>
                </div>
                <small class="text-muted my-auto">14 min</small>
            </li>
            <li class="list">
                <div class="profile">
                    <img src="{{ asset('assets/images/faces/face4.jpg') }}" alt="image">
                    <span class="offline"></span>
                </div>
                <div class="info">
                    <p>James Richardson</p>
                    <p>Away</p>
                </div>
                <small class="text-muted my-auto">2 min</small>
            </li>
            <li class="list">
                <div class="profile">
                    <img src="{{ asset('assets/images/faces/face5.jpg') }}" alt="image">
                    <span class="online"></span>
                </div>
                <div class="info">
                    <p>Madeline Kennedy</p>
                    <p>Available</p>
                </div>
                <small class="text-muted my-auto">5 min</small>
            </li>
            <li class="list">
                <div class="profile">
                    <img src="{{ asset('assets/images/faces/face6.jpg') }}" alt="image">
                    <span class="online"></span>
                </div>
                <div class="info">
                    <p>Sarah Graves</p>
                    <p>Available</p>
                </div>
                <small class="text-muted my-auto">47 min</small>
            </li>
        </ul>
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
