@extends('adminlte::auth.auth-page', ['auth_type' => 'Verificar E-mail'])

@section('auth_header', __('adminlte::adminlte.verify_message'))

@section('auth_body')
    <div class="container-scroller">
        <div class="container-fluid page-body-wrapper full-page-wrapper">
            <div class="content-wrapper d-flex align-items-stretch auth auth-img-bg">
                <div class="row flex-grow">
                    <div class="col-lg-12 d-flex align-items-center justify-content-center flex-wrap">
                        <div class="auth-form-transparent col-md-6 p-2">
                            @if($errors->has('email'))
                                <div class="invalid-feedback">
                                    <strong>{{ $errors->first('email') }}</strong>
                                </div>
                            @endif
                            @if($errors->has('password'))
                                <div class="invalid-feedback">
                                    <strong>{{ $errors->first('password') }}</strong>
                                </div>
                            @endif
                            @if ($errors->any())
                                <div class="alert alert-warning">
                                    <ol>
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ol>
                                </div>
                            @endif
                            @if(session('resent'))
                                <div class="alert alert-success" role="alert">
                                    {{ __('adminlte::adminlte.verify_email_sent') }}
                                </div>
                            @endif
                            <div class="brand-logo d-flex justify-content-center">
                                <img src="{{ asset('assets/images/system/logotipo.png') }}" alt="logo">
                            </div>

                            <div class="text-center">
                                Antes de continuar, por favor verifique seu email com o link de confirmação.<br>
                                Caso não tenha recebido o email,

                                <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
                                    @csrf
                                    <button type="submit" class="btn btn-link p-0 mt-3 mb-0 align-baseline">
                                        {{ __('adminlte::adminlte.verify_request_another') }}
                                    </button><br>
                                    para {{ auth()->user()->email }}.
                                </form>
                            </div>

                            <div class="text-center mt-5">
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
