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
            <div class="content-wrapper d-flex align-items-center auth auth-bg-1 theme-one">
                <div class="row w-100">
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
                    <div class="col-lg-4 mx-auto">
                        <div class="auto-form-wrapper">
                            <form action="{{ route('login') }}" method="post">
                                {{ csrf_field() }}
                                <div class="form-group">
                                    <label class="label">E-mail</label>
                                    <input type="text" class="form-control" name="email" value="{{ old('email') }}" placeholder="{{ __('adminlte::adminlte.email') }}" required>
                                </div>
                                <div class="form-group">
                                    <label class="label">Senha</label>
                                    <input type="password" class="form-control" name="password" placeholder="{{ __('adminlte::adminlte.password') }}" required>
                                </div>
                                <div class="form-group mt-2">
                                    <button class="btn btn-primary submit-btn btn-block">Entrar</button>
                                </div>
                                <div class="form-group d-flex justify-content-between mt-2">
                                    <div class="icheck">
                                        <label class="ml-0">
                                            <input type="checkbox" name="remember"> {{ __('adminlte::adminlte.remember_me') }}
                                        </label>
                                    </div>
                                    <a href="{{ $password_reset_url }}" class="text-small forgot-password">Esqueci minha senha</a>
                                </div>
                                <div class="text-block text-center my-3">
                                    <span class="text-small font-weight-semibold">Não é membro?</span>
                                    <a href="{{ route('register') }}" class="text-small">Cadastre-se agora.</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- content-wrapper ends -->
        </div>
        <!-- page-body-wrapper ends -->
    </div>
@stop
