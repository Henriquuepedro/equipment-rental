@extends('adminlte::auth.auth-page', ['auth_type' => 'login'])

@php( $password_email_url = View::getSection('password_email_url') ?? config('adminlte.password_email_url', 'password/email') )

@if (config('adminlte.use_route_url', false))
    @php( $password_email_url = $password_email_url ? route($password_email_url) : '' )
@else
    @php( $password_email_url = $password_email_url ? url($password_email_url) : '' )
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
                    <div class="col-lg-12 d-flex align-items-center justify-content-center">
                        <div class="auth-form-transparent text-left p-5">
                            @if ($errors->any())
                                <div class="alert alert-warning">
                                    <ol>
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ol>
                                </div>
                            @endif
                            <form action="{{ $password_email_url }}" method="post">
                                @if(session('status'))
                                    <div class="alert alert-success">
                                        {{ session('status') }}
                                    </div>
                                @endif
                                {{ csrf_field() }}

                                <div class="form-group">
                                    <label class="label">E-mail</label>
                                    <input type="email" name="email" class="form-control form-control-lg border-left-0 {{ $errors->has('email') ? 'is-invalid' : '' }}" value="{{ old('email') }}">
                                    @if($errors->has('email'))
                                        <div class="invalid-feedback">
                                            <strong>{{ $errors->first('email') }}</strong>
                                        </div>
                                    @endif
                                </div>

                                {{-- Send reset link button --}}
                                <div class="my-3 d-grid gap-2">
                                    <button class="btn btn-block btn-primary mt-3"><span class="fas fa-share-square"></span> {{ __('adminlte::adminlte.send_password_reset_link') }}</button>
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
