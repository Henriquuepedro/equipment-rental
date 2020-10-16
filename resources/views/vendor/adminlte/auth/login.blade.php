@extends('adminlte::auth.auth-page', ['auth_type' => 'login'])

@section('adminlte_css_pre')
    <link rel="stylesheet" href="{{ asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css') }}">
@stop
@section('adminlte_js')
    <script src="{{ asset('assets/snippets/pages/user/login.js') }}" type="text/javascript"></script>
@stop



@section('auth_body')

    <div class="container-scroller">
        <div class="container-fluid page-body-wrapper full-page-wrapper">
            <div class="content-wrapper d-flex align-items-center auth auth-bg-1 theme-one">
                <div class="row w-100">
                    <div class="col-lg-4 mx-auto">
                        <div class="auto-form-wrapper">
                            <form action="{{ route('login') }}" method="post">
                                {{ csrf_field() }}
                                <div class="form-group">
                                    <label class="label">E-mail</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="email" value="{{ old('email') }}" placeholder="{{ __('adminlte::adminlte.email') }}" required>
                                        <div class="input-group-append">
                                            <span class="input-group-text">
                                              <i class="mdi mdi-check-circle-outline"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="label">Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" name="password" placeholder="{{ __('adminlte::adminlte.password') }}" required>
                                        <div class="input-group-append">
                                            <span class="input-group-text">
                                              <i class="mdi mdi-check-circle-outline"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <button class="btn btn-primary submit-btn btn-block">Login</button>
                                </div>
                                <div class="form-group d-flex justify-content-between">
                                    <div class="form-check form-check-flat mt-0">
                                        <label class="form-check-label">
                                        <input type="checkbox" class="form-check-input" name="remember"> {{ __('adminlte::adminlte.remember_me') }} </label>
                                    </div>
                                    <a href="#" class="text-small forgot-password text-black">Forgot Password</a>
                                </div>
                                <div class="form-group">
                                    <button class="btn btn-block g-login">
                                        <img class="mr-3" src="../../../assets/images/file-icons/icon-google.svg" alt="">Log in with Google</button>
                                </div>
                                <div class="text-block text-center my-3">
                                    <span class="text-small font-weight-semibold">Not a member ?</span>
                                    <a href="register.html" class="text-black text-small">Create new account</a>
                                </div>
                            </form>
                        </div>
                        <ul class="auth-footer">
                            <li>
                                <a href="#">Conditions</a>
                            </li>
                            <li>
                                <a href="#">Help</a>
                            </li>
                            <li>
                                <a href="#">Terms</a>
                            </li>
                        </ul>
                        <p class="footer-text text-center">copyright © 2018 Bootstrapdash. All rights reserved.</p>
                    </div>
                </div>
            </div>
            <!-- content-wrapper ends -->
        </div>
        <!-- page-body-wrapper ends -->
    </div>
@stop
