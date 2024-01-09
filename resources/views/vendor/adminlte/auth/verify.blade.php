@extends('adminlte::auth.auth-page', ['auth_type' => 'Verificar E-mail'])

@section('auth_header', __('adminlte::adminlte.verify_message'))

@section('auth_body')
    <div class="container-scroller">
        <div class="container-fluid page-body-wrapper full-page-wrapper">
            <div class="content-wrapper d-flex align-items-center auth auth-bg-1 theme-one">
                <div class="row w-100">
                    <div class="col-lg-4 mx-auto">
                        <div class="auto-form-wrapper">

                            @if(session('resent'))
                                <div class="alert alert-success" role="alert">
                                    {{ __('adminlte::adminlte.verify_email_sent') }}
                                </div>
                            @endif

                            <div class="text-center">
                                Antes de continuar, por favor verifique seu email com o link de confirmação.
                                Caso não tenha recebido o email,

                                <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
                                    @csrf
                                    <button type="submit" class="btn btn-link p-0 mt-3 mb-0 align-baseline">
                                        {{ __('adminlte::adminlte.verify_request_another') }}
                                    </button><br>
                                    para {{ auth()->user()->email }}.
                                </form>
                            </div>
                        </div>
                        <div class="auto-form-wrapper mt-5">
                            <div class="text-center mb-4">
                                Se esse não é seu endereço de e-mail,<br>
                                <a class="auth-link" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">clique aqui para sair e iniciar uma nova sessão</a>.
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
