@extends('adminlte::auth.auth-page', ['auth_type' => 'login'])

@php( $password_reset_url = View::getSection('password_reset_url') ?? config('adminlte.password_reset_url', 'password/reset') )

@if (config('adminlte.use_route_url', false))
    @php( $password_reset_url = $password_reset_url ? route($password_reset_url) : '' )
@else
    @php( $password_reset_url = $password_reset_url ? url($password_reset_url) : '' )
@endif

@section('adminlte_css_pre')
    <link rel="stylesheet" href="{{ asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css') }}">
    <link href="{{ asset('vendor/icheck/skins/all.css') }}" rel="stylesheet">
    <style>
        .form-group label {
            background-color: #1f2127 !important;
        }
    </style>
@stop
@section('adminlte_js')
    <script>
        $(function(){
            $('[name="remember"]').iCheck({
                checkboxClass: 'icheckbox_minimal-blue',
                increaseArea: '20%'
            });
        })
    </script>
@stop

@section('auth_body')
    <div class="container-scroller">
        <div class="container-fluid page-body-wrapper full-page-wrapper">
            <div class="content-wrapper d-flex align-items-stretch auth auth-img-bg">
                <div class="row flex-grow">
                    <div class="col-lg-6 d-flex align-items-center justify-content-center">
                        <div class="auth-form-transparent text-left p-3">
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
                            <div class="brand-logo d-flex justify-content-center">
                                <img src="{{ asset('assets/images/system/logotipo.png') }}" alt="logo">
                            </div>
                            <form action="{{ route('login') }}" method="post">
                                {{ csrf_field() }}
                                <div class="form-group">
                                    <label class="label">E-mail</label>
                                    <input type="email" class="form-control form-control-lg border-left-0" name="email" value="{{ old('email') }}" required>
                                </div>
                                <div class="form-group">
                                    <label class="label">Senha</label>
                                    <input type="password" class="form-control form-control-lg border-left-0" name="password" required>
                                </div>
                                <div class="my-2 d-flex justify-content-between align-items-center">
                                    <div class="form-check">
                                        <label class="form-check-label text-muted">
                                            <input type="checkbox" name="remember"> {{ __('adminlte::adminlte.remember_me') }}
                                        </label>
                                    </div>
                                    <a href="{{ $password_reset_url }}" class="auth-link text-primary">Esqueci minha senha</a>
                                </div>
                                <div class="my-3 d-grid gap-2">
                                    <button class="btn btn-block btn-primary btn-lg fw-medium auth-form-btn">Entrar</button>
                                </div>
                                <div class="text-center mt-4 fw-light">
                                    <span class="font-weight-semibold">Não é membro?</span>
                                    <a href="{{ route('register') }}" class="text-primary">Cadastre-se agora.</a>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="col-lg-6 login-half-bg d-flex flex-row">
                        <p class="text-white fw-medium text-center flex-grow align-self-end">Copyright © {{ date('Y') }} Todos os direitos reservados.</p>
                    </div>
                </div>
            </div>
            <!-- content-wrapper ends -->
        </div>
        <!-- page-body-wrapper ends -->
    </div>
@stop
