@extends('adminlte::page')

@section('title', 'Cadastro de Cliente')

@section('content_header')
    <h1 class="m-0 text-dark">Cadastro de Cliente</h1>
@stop

@section('css')
@stop

@section('js')
<script src="{{ asset('assets/js/views/client/form.js') }}" type="application/javascript"></script>
<script>
    // Validar dados
    $("#formCreateClient").validate({
        rules: {
            name_client: {
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
            name_client: {
                required: 'Informe um nome/razão social para o cliente'
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
            let verifyAddress = verifyAddressComplet();
            if (!verifyAddress[0]) {
                Toast.fire({
                    icon: 'warning',
                    title: `Finalize o cadastro do ${verifyAddress[1]}º endereço, para finalizar o cadastro.`
                });
                return false;
            }

            $('#formCreateClient [type="submit"]').attr('disabled', true);
            form.submit();
        }
    });
</script>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12 d-flex align-items-stretch grid-margin">
            <div class="row flex-grow">
                <div class="col-12">
                    @if ($errors->any())
                    <div class="alert alert-warning">
                        <ol>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ol>
                    </div>
                    @endif
                    <form action="{{ route(('client.insert')) }}" method="POST" enctype="multipart/form-data" id="formCreateClient">
                        <div class="card">
                            <div class="card-body d-flex justify-content-around">
                                <div class="form-radio form-radio-flat">
                                    <label class="form-check-label">
                                        <input type="radio" class="form-check-input" name="type_person" value="pf" @if(old('type_person') === 'pf') checked @endif> Pessoa Física <i class="input-helper"></i>
                                    </label>
                                </div>
                                <div class="form-radio form-radio-flat">
                                    <label class="form-check-label">
                                        <input type="radio" class="form-check-input" name="type_person" value="pj" @if(old('type_person') === 'pj') checked @endif> Pessoa Jurídica <i class="input-helper"></i>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="card display-none">
                            <div class="card-body">
                                <div class="header-card-body">
                                    <h4 class="card-title">Dados do Cliente</h4>
                                    <p class="card-description"> Preencha o formulário abaixo com as informações do novo cliente </p>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <label for="name_client">Nome do Cliente <sup>*</sup></label>
                                        <input type="text" class="form-control" id="name_client" name="name_client" autocomplete="nope" value="{{ old('name_client') }}" required>
                                    </div>
                                    <div class="form-group col-md-6 d-none">
                                        <label for="fantasy_client">Fantasia</label>
                                        <input type="text" class="form-control" id="fantasy_client" name="fantasy_client" autocomplete="nope" value="{{ old('fantasy_client') }}">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label for="cpf_cnpj">CPF</label>
                                        <input type="text" class="form-control" id="cpf_cnpj" name="cpf_cnpj" autocomplete="nope" value="{{ old('cpf_cnpj') }}">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="rg_ie">RG</label>
                                        <input type="text" class="form-control" id="rg_ie" name="rg_ie" autocomplete="nope" value="{{ old('rg_ie') }}">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="phone_1">Telefone Principal</label>
                                        <input type="text" class="form-control" id="phone_1" name="phone_1" autocomplete="nope" value="{{ old('phone_1') }}">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label for="contact">Contato</label>
                                        <input type="text" class="form-control" id="contact" name="contact" autocomplete="nope" value="{{ old('contact') }}">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="phone_2">Telefone Secundário</label>
                                        <input type="text" class="form-control" id="phone_2" name="phone_2" autocomplete="nope" value="{{ old('phone_2') }}">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="email">Endereço de E-mail</label>
                                        <input type="email" class="form-control" id="email" name="email" autocomplete="nope" value="{{ old('email') }}">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <label for="observation">Observação</label>
                                        <textarea class="form-control" id="observation" name="observation" rows="3">{{ old('observation') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card display-none">
                            <div class="card-body">
                                <div class="header-card-body">
                                    <h4 class="card-title">Dados do Endereço</h4>
                                    <p class="card-description"> Preencha o formulário abaixo com as informações de endereço </p>
                                </div>
                                <div class="accordion form-group" id="accordionAddress">
                                    @if (old('name_address') && count(old('name_address')))
                                        @for($addr = 0; $addr < count(old('name_address')); $addr++)
                                            @php
                                                $numberNewAddress = $addr + 1;
                                            @endphp
                                            <div class="box collapsed-box box-primary">
                                                <div class="box-header">
                                                    <h5 class="mb-0 d-flex justify-content-between">
                                                        <button class="btn btn-link no-sublime" type="button">
                                                            <i class="fa fa-caret-right"></i>  <strong>{{ old('name_address')[$addr] }} |</strong> {{ old('cep')[$addr] }} - {{ old('address')[$addr] }}, {{ old('number')[$addr] }} - {{ old('neigh')[$addr] }} - {{ old('city')[$addr] }} - {{old('state')[$addr] }}
                                                        </button>
                                                        <div class="col-md-2 text-right no-padding">
                                                            <button type="button" class="btn btn-primary edit-address"><i class="fa fa-edit"></i></button>
                                                            <button type="button" class="btn btn-danger remove-address"><i class="fa fa-trash"></i></button>
                                                        </div>
                                                    </h5>
                                                </div>
                                                <div class="box-body display-none">
                                                    <div class="row">
                                                        <div class="form-group col-md-12">
                                                            <label>Nome de Controle</label>
                                                            <input type="text" class="form-control" name="name_address[]" autocomplete="nope" value="{{ old('name_address')[$addr] }}">
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="form-group col-md-3">
                                                            <label>CEP</label>
                                                            <input type="text" class="form-control" name="cep[]" autocomplete="nope" value="{{ old('cep')[$addr] }}">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Endereço</label>
                                                            <input type="text" class="form-control" name="address[]" autocomplete="nope" value="{{ old('address')[$addr] }}">
                                                        </div>
                                                        <div class="form-group col-md-3">
                                                            <label>Número</label>
                                                            <input type="text" class="form-control" name="number[]" autocomplete="nope" value="{{ old('number')[$addr] }}">
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="form-group col-md-6">
                                                            <label>Complemento</label>
                                                            <input type="text" class="form-control" name="complement[]" autocomplete="nope" value="{{ old('complement')[$addr] }}">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>Referência</label>
                                                            <input type="text" class="form-control" name="reference[]" autocomplete="nope" value="{{ old('reference')[$addr] }}">
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="form-group col-md-4">
                                                            <label>Bairro</label>
                                                            <input type="text" class="form-control" name="neigh[]" autocomplete="nope" value="{{ old('neigh')[$addr] }}">
                                                        </div>
                                                        <div class="form-group col-md-4">
                                                            <label>Cidade</label>
                                                            <input type="text" class="form-control" name="city[]" autocomplete="nope" value="{{ old('city')[$addr] }}">
                                                        </div>
                                                        <div class="form-group col-md-4">
                                                            <label>Estado</label>
                                                            <input type="text" class="form-control" name="state[]" autocomplete="nope" value="{{ old('state')[$addr] }}">
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="form-group d-flex justify-content-between flex-wrap col-md-12">
                                                            <button type="button" class="btn btn-link confirm-map text-center"><i class="fas fa-map-marked-alt"></i> Confirmar Endereço no Mapa</button>
                                                            <button type="button" class="btn btn-success text-center save-address"><i class="fa fa-save"></i> Salvar</button>
                                                        </div>
                                                    </div>
                                                    <input type="hidden" name="lat[]" value="{{ old('lat')[$addr] }}"/>
                                                    <input type="hidden" name="lng[]" value="{{ old('lng')[$addr] }}"/>
                                                </div>
                                            </div>
                                        @endfor
                                    @endif
                                    <div id="new-addressses"></div>
                                </div>
                                <div class="col-md-12 text-center">
                                    <button type="button" class="btn btn-primary" id="add-new-address">Adicionar Novo Endereço</button>
                                </div>
                            </div>
                        </div>
                        <div class="card display-none">
                            <div class="card-body d-flex justify-content-between">
                                <a href="{{ route('client.index') }}" class="btn btn-secondary col-md-3"><i class="fa fa-arrow-left"></i> Cancelar</a>
                                <button type="submit" class="btn btn-success col-md-3"><i class="fa fa-save"></i> Cadastrar</button>
                            </div>
                        </div>
                        {{ csrf_field() }}
                    </form>
                </div>
            </div>
        </div>
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
                            <div class="col-md-12 form-group text-center">
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
    </div>
@stop
