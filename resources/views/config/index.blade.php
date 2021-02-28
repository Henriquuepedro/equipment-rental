@extends('adminlte::page')

@section('title', 'Configuração')

@section('content_header')
    <h1 class="m-0 text-dark">Configuração</h1>
@stop

@section('css')
    <style>
        .permissions input[name^="user_"],
        .permissions input[name^="newuser_"],
        .permissions label[for^="user_"],
        .permissions label[for^="newuser_"]{
            cursor: pointer;
        }
        #dropdownConfigUser {
            height: 35px;
        }
        #dropdownConfigUser i {
            margin-left: 10px;
        }
        [aria-labelledby="dropdownConfigUser"] .btn{
            border-radius: 0;
        }
        #viewPermission .permissions .card .card-body ,
        #newUserModal .permissions .card .card-body {
            padding: 0.8rem 0.8rem;
        }
        .card-config-company {
            border: 1px solid #000;
            border-radius: 5px;
            padding: 10px 0;
            background: #666;
            color: #fff;
        }
        @media (max-width: 576px) {
            #users-registred .permissions .card .card-body {
                padding: 0.8rem 0.8rem;
            }
            #users-registred .card .card-body .user-avatar{
                width: 100%;
                text-align: center;
            }
            [aria-labelledby="dropdownConfigUser"]{
                top: -163px !important;
                left: 0px !important;;
                right: 40px !important;;
            }
        }
    </style>
@stop

@section('js')
    <script src="{{ asset('assets/js/shared/file-upload.js') }}" type="application/javascript"></script>
    <script src="{{ asset('assets/js/views/config/form.js') }}" type="application/javascript"></script>
@stop

@section('content')
    <div class="row profile-page">
        <div class="col-12">
            @if ($errors->any())
                <div class="alert-animate alert-warning">
                    <ol>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ol>
                </div>
            @endif
            @if(session('success'))
                <div class="alert-animate alert-success"><i class="fa fa-check-circle"></i> {{session('success')}}</div>
            @endif
            @if(session('warning'))
                <div class="alert-animate alert-danger mt-2">{{session('warning')}}</div>
            @endif
            <div class="card">
                <div class="card-body">
                    <div class="header-card-body">
                        <h4 class="card-title no-border">Configurações da Empresa</h4>
                    </div>
                    <div class="profile-body pt-3">
                        <ul class="nav tab-switch" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="company-tab" data-toggle="pill" href="#company" role="tab" aria-controls="company" aria-selected="true">Empresa</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="users-tab" data-toggle="pill" href="#users" role="tab" aria-controls="users" aria-selected="true">Usuários</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="config-tab" data-toggle="pill" href="#config" role="tab" aria-controls="config" aria-selected="true">Configuração</a>
                            </li>
                        </ul>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="tab-content tab-body" id="config-switch">
                                    <div class="tab-pane fade show active" id="company" role="tabpanel" aria-labelledby="company-tab">
                                        <form action="{{ route('config.update.company') }}" method="POST" enctype="multipart/form-data" id="formUpdateCompany" class="d-flex flex-wrap">
                                            <div class="col-md-9 no-padding">
                                                <div class="row">
                                                    <div class="form-group {{  $company->type_person == 'pf' ? 'col-md-12' : 'col-md-6'}}">
                                                        <label for="name">{{ $company->type_person == 'pf' ? 'Nome Completo' : 'Razão Social' }}</label>
                                                        <input type="text" class="form-control" name="name" id="name" value="{{ old('name') ?? $company->name }}" required>
                                                    </div>
                                                    @if ($company->type_person == 'pj')
                                                    <div class="form-group col-md-6">
                                                        <label for="fantasy">Telefone</label>
                                                        <input type="text" class="form-control" name="fantasy" id="fantasy" value="{{ old('fantasy') ?? $company->fantasy }}">
                                                    </div>
                                                    @endif
                                                </div>
                                                <div class="row">
                                                    <div class="form-group col-md-5">
                                                        <label>{{ $company->type_person == 'pf' ? 'CPF' : 'CNPJ' }}</label>
                                                        <input type="tel" class="form-control" id="cpf_cnpj" value="{{ old('cpf_cnpj') ?? $company->cpf_cnpj }}" disabled>
                                                    </div>
                                                    <div class="form-group col-md-7">
                                                        <label for="email">E-mail Comercial</label>
                                                        <input type="email" class="form-control" name="email" id="email" value="{{ old('email') ?? $company->email }}" required>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="form-group col-md-4">
                                                        <label for="phone_1">Telefone Primário</label>
                                                        <input type="tel" class="form-control" name="phone_1" id="phone_1" value="{{ old('phone_1') ?? $company->phone_1 }}" required>
                                                    </div>
                                                    <div class="form-group col-md-4">
                                                        <label for="phone_2">Telefone Secundário</label>
                                                        <input type="tel" class="form-control" name="phone_2" id="phone_2" value="{{ old('phone_2') ?? $company->phone_2 }}">
                                                    </div>
                                                    <div class="form-group col-md-4">
                                                        <label for="contact">Contato</label>
                                                        <input type="text" class="form-control" name="contact" id="contact" value="{{ old('contact') ?? $company->contact }}">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="row">
                                                    <div class="form-group col-md-12 no-padding mt-4">
                                                        <div class="logo-company text-center col-md-12">
                                                            <img src="{{ $company->logo }}" style="max-height:100px; max-width: 100%" id="src-profile-logo">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row mt-2">
                                                    <div class="form-group col-md-12 no-padding">
                                                        <input type="file" name="profile_logo" id="profile-logo" class="file-upload-default">
                                                        <div class="input-group col-md-12">
                                                            <input type="text" class="form-control file-upload-info" disabled placeholder="Alterar Logo"/>
                                                            <span class="input-group-append">
                                                                <button class="file-upload-browse btn btn-info" type="button">Alterar</button>
                                                            </span>
                                                        </div>
                                                        <small class="d-flex justify-content-center">Imagens em JPG, JPEG ou PNG até 2mb.</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12 no-padding">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <hr class="mb-0">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12 no-padding mt-3">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <h4>Endereço da Empresa</h4>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12 no-padding">
                                                <div class="row">
                                                    <div class="form-group col-md-3">
                                                        <label for="cep">CEP</label>
                                                        <input type="tel" class="form-control" name="cep" id="cep" value="{{ old('cep') ?? $company->cep }}">
                                                    </div>
                                                    <div class="form-group col-md-6">
                                                        <label for="address">Endereço</label>
                                                        <input type="text" class="form-control" name="address" id="address" value="{{ old('address') ?? $company->address }}">
                                                    </div>
                                                    <div class="form-group col-md-3">
                                                        <label for="number">Número</label>
                                                        <input type="text" class="form-control" name="number" id="number" value="{{ old('number') ?? $company->number }}">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12 no-padding">
                                                <div class="row">
                                                    <div class="form-group col-md-6">
                                                        <label for="complement">Complemento</label>
                                                        <input type="text" class="form-control" name="complement" id="complement" value="{{ old('complement') ?? $company->complement }}">
                                                    </div>
                                                    <div class="form-group col-md-6">
                                                        <label for="reference">Referência</label>
                                                        <input type="text" class="form-control" name="reference" id="reference" value="{{ old('reference') ?? $company->reference }}">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12 no-padding">
                                                <div class="row">
                                                    <div class="form-group col-md-4">
                                                        <label for="neigh">Bairro</label>
                                                        <input type="text" class="form-control" name="neigh" id="neigh" value="{{ old('neigh') ?? $company->neigh }}">
                                                    </div>
                                                    <div class="form-group col-md-4">
                                                        <label for="city">Cidade</label>
                                                        <input type="text" class="form-control" name="city" id="city" value="{{ old('city') ?? $company->city }}">
                                                    </div>
                                                    <div class="form-group col-md-4">
                                                        <label for="state">Estado</label>
                                                        <input type="text" class="form-control" name="state" id="state" value="{{ old('state') ?? $company->state }}">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12 no-padding">
                                                <div class="row">
                                                    <div class="form-group col-md-12 text-right pt-3 mt-3 border-top">
                                                        <button type="submit" class="btn btn-success col-md-3"><i class="fa fa-save"></i> Salvar</button>
                                                    </div>
                                                </div>
                                            </div>
                                            {{ csrf_field() }}
                                        </form>
                                    </div>
                                    <div class="tab-pane fade" id="users" role="tabpanel" aria-labelledby="users-tab">
                                        <div class="row">
                                            <div class="col-md-12 mb-4 mt-2 text-right">
                                                <button type="button" class="btn btn-success col-md-3" id="new-user"><i class="fa fa-user-plus"></i> Criar Usuário</button>
                                            </div>
                                            <div id="users-registred" class="col-md-12 no-padding"></div>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="config" role="tabpanel" aria-labelledby="config-tab">
                                        <form action="{{ route('config.update.config') }}" method="POST" enctype="multipart/form-data" id="formUpdateCompany">
                                            <div class="row">
                                                <div class="form-group col-md-12 text-center mb-2">
                                                    <h4>Defina as configurações para seu ambiente.</h4>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="form-group col-md-12 d-flex justify-content-center">
                                                    @foreach($configCompany as $config)
                                                    <div class="form-group col-md-3 card-config-company">
                                                        <div class="switch d-flex flex-wrap justify-content-center text-center">
                                                            <input type="checkbox" class="check-style check-md" name="{{ $config['name'] }}" id="{{ $config['name'] }}" {{ old() ? old($config['name']) ? 'checked': '' : ($config['status'] ? 'checked' : '') }}>
                                                            <label for="{{ $config['name'] }}" class="check-style check-md"></label>
                                                            {{ $config['description'] }}
                                                        </div>
                                                    </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="form-group col-md-12 d-flex justify-content-end mt-3">
                                                    <button class="btn btn-success"><i class="fa fa-save"></i> Salvar Configurações</button>
                                                </div>
                                            </div>
                                            {{ csrf_field() }}
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="newUserModal" tabindex="-1" role="dialog" aria-labelledby="newUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <form action="{{ route('ajax.user.new-user') }}" method="POST" id="formCreateUser">
                    <div class="modal-header">
                        <h5 class="modal-title" id="newUserModalLabel">Cadastro de novo usuário</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="form-group col-md-12">
                                <label>Nome</label>
                                <input type="text" class="form-control" name="name_modal">
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-7">
                                <label>E-mail</label>
                                <input type="email" class="form-control" name="email_modal">
                            </div>
                            <div class="form-group col-md-5">
                                <label>Telefone</label>
                                <input type="tel" class="form-control" name="phone_modal">
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label>Senha</label>
                                <input type="password" class="form-control" name="password_modal" id="password_modal">
                            </div>
                            <div class="form-group col-md-6">
                                <label>Confirme a Senha</label>
                                <input type="password" class="form-control" name="password_modal_confirmation">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <hr>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-5 text-center">
                                <h4 class="no-margin">Permissões de acesso</h4>
                                <small>Defina as permissão do usuário</small>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap justify-content-center">
                            {!! $htmlPermissions !!}
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-around">
                        <button type="button" class="btn btn-secondary col-md-3" data-dismiss="modal"><i class="fa fa-times"></i> Cancelar</button>
                        <button type="submit" class="btn btn-success col-md-3"><i class="fa fa-save"></i> Cadastrar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="viewPermission" tabindex="-1" role="dialog" aria-labelledby="newViewPermission" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <form action="{{ route('ajax.user.update-permission') }}" method="POST" id="formUpdatePermission">
                    <div class="modal-header">
                        <h5 class="modal-title" id="newViewPermission">Permissões do usuário</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="d-flex flex-wrap justify-content-center"></div>
                    </div>
                    <div class="modal-footer d-flex justify-content-around">
                        <button type="button" class="btn btn-secondary col-md-3" data-dismiss="modal"><i class="fa fa-times"></i> Cancelar</button>
                        <button type="submit" class="btn btn-success col-md-3"><i class="fa fa-sync-alt"></i> Atualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="updateUser" tabindex="-1" role="dialog" aria-labelledby="updateUser" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <form action="{{ route('ajax.user.update') }}" method="POST" id="formUpdateUser">
                    <div class="modal-header">
                        <h5 class="modal-title" id="updateUser">Atualizar usuário</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="form-group col-md-12">
                                <label>Nome</label>
                                <input type="text" name="update_user_name" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label>E-mail</label>
                                <input type="text" name="update_user_email" id="update_user_email" class="form-control">
                            </div>
                            <div class="form-group col-md-6">
                                <label>Telefone</label>
                                <input type="text" name="update_user_phone" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-around">
                        <button type="button" class="btn btn-secondary col-md-3" data-dismiss="modal"><i class="fa fa-times"></i> Cancelar</button>
                        <button type="submit" class="btn btn-success col-md-3"><i class="fa fa-sync-alt"></i> Atualizar</button>
                    </div>
                    <input type="hidden" name="update_user_id">
                </form>
            </div>
        </div>
    </div>
    <input type="hidden" id="routeGetUserPermission" value="{{ route('ajax.user.get-permission') }}">
    <input type="hidden" id="routeInactiveUser" value="{{ route('ajax.user.inactivate') }}">
    <input type="hidden" id="routeUserChangeType" value="{{ route('ajax.user.change-type') }}">
    <input type="hidden" id="routeDeleteUser" value="{{ route('ajax.user.delete') }}">
    <input type="hidden" id="routeGetUser" value="{{ route('ajax.user.get-user') }}">
    <input type="hidden" id="routeGetUsers" value="{{ route('ajax.user.get-users') }}">
@stop
