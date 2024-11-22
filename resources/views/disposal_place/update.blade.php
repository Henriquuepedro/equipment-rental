@extends('adminlte::page')

@section('title', empty($disposal_place) ? 'Cadastrar local de descarte' : 'Alterar local de descarte')

@section('content_header')
    <h1 class="m-0 text-dark">{{ empty($disposal_place) ? 'Cadastre' : 'Alterar' }} local de descarte</h1>
@stop

@section('css')
@stop

@section('js')
<script src="{{ asset('assets/js/views/disposal_place/form.js') }}" type="application/javascript"></script>
<script>
    // Validar dados
    $("#formUpdateDisposalPlace").validate({
        rules: {
            name: {
                required: true
            },
            phone_1: {
                rangelength: [13, 14]
            },
            phone_2: {
                rangelength: [13, 14]
            },
            cpf_cnpj: {
                cpf_cnpj: true
            },
            email: {
                required: true
            }
        },
        messages: {
            name: {
                required: 'Informe um nome/razão social para o local de descarte'
            },
            phone_1: {
                rangelength: "O número de telefone principal está inválido, informe um válido. (99) 999..."
            },
            phone_2: {
                rangelength: "O número de telefone secundário está inválido, informe um válido. (99) 999..."
            },
            email: {
                required: 'Informe um endereço de e-mail para o local de descarte'
            }
        },
        invalidHandler: function(event, validator) {
            $('html, body').animate({scrollTop:0}, 400);
            let arrErrors = [];
            $.each(validator.errorMap, function (key, val) {
                arrErrors.push(val);
            });
            setTimeout(() => {
                Swal.fire({
                    icon: 'warning',
                    title: 'Atenção',
                    html: '<ol><li>'+arrErrors.join('</li><li>')+'</li></ol>'
                });
            }, 500);
        },
        submitHandler: function(form) {
            form.submit();
        }
    });
</script>
@stop

@php
    $disabled = in_array('DisposalPlaceUpdatePost', $permissions) || empty($disposal_place) ? '' : 'disabled';
    $btns = in_array('DisposalPlaceUpdatePost', $permissions) || empty($disposal_place);
@endphp

@section('content')
    <div class="row">
        <div class="col-md-12 grid-margin">
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
                    <form action="{{ route((empty($disposal_place) ? 'disposal_place.insert' : 'disposal_place.update')) }}" method="POST" enctype="multipart/form-data" id="formUpdateDisposalPlace">
                        <div class="card">
                            <div class="card-body d-flex justify-content-around">
                                <div class="form-radio form-radio-flat">
                                    <label class="form-check-label">
                                        <input {{ $disabled }} type="radio" class="form-check-input" name="type_person" value="pf" {{ old('type_person', $disposal_place->type_person ?? '') === 'pf' ? 'checked' : '' }}> Pessoa Física <i class="input-helper"></i>
                                    </label>
                                </div>
                                <div class="form-radio form-radio-flat">
                                    <label class="form-check-label">
                                        <input {{ $disabled }} type="radio" class="form-check-input" name="type_person" value="pj" {{ old('type_person', $disposal_place->type_person ?? '') === 'pj' ? 'checked' : '' }}> Pessoa Jurídica <i class="input-helper"></i>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="card mt-2 display-none">
                            <div class="card-body">
                                <div class="header-card-body">
                                    <h4 class="card-title">Dados do local de descarte</h4>
                                    <p class="card-description"> {{ empty($disposal_place) ? 'Cadastre' : ($btns ? 'Altere' : 'Visualize') }} o formulário abaixo com as informações do local de descarte </p>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-10">
                                        <label for="name">Nome do local de descarte <sup>*</sup></label>
                                        <input {{ $disabled }} type="text" class="form-control" id="name" name="name" autocomplete="nope" value="{{ old('name', $disposal_place->name ?? '') }}" required>
                                    </div>
                                    <div class="form-group col-md-5 d-none">
                                        <label for="fantasy">Fantasia</label>
                                        <input {{ $disabled }} type="text" class="form-control" id="fantasy" name="fantasy" autocomplete="nope" value="{{ old('fantasy', $disposal_place->fantasy ?? '') }}">
                                    </div>
                                    <div class="form-group col-md-2">
                                        <div class="switch d-flex mt-4">
                                            <input {{ $disabled }} type="checkbox" class="check-style check-xs" name="active" id="active" {{ old('active', $disposal_place->active ?? 1) ? 'checked' : '' }}>
                                            <label for="active" class="check-style check-xs"></label>&nbsp;Ativo
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label for="cpf_cnpj">CPF <sup>*</sup></label>
                                        <input {{ $disabled }} type="tel" class="form-control" id="cpf_cnpj" name="cpf_cnpj" autocomplete="nope" value="{{ old('cpf_cnpj', $disposal_place->cpf_cnpj ?? '') }}">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="rg_ie">RG</label>
                                        <input {{ $disabled }} type="tel" class="form-control" id="rg_ie" name="rg_ie" autocomplete="nope" value="{{ old('rg_ie', $disposal_place->rg_ie ?? '') }}">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="contact">Contato</label>
                                        <input {{ $disabled }} type="text" class="form-control" id="contact" name="contact" autocomplete="nope" value="{{ old('contact', $disposal_place->contact ?? '') }}">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label for="phone_1">Telefone Principal <sup>*</sup></label>
                                        <input {{ $disabled }} type="tel" class="form-control" id="phone_1" name="phone_1" autocomplete="nope" value="{{ old('phone_1', $disposal_place->phone_1 ?? '') }}">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="phone_2">Telefone Secundário</label>
                                        <input {{ $disabled }} type="tel" class="form-control" id="phone_2" name="phone_2" autocomplete="nope" value="{{ old('phone_2', $disposal_place->phone_2 ?? '') }}">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="email">Endereço de E-mail <sup>*</sup></label>
                                        <input {{ $disabled }} type="email" class="form-control" id="email" name="email" autocomplete="nope" value="{{ old('email', $disposal_place->email ?? '') }}">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <label for="observation">Observação</label>
                                        <textarea {{ $disabled }} class="form-control" id="observation" name="observation" rows="3">{{ old('observation', $disposal_place->observation ?? '') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card mt-2 display-none">
                            <div class="card-body">
                                <div class="header-card-body">
                                    <h4 class="card-title">Dados do Endereço</h4>
                                    <p class="card-description"> {{ empty($disposal_place) ? 'Cadastre' : ($btns ? 'Altere' : 'Visualize') }} o formulário abaixo com as informações de endereço </p>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-3">
                                        <label for="address_zipcode">CEP</label>
                                        <input {{ $disabled }} type="text" class="form-control" name="address_zipcode" id="address_zipcode" autocomplete="nope" value="{{ old('address_zipcode', $disposal_place->address_zipcode ?? '') }}">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="address_name">Endereço</label>
                                        <input {{ $disabled }} type="text" class="form-control" name="address_name" id="address_name" autocomplete="nope" value="{{ old('address_name', $disposal_place->address_name ?? '') }}">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="address_number">Número</label>
                                        <input {{ $disabled }} type="text" class="form-control" name="address_number" id="address_number" autocomplete="nope" value="{{ old('address_number', $disposal_place->address_number ?? '') }}">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label for="address_complement">Complemento</label>
                                        <input {{ $disabled }} type="text" class="form-control" name="address_complement" id="address_complement" autocomplete="nope" value="{{ old('address_complement', $disposal_place->address_complement ?? '') }}">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="address_reference">Referência</label>
                                        <input {{ $disabled }} type="text" class="form-control" name="address_reference" id="address_reference" autocomplete="nope" value="{{ old('address_reference', $disposal_place->address_reference ?? '') }}">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label for="address_neigh">Bairro</label>
                                        <input {{ $disabled }} type="text" class="form-control" name="address_neigh" id="address_neigh" autocomplete="nope" value="{{ old('address_neigh', $disposal_place->address_neigh ?? '') }}">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="address_state">Estado</label>
                                        <select {{ $disabled }} class="form-control" name="address_state" id="address_state" data-value-state="{{ old('address_state', $disposal_place->address_state ?? '') }}"></select>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="address_city">Cidade</label>
                                        <select {{ $disabled }} class="form-control" name="address_city" id="address_city" data-value-city="{{ old('address_city', $disposal_place->address_city ?? '') }}"></select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card mt-2 display-none">
                            <div class="card-body d-flex justify-content-between">
                                <a href="{{ route('disposal_place.index') }}" class="btn btn-secondary col-md-3"><i class="fa fa-arrow-left"></i> Cancelar</a>
                                @if($btns)<button type="submit" class="btn btn-success col-md-3"><i class="fa fa-{{ empty($disposal_place) ? 'save' : 'sync' }}"></i> {{ empty($disposal_place) ? 'Cadastrar' : 'Atualizar' }}</button>@endif
                            </div>
                        </div>
                        @if($btns)
                        <input type="hidden" name="disposal_place_id" value="{{ $disposal_place->id ?? '' }}">
                        {{ csrf_field() }}
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
