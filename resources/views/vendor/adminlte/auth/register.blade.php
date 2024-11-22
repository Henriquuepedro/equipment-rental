@extends('adminlte::auth.auth-page', ['auth_type' => 'Registrar'])

@php( $login_url = View::getSection('login_url') ?? config('adminlte.login_url', 'login') )
@php( $register_url = View::getSection('register_url') ?? config('adminlte.register_url', 'register') )

@if (config('adminlte.use_route_url', false))
    @php( $login_url = $login_url ? route($login_url) : '' )
    @php( $register_url = $register_url ? route($register_url) : '' )
@else
    @php( $login_url = $login_url ? url($login_url) : '' )
    @php( $register_url = $register_url ? url($register_url) : '' )
@endif

@section('adminlte_css_pre')
    <link rel="stylesheet" href="{{ asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css') }}">
    <link href="{{ asset('vendor/icheck/skins/all.css') }}" rel="stylesheet">
    <style>
        .form-group label {
            background-color: #1f2127 !important;
        }
        .auth form .form-group {
            margin-bottom: 0 !important;
        }
    </style>
@stop
@section('adminlte_js')
    <script>
        $(function(){
            $('[name="type_person"]').iCheck({
                radioClass: 'iradio_square-blue',
                increaseArea: '20%'
            });
            $('[name="phone_1"],[name="phone_2"]').mask(MaskPhoneBehavior, maskPhoneOptions);
            if ($('[name="type_person"]:checked').length) {
                $('[name="type_person"]:checked').trigger('change');
            }
        });

        $('[name="type_person"]').on('change', function(){
            const type = $(this).val();
            const form = $(this).closest('form');

            if (type === 'pf') {
                form.find('label[for="name"]').html('Nome Completo <sup>*</sup>');
                form.find('label[for="cpf_cnpj"]').html('CPF <sup>*</sup>');
                form.find('label[for="rg_ie"]').text('RG');
                form.find('#fantasy').val('').closest('.form-group').addClass('d-none');
                form.find('[name="cpf_cnpj"]').mask('000.000.000-00');
                form.find('.personal_data').slideDown('slow');
            }
            else if (type === 'pj') {
                form.find('label[for="name"]').html('Razão Social <sup>*</sup>');
                form.find('label[for="cpf_cnpj"]').html('CNPJ <sup>*</sup>');
                form.find('label[for="rg_ie"]').text('IE');
                form.find('#fantasy').closest('.form-group').removeClass('d-none');
                form.find('[name="cpf_cnpj"]').mask('00.000.000/0000-00');
                form.find('.personal_data').slideUp('slow');
            }

            form.find(".card").each(function() {
                $(this).slideDown('slow');
            });

            setTimeout(() => {
                $('[name="state"], [name="city"]').select2()
            }, 500)
        });

        $('#password').on('keyup', function(){
            let border_color_pwd = 'unset';
            let border_color_cfm_pwd = 'unset';
            const confirm_password_input = $('#password_confirmation');
            if ($(this).val().length < 8) {
                border_color_pwd = '#ff6258';
            }

            if (confirm_password_input.val().length && $(this).val() !== confirm_password_input.val()) {
                border_color_cfm_pwd = '#ff6258';
            }

            $(this).css({borderColor: border_color_pwd});
            confirm_password_input.css({borderColor: border_color_cfm_pwd});
        });

        $('#password_confirmation').on('keyup', function(){
            let border_color = 'unset';
            if ($(this).val() !== $('#password').val()) {
                border_color = '#ff6258';
            }

            $(this).css({borderColor: border_color})
        });
    </script>
@stop

@section('auth_body')
    <div class="container-scroller">
        <div class="container-fluid page-body-wrapper full-page-wrapper">
            <div class="content-wrapper d-flex align-items-stretch auth auth-img-bg">
                <div class="row flex-grow">
                    <div class="col-lg-6 d-flex align-items-center justify-content-center">
                        <div class="auth-form-transparent text-left p-3">
                            @if ($errors->any())
                                <div class="alert alert-animate alert-warning">
                                    <ol>
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ol>
                                </div>
                            @endif
                            <div class="brand-logo d-flex justify-content-center">
                                <img src="{{ asset('assets/images/system/logotipo-horizontal-white.png') }}" alt="logo">
                            </div>
                            <form action="{{ $register_url }}" method="post">
                                {{ csrf_field() }}
                                <div class="d-flex justify-content-around">
                                    <div class="form-radio form-radio-flat mb-0">
                                        <label>
                                            <input type="radio" name="type_person" value="pf" @if(old('type_person') === 'pf') checked @endif @if(old('type_person') !== 'pf' && old('type_person') !== 'pf') checked @endif> Pessoa Física
                                        </label>
                                    </div>
                                    <div class="form-radio form-radio-flat mb-0">
                                        <label>
                                            <input type="radio" name="type_person" value="pj" @if(old('type_person') === 'pj') checked @endif> Pessoa Jurídica
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <label for="name">Nome Completo <sup>*</sup></label>
                                        <input type="text" class="form-control" id="name" name="name" autocomplete="nope" value="{{ old('name') }}" required>
                                    </div>
                                    <div class="form-group col-md-12 d-none">
                                        <label for="fantasy">Fantasia</label>
                                        <input type="text" class="form-control" id="fantasy" name="fantasy" autocomplete="nope" value="{{ old('fantasy') }}">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label for="cpf_cnpj">CPF <sup>*</sup></label>
                                        <input type="tel" class="form-control" id="cpf_cnpj" name="cpf_cnpj" autocomplete="nope" value="{{ old('cpf_cnpj') }}" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="contact">Nome de Contato <sup>*</sup></label>
                                        <input type="text" class="form-control" id="contact" name="contact" autocomplete="nope" value="{{ old('contact') }}" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label for="phone_1">Telefone Principal <sup>*</sup></label>
                                        <input type="tel" class="form-control" id="phone_1" name="phone_1" autocomplete="nope" value="{{ old('phone_1') }}" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="phone_2">Telefone Secundário</label>
                                        <input type="tel" class="form-control" id="phone_2" name="phone_2" autocomplete="nope" value="{{ old('phone_2') }}">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <label for="email">Endereço de E-mail <sup>*</sup></label>
                                        <input type="email" class="form-control" id="email" name="email" autocomplete="nope" value="{{ old('email') }}" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label for="password">Senha <sup>*</sup></label>
                                        <div class="input-group">
                                            <input type="password" class="form-control" id="password" name="password" autocomplete="nope" value="{{ old('password') }}" required>
                                            <div class="input-group-addon input-group-append">
                                                <button type="button" class="btn btn-secondary show-hide-password"><i class="fa fa-eye"></i></button>
                                            </div>
                                        </div>
                                        <small>A senha deve conter no mínimo 8 dígitos.</small>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="password_confirmation">Confirme a Senha <sup>*</sup></label>
                                        <div class="input-group">
                                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" autocomplete="nope" value="{{ old('password_confirmation') }}" required>
                                            <div class="input-group-addon input-group-append">
                                                <button type="button" class="btn btn-secondary show-hide-password"><i class="fa fa-eye"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-3 d-grid gap-2">
                                    <button type="submit" class="btn btn-block btn-primary btn-lg fw-medium auth-form-btn">
                                        <i class="fas fa-user-plus"></i> Registrar
                                    </button>
                                </div>

                                <div class="ttext-center mt-4 fw-light">
                                    <span class="font-weight-semibold">Já é membro?</span>
                                    <a href="{{ route('login') }}" class="text-primary">Entre.</a>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="col-lg-6 register-half-bg d-flex flex-row">
                        <p class="text-white fw-medium text-center flex-grow align-self-end">Copyright &copy; 2021 All rights
                            reserved.</p>
                    </div>
                </div>
            </div>
            <!-- content-wrapper ends -->
        </div>
        <!-- page-body-wrapper ends -->
    </div>
@stop

@section('auth_footer')
    <p class="my-0">
        <a href="{{ $login_url }}">
            {{ __('adminlte::adminlte.i_already_have_a_membership') }}
        </a>
    </p>
@stop
