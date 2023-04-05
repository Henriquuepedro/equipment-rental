@extends('adminlte::page')

@section('title', 'Alterar de Fornecedor')

@section('content_header')
    <h1 class="m-0 text-dark">Alterar de Fornecedor</h1>
@stop

@section('css')
@stop

@section('js')
<script src="{{ asset('assets/js/views/provider/form.js') }}" type="application/javascript"></script>
<script>
    // Validar dados
    $("#formUpdateProvider").validate({
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
            }
        },
        messages: {
            name: {
                required: 'Informe um nome/razão social para o fornecedor'
            },
            phone_1: {
                rangelength: "O número de telefone principal está inválido, informe um válido. (99) 999..."
            },
            phone_2: {
                rangelength: "O número de telefone secundário está inválido, informe um válido. (99) 999..."
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
            $('#formUpdateProvider [type="submit"]').attr('disabled', true);
            let verifyAddress = verifyAddressComplet();
            if (!verifyAddress[0]) {
                Toast.fire({
                    icon: 'warning',
                    title: `Finalize o cadastro do ${verifyAddress[1]}º endereço, para alterar o cadastro.`
                });
                $('#formUpdateProvider [type="submit"]').attr('disabled', false);
                return false;
            }

            form.submit();
        }
    });
</script>
@stop

@php
    $disabled = in_array('ProviderUpdatePost', $permissions) ? '' : 'disabled';
    $btns = in_array('ProviderUpdatePost', $permissions);
@endphp

@section('content')
    <div class="row">
        <div class="col-md-12 d-flex align-items-stretch grid-margin">
            <div class="row flex-grow">
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
                    <form action="{{ route(('provider.update')) }}" method="POST" enctype="multipart/form-data" id="formUpdateProvider">
                        <div class="card">
                            <div class="card-body d-flex justify-content-around">
                                <div class="form-radio form-radio-flat">
                                    <label class="form-check-label">
                                        <input {{ $disabled }} type="radio" class="form-check-input" name="type_person" value="pf" {{ old() ? (old('type_person') === 'pf' ? 'checked' : '') : ($provider->type === 'pf' ? 'checked' : '') }}> Pessoa Física <i class="input-helper"></i>
                                    </label>
                                </div>
                                <div class="form-radio form-radio-flat">
                                    <label class="form-check-label">
                                        <input {{ $disabled }} type="radio" class="form-check-input" name="type_person" value="pj" {{ old() ? (old('type_person') === 'pj' ? 'checked' : '') : ($provider->type === 'pj' ? 'checked' : '') }}> Pessoa Jurídica <i class="input-helper"></i>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="card display-none">
                            <div class="card-body">
                                <div class="header-card-body">
                                    <h4 class="card-title">Dados do Fornecedor</h4>
                                    <p class="card-description"> {{ $btns ? 'Altere' : 'Visualize'}} o formulário abaixo com as informações do fornecedor </p>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <label for="name">Nome do Fornecedor <sup>*</sup></label>
                                        <input {{ $disabled }} type="text" class="form-control" id="name" name="name" autocomplete="nope" value="{{ old('name') ?? $provider->name }}" required>
                                    </div>
                                    <div class="form-group col-md-6 d-none">
                                        <label for="fantasy">Fantasia</label>
                                        <input  {{ $disabled }}type="text" class="form-control" id="fantasy" name="fantasy" autocomplete="nope" value="{{ old('fantasy') ?? $provider->fantasy }}">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label for="cpf_cnpj">CPF</label>
                                        <input {{ $disabled }} type="tel" class="form-control" id="cpf_cnpj" name="cpf_cnpj" autocomplete="nope" value="{{ old('cpf_cnpj') ?? $provider->cpf_cnpj }}">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="rg_ie">RG</label>
                                        <input {{ $disabled }} type="tel" class="form-control" id="rg_ie" name="rg_ie" autocomplete="nope" value="{{ old('rg_ie') ?? $provider->rg_ie }}">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="contact">Contato</label>
                                        <input {{ $disabled }} type="text" class="form-control" id="contact" name="contact" autocomplete="nope" value="{{ old('contact') ?? $provider->contact }}">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label for="phone_1">Telefone Principal</label>
                                        <input {{ $disabled }} type="tel" class="form-control" id="phone_1" name="phone_1" autocomplete="nope" value="{{ old('phone_1') ?? $provider->phone_1 }}">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="phone_2">Telefone Secundário</label>
                                        <input {{ $disabled }} type="tel" class="form-control" id="phone_2" name="phone_2" autocomplete="nope" value="{{ old('phone_2') ?? $provider->phone_2 }}">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="email">Endereço de E-mail</label>
                                        <input {{ $disabled }} type="email" class="form-control" id="email" name="email" autocomplete="nope" value="{{ old('email') ?? $provider->email }}">
                                    </div>
                                </div>
                                <div class="row personal_data">
                                    <div class="form-group col-md-4">
                                        <label for="sex" style="top: 15px; left: 0;">Sexo</label><br>
                                        <input {{ $disabled }} type="radio" id="sex_1" name="sex" value="1" style="position: relative; top: 15px;" {{ old('sex') ?? $provider->sex == '1' ? 'checked' : '' }}> <label for="sex_1" style="top: 17px; left: 0; pointer-events: none;">Masculino</label>
                                        <input {{ $disabled }} type="radio" id="sex_2" name="sex" value="2" style="position: relative; top: 15px;" {{ old('sex') ?? $provider->sex == '2' ? 'checked' : '' }}> <label for="sex_2" style="top: 17px; left: 0; pointer-events: none;">Feminino</label>
                                        <input {{ $disabled }} type="radio" id="sex_3" name="sex" value="3" style="position: relative; top: 15px;" {{ old('sex') ?? $provider->sex == '3' ? 'checked' : '' }}> <label for="sex_3" style="top: 17px; left: 0; pointer-events: none;">Outro</label>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="birth_date">Data de Nascimento</label>
                                        <input {{ $disabled }} type="date" class="form-control" id="birth_date" name="birth_date" autocomplete="nope" value="{{ old('birth_date') ?? $provider->birth_date }}">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="nationality">Nacionalidade</label>
                                        <select {{ $disabled }} class="form-control" id="nationality" name="nationality"></select>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="marital_status">Estado Civíl</label>
                                        <select {{ $disabled }} class="form-control" id="marital_status" name="marital_status"></select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <label for="observation">Observação</label>
                                        <textarea {{ $disabled }} class="form-control" id="observation" name="observation" rows="3">{{ old('observation') ?? $provider->observation }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card display-none">
                            <div class="card-body">
                                <div class="row">
                                    <div class="form-group col-md-3">
                                        <label>CEP</label>
                                        <input {{ $disabled }} type="text" class="form-control" name="cep" autocomplete="nope" value="{{ old('cep') ?? $provider->cep }}">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Endereço</label>
                                        <input {{ $disabled }} type="text" class="form-control" name="address" autocomplete="nope" value="{{ old('address') ?? $provider->address }}">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Número</label>
                                        <input {{ $disabled }} type="text" class="form-control" name="number" autocomplete="nope" value="{{ old('number') ?? $provider->number }}">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label>Complemento</label>
                                        <input {{ $disabled }} type="text" class="form-control" name="complement" autocomplete="nope" value="{{ old('complement') ?? $provider->complement }}">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Referência</label>
                                        <input {{ $disabled }} type="text" class="form-control" name="reference" autocomplete="nope" value="{{ old('reference') ?? $provider->reference }}">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label>Bairro</label>
                                        <input {{ $disabled }} type="text" class="form-control" name="neigh" autocomplete="nope" value="{{ old('neigh') ?? $provider->neigh }}">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Cidade</label>
                                        <input {{ $disabled }} type="text" class="form-control" name="city" autocomplete="nope" value="{{ old('city') ?? $provider->city }}">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Estado</label>
                                        <input {{ $disabled }} type="text" class="form-control" name="state" autocomplete="nope" value="{{ old('state') ?? $provider->state }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card display-none">
                            <div class="card-body d-flex justify-content-between">
                                <a href="{{ route('provider.index') }}" class="btn btn-secondary col-md-3"><i class="fa fa-arrow-left"></i> Cancelar</a>
                                @if($btns)<button type="submit" class="btn btn-success col-md-3"><i class="fa fa-sync"></i> Atualizar</button>@endif
                            </div>
                        </div>
                        @if($btns)
                        <input type="hidden" name="provider_id" value="{{ $provider->id }}">
                        <input type="hidden" name="nationality_id" value="{{ $provider->nationality }}">
                        <input type="hidden" name="marital_status_id" value="{{ $provider->marital_status }}">
                        {{ csrf_field() }}
                        @endif
                    </form>
                </div>
            </div>
        </div>
        @if($btns)
        <div class="modal fade" tabindex="-1" role="dialog" id="confirmAddress">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirmar Endereço no Mapa</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12 form-group text-center mb-2">
                                <button type="button" class="btn btn-primary" id="updateLocationMap"><i class="fas fa-sync-alt"></i> Atualizar Localização</button>
                            </div>
                        </div>
                        <div class="row">
                            <div id="map" style="height: 400px"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary col-md-3" data-dismiss="modal">Confirmar</button>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
@stop
