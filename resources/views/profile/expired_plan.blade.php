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
                    <div class="col-lg-4 mx-auto">
                        <div class="auth-form-transparent text-left p-5 text-center">
                            <img src="{{ $settings['img_profile'] }}" class="lock-profile-img rounded-circle mb-3" alt="img">
                            <p class="text-uppercase mb-0">{{ $settings['name_company'] }}</p>
                            <p>{{ auth()->user()->__get('email') }}</p>
                            <div class="box box-primary mt-3">
                                <div class="box-body">
                                    <p>Olá, {{ auth()->user()->__get('name') }}.</p>
                                    <p>Seu plano expirou em <u>{{ $settings['plan_expiration_date'] }}</u>, renove o plano para não ficar sem acesso a plataforma.</p>
                                    <p>Caso já tenha efetuado o pagamento, aguarde alguns instantes até que seja compensado.</p>
                                </div>
                            </div>
                            <div class="mt-3">
                                <a class="btn btn-block btn-success btn-lg font-weight-medium" href="#">Renovar Plano</a>
                            </div>
                            <div class="mt-3 text-center">
                                <a class="auth-link text-white" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i class="fa fa-power-off"></i> Encerrar Sessão</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop

@section('adminlte_js')
    @stack('js')
    @yield('js')
@stop
