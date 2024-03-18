@extends('adminlte::auth.auth-page', ['auth_type' => 'login'])

@php( $password_reset_url = View::getSection('password_reset_url') ?? config('adminlte.password_reset_url', 'password/reset') )

@if (config('adminlte.use_route_url', false))
    @php( $password_reset_url = $password_reset_url ? route($password_reset_url) : '' )
@else
    @php( $password_reset_url = $password_reset_url ? url($password_reset_url) : '' )
@endif

@section('adminlte_css_pre')
    <style>
        .form-group label {
            background-color: #1f2127 !important;
        }
    </style>
@stop

@section('auth_header', __('adminlte::adminlte.password_reset_message'))

@section('auth_body')
    <div class="container-scroller">
        <div class="container-fluid page-body-wrapper full-page-wrapper">
            <div class="content-wrapper d-flex align-items-stretch auth auth-img-bg">
                <div class="row flex-grow">
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
                    <div class="col-lg-12 d-flex align-items-center justify-content-center">
                        <div class="auth-form-transparent text-left p-5">
                            <div class="brand-logo d-flex justify-content-center">
                                <img src="{{ asset('assets/images/system/logotipo.png') }}" alt="logo">
                            </div>
                            <form action="{{ $password_reset_url }}" method="post">
                                {{ csrf_field() }}

                                {{-- Token field --}}
                                <input type="hidden" name="token" value="{{ $token }}">

                                {{-- Email field --}}
                                <div class="form-group">
                                    <label class="label">E-mail</label>
                                    <input type="email" name="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" value="{{ old('email') }}">
                                    @if($errors->has('email'))
                                        <div class="invalid-feedback">
                                            <strong>{{ $errors->first('email') }}</strong>
                                        </div>
                                    @endif
                                </div>

                                {{-- Password field --}}
                                <div class="form-group">
                                    <label class="label">Senha</label>
                                    <input type="password" name="password" class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}">
                                    @if($errors->has('password'))
                                        <div class="invalid-feedback">
                                            <strong>{{ $errors->first('password') }}</strong>
                                        </div>
                                    @endif
                                </div>

                                {{-- Password confirmation field --}}
                                <div class="form-group">
                                    <label class="label">Confirme a Senha</label>
                                    <input type="password" name="password_confirmation" class="form-control {{ $errors->has('password_confirmation') ? 'is-invalid' : '' }}">
                                    @if($errors->has('password_confirmation'))
                                        <div class="invalid-feedback">
                                            <strong>{{ $errors->first('password_confirmation') }}</strong>
                                        </div>
                                    @endif
                                </div>

                                {{-- Confirm password reset button --}}
                                <div class="my-3 d-grid gap-2">
                                    <button class="btn btn-block btn-primary mt-3"><span class="fas fa-sync-alt"></span> {{ __('adminlte::adminlte.reset_password') }}</button>
                                </div>

                            </form>
                            <div class="text-block text-center my-3">
                                <a href="{{ route('login') }}" class="text-primary">Voltar para o login</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
