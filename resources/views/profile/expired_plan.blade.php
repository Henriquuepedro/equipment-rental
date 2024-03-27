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

@section('title', 'Plano Expirado')

@section('body')
    <div class="container-scroller">
        <div class="container-fluid page-body-wrapper full-page-wrapper">
            <div class="content-wrapper d-flex align-items-center auth lock-full-bg">
                <div class="row w-100">
                    <div class="col-lg-12 d-flex align-items-center justify-content-center flex-wrap">
                        <div class="auth-form-transparent col-md-4 p-2 d-flex justify-content-center flex-wrap text-center">
                            <div class="brand-logo d-flex justify-content-center col-md-12">
                                <img src="{{ asset('assets/images/system/logotipo-horizontal-white.png') }}" alt="logo">
                            </div>
                            <p class="text-uppercase mb-0 col-md-12">{{ $settings['name_company'] }}</p>
                            <p class="col-md-12">{{ auth()->user()->__get('email') }}</p>
                            <div class="box box-primary mt-3">
                                <div class="box-body text-center">
                                    <p>Olá, {{ auth()->user()->__get('name') }}.</p>
                                    <p>Seu plano expirou em <u>{{ $settings['plan_expiration_date'] }}</u>, renove o plano para não ficar sem acesso a plataforma.</p>
                                    <p>Caso já tenha efetuado o pagamento, aguarde alguns instantes até que seja processado.</p>
                                </div>
                            </div>
                            <div class="my-3 d-grid gap-2 col-md-12">
                                <a href="{{ route('plan.index') }}" class="btn btn-block btn-primary btn-lg fw-medium auth-form-btn">Renovar Plano</a>
                            </div>
                            <div class="mt-3 text-center col-md-12">
                                <a class="auth-link" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i class="fa fa-power-off"></i> Encerrar Sessão</a>
                            </div>
                        </div>
                    </div>
                </div>
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
