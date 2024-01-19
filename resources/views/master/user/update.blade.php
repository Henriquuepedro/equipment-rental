@extends('adminlte::page')

@section('title', 'Alterar Usuário')

@section('content_header')
    <h1 class="m-0 text-dark">Alterar Usuário</h1>
@stop

@section('css')
@stop

@section('js')
<script>
    $(function(){
        $('[name="phone"]').mask('(00) 000000000');
    });

    $(document).on('click', '#formUpdateUser input[type="checkbox"]', function(){
        const permission_id = parseInt($(this).data('permission-id'));
        const auto_check    = $(this).data('auto-check');
        const parentEl      = '#formUpdateUser';
        let input_auto_check;

        $(`${parentEl} input[type="checkbox"]:checked`).each(function(){
            input_auto_check = $(this).data('auto-check');
            if (input_auto_check.includes(permission_id)) {
                $(`${parentEl} input[type="checkbox"][data-permission-id="${permission_id}"]`).prop('checked', true);
                return false;
            }
        });

        if (auto_check.length) {
            auto_check.forEach(id => {
                $(`${parentEl} input[type="checkbox"][data-permission-id="${id}"]`).prop('checked', true);
            })
        }
    });

    // Validar dados
    $("#formUpdateUser").validate({
        rules: {
            name: {
                required: true
            },
            phone: {
                rangelength: [13, 14]
            },
            email: {
                required: true,
                email: true
            },
            password: {
                minlength: 6
            },
            password_confirmation: {
                equalTo : "#password"
            }
        },
        messages: {
            name: {
                required: 'Digite o nome/razão social da empresa.'
            },
            phone: {
                rangelength: "O campo telefone primário deve ser um telefone válido."
            },
            email: {
                required: "Informe um e-mail comercial válido.",
                email: "Informe um e-mail comercial válido."
            },
            password: {
                minlength: "Senha deve conter no mínimo 6 caracteres."
            },
            password_confirmation: {
                equalTo : "Senhas devem ser iguais."
            }
        },
        invalidHandler: function(event, validator) {
            let arrErrors = [];
            $.each(validator.errorMap, function (key, val) {
                arrErrors.push(val);
            });
            Swal.fire({
                icon: 'warning',
                title: 'Atenção',
                html: '<ol><li>'+arrErrors.join('</li><li>')+'</li></ol>'
            });
        },
        submitHandler: function(form) {
            form.submit();
        }
    });

    $('.select-all-permission').on('change', function(){
        const checked = $(this).is(':checked');

        $(this).closest('.card-body').find('input[name="permission[]"][type="checkbox"]').each(function (){
            $(this).prop('checked', checked);
        });
    });
</script>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12 d-flex align-items-stretch grid-margin">
            <div class="row flex-grow">
                <div class="col-12">
                    @if ($errors->any())
                    <div class="alert alert-animate alert-warning">
                        <ol>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ol>
                    </div>
                    @endif
                    <form action="{{ route(empty($user) ? 'master.user.insert' : 'master.user.update', empty($user) ? [] : array('id' => $user->id)) }}" method="POST" enctype="multipart/form-data" id="formUpdateUser">
                        <div class="card">
                            <div class="card-body d-flex flex-wrap">
                                <div class="header-card-body">
                                    <h4 class="card-title">Dados do Usuário</h4>
                                    <p class="card-description"> Preencha o formulário abaixo com as novas informações do usuário </p>
                                </div>
                                <div class="col-md-12 no-padding">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="alert alert-fill-primary" role="alert">
                                                <i class="mdi mdi-alert-circle"></i> Após a criação do usuário, o mesmo deverá confirmar seu e-mail para acessar a plataforma.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="form-group col-md-4">
                                            <label>Nome</label>
                                            <input type="text" class="form-control" name="name" value="{{ old('name', $user->name ?? '') }}">
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>E-mail</label>
                                            <input type="email" class="form-control" name="email" value="{{ old('email', $user->email ?? '') }}">
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>Telefone</label>
                                            <input type="tel" class="form-control" name="phone" value="{{ old('phone', $user->phone ?? '') }}">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="form-group col-md-5">
                                            <label>Empresa</label>
                                            <select class="form-control select2" name="company" {{ empty($user) ? '' : 'disabled' }}>
                                                @foreach($companies as $company)
                                                    <option value="{{ $company->id }}" {{ old('company', $user->company_id ?? '') == $company->id ? 'selected' : '' }}>{{ $company->fantasy ?? $company->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group col-md-5">
                                            <label>Tipo de Usuário</label>
                                            <select class="form-control select2" name="type_user">
                                                <option value="0" {{ old('company', $user->type_user ?? '') == 0 ? 'selected' : '' }}>Usuário(User)</option>
                                                <option value="1" {{ old('company', $user->type_user ?? '') == 1 ? 'selected' : '' }}>Administrador da Empresa(Admin)</option>
                                                <option value="2" {{ old('company', $user->type_user ?? '') == 2 ? 'selected' : '' }}>Administrador do Sistema(Admin-Master)</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-2">
                                            <div class="switch d-flex mt-4">
                                                <input type="checkbox" class="check-style check-xs" name="active" id="active" {{ old('active', $user->active ?? 'on') ? 'checked' : '' }}>
                                                <label for="active" class="check-style check-xs"></label>&nbsp;Ativo
                                            </div>
                                        </div>
                                    </div>
                                    @if (empty($user))
                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            <label>Senha</label>
                                            <input type="password" class="form-control" name="password" id="password">
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>Confirme a Senha</label>
                                            <input type="password" class="form-control" name="password_confirmation" id="password_confirmation">
                                        </div>
                                    </div>
                                    @endif
                                    <div class="row">
                                        <div class="col-md-12">
                                            <hr>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12 mb-3 text-center">
                                            <h4 class="no-margin">Permissões de acesso</h4>
                                            <small>Defina as permissão do usuário</small>
                                            <br>
                                            <div class="d-flex justify-content-center mt-4">
                                                <div class="switch">
                                                    <input type="checkbox" class="switch-input select-all-permission" id="permission_select_all_permission">
                                                    <label for="permission_select_all_permission" class="switch-label"></label>
                                                </div>
                                                Selecionar Tudo
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row d-flex flex-wrap justify-content-center">
                                        {!! $htmlPermissions !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body d-flex justify-content-between">
                                <a href="{{ route('master.user.index') }}" class="btn btn-secondary col-md-3"><i class="fa fa-arrow-left"></i> Cancelar</a>
                                <button type="submit" class="btn btn-success col-md-3"><i class="fa fa-sync"></i> Atualizar</button>
                            </div>
                        </div>
                        {{ csrf_field() }}
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
