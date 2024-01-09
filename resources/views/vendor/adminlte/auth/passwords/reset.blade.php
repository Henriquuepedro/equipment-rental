@extends('adminlte::auth.auth-page', ['auth_type' => 'login'])

@php( $password_reset_url = View::getSection('password_reset_url') ?? config('adminlte.password_reset_url', 'password/reset') )

@if (config('adminlte.use_route_url', false))
    @php( $password_reset_url = $password_reset_url ? route($password_reset_url) : '' )
@else
    @php( $password_reset_url = $password_reset_url ? url($password_reset_url) : '' )
@endif

@section('auth_header', __('adminlte::adminlte.password_reset_message'))

@section('auth_body')
    <div class="container-scroller">
        <div class="container-fluid page-body-wrapper full-page-wrapper">
            <div class="content-wrapper d-flex align-items-center auth auth-bg-1 theme-one">
                <div class="row w-100">
                    <div class="col-lg-4 mx-auto">
                        <div class="auto-form-wrapper">
                            <form action="{{ $password_reset_url }}" method="post">
                                {{ csrf_field() }}

                                {{-- Token field --}}
                                <input type="hidden" name="token" value="{{ $token }}">

                                {{-- Email field --}}
                                <div class="form-group">
                                    <label class="label">E-mail</label>
                                    <input type="email" name="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                                           value="{{ old('email') }}" placeholder="{{ __('adminlte::adminlte.email') }}">
                                    @if($errors->has('email'))
                                        <div class="invalid-feedback">
                                            <strong>{{ $errors->first('email') }}</strong>
                                        </div>
                                    @endif
                                </div>

                                {{-- Password field --}}
                                <div class="form-group">
                                    <label class="label">Senha</label>
                                    <input type="password" name="password"
                                           class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                                           placeholder="{{ __('adminlte::adminlte.password') }}">
                                    @if($errors->has('password'))
                                        <div class="invalid-feedback">
                                            <strong>{{ $errors->first('password') }}</strong>
                                        </div>
                                    @endif
                                </div>

                                {{-- Password confirmation field --}}
                                <div class="form-group">
                                    <label class="label">Confirme a Senha</label>
                                    <input type="password" name="password_confirmation"
                                           class="form-control {{ $errors->has('password_confirmation') ? 'is-invalid' : '' }}"
                                           placeholder="{{ trans('adminlte::adminlte.retype_password') }}">
                                    @if($errors->has('password_confirmation'))
                                        <div class="invalid-feedback">
                                            <strong>{{ $errors->first('password_confirmation') }}</strong>
                                        </div>
                                    @endif
                                </div>

                                {{-- Confirm password reset button --}}
                                <button type="submit" class="btn btn-block btn-primary mt-3">
                                    <span class="fas fa-sync-alt"></span>
                                    {{ __('adminlte::adminlte.reset_password') }}
                                </button>

                            </form>
                            <div class="text-block text-center my-3">
                                <a href="{{ route('login') }}" class="text-small">Voltar para o login</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
