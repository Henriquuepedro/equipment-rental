@extends('adminlte::page')

@section('title', 'Cadastro de Cliente')

@section('content_header')
    <h1 class="m-0 text-dark">Cadastro de Cliente</h1>
@stop

@section('css')

@stop

@section('js')

@stop

@section('content')

    <div class="row">
        <div class="col-md-12 d-flex align-items-stretch grid-margin">
            <div class="row flex-grow">
                <div class="col-12">
                    @if(session('success'))
                        <div class="alert alert-pri mt-2">{{session('success')}}</div>
                    @endif
                    @if(session('warning'))
                        <div class="alert alert-danger mt-2">{{session('warning')}}</div>
                    @endif
                    <div class="card">
                        <div class="card-body">
                            <div class="header-card-body">
                                <h4 class="card-title">Cadastro de Cliente</h4>
                                <p class="card-description"> Preencha o formulário a baixo para cadastrar um novo cliente </p>
                            </div>
                            <form class="forms-sample">
                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <label for="name_client">Nome do Cliente</label>
                                        <input type="text" class="form-control" id="name_client" name="name_client" placeholder="Digite o nome do cliente">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label for="phone_1">Telefone Primário</label>
                                        <input type="text" class="form-control" id="phone_1" name="phone_1" placeholder="Digite o telefone primário">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="phone_2">Telefone Secundário</label>
                                        <input type="text" class="form-control" id="phone_2" name="phone_2" placeholder="Digite o telefone secundário">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="email">Endereço de E-mail</label>
                                        <input type="email" class="form-control" id="email" name="email" placeholder="Digite o endereço de e-mail">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <hr>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label for="cpf_cnpj">CPF</label>
                                        <input type="text" class="form-control" id="cpf_cnpj" name="cpf_cnpj" placeholder="Digite o número de CPF">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="rg_ie">RG</label>
                                        <input type="text" class="form-control" id="rg_ie" name="rg_ie" placeholder="Digite o número de RG">
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body d-flex justify-content-between">
                            <button class="btn btn-secondary col-md-3">Cancelar</button>
                            <button type="submit" class="btn btn-success col-md-3">Cadastrar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop
