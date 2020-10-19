@extends('adminlte::page')

@section('title', 'Alterar de Cliente')

@section('content_header')
    <h1 class="m-0 text-dark">Alterar de Cliente</h1>
@stop

@section('css')
@stop

@section('js')
<script>
    $('[name="type_person"]').on('change', function(){
        const type = $(this).val();

        if (type === 'pf') {
            $('label[for="name_client"]').html('Nome do Cliente <sup>*</sup>');
            $('#name_client').closest('.form-group').removeClass('col-md-6').addClass('col-md-12');
            $('label[for="cpf_cnpj"]').text('CPF');
            $('label[for="rg_ie"]').text('RG');
            $('#fantasy_client').val('').closest('.form-group').addClass('d-none');
            $('[name="cpf_cnpj"]').mask('000.000.000-00');
        }
        else if (type === 'pj') {
            $('label[for="name_client"]').html('Razão Social <sup>*</sup>');
            $('#name_client').closest('.form-group').removeClass('col-md-12').addClass('col-md-6');
            $('label[for="cpf_cnpj"]').text('CNPJ');
            $('label[for="rg_ie"]').text('IE');
            $('#fantasy_client').closest('.form-group').removeClass('d-none');
            $('[name="cpf_cnpj"]').mask('00.000.000/0000-00');
        }

        $(".card").each(function() {
            $(this).slideDown('slow');
        });
    });

    $(document).on('blur', '[name="cep[]"]', function (){
        const cep = $(this).val().replace(/\D/g, '');
        let el;
        if ($(this).closest('#new-addressses').length)
            el = $(this).closest('.box');
        else
            el = $(this).closest('.card-body');

        if (cep.length === 0) return false;
        if (cep.length !== 8) {
            Toast.fire({
                icon: 'error',
                title: 'CEP não encontrado'
            });
            return false;
        }
        $.getJSON("https://viacep.com.br/ws/"+ cep +"/json/", function(dados) {

            if (!("erro" in dados)) {
                if(dados.logradouro !== '') el.find('[name^="address"]').val(dados.logradouro).parent().addClass("label-animate");
                if(dados.bairro !== '')     el.find('[name="neigh[]"]').val(dados.bairro).parent().addClass("label-animate");
                if(dados.localidade !== '') el.find('[name="city[]"]').val(dados.localidade).parent().addClass("label-animate");
                if(dados.uf !== '')         el.find('[name="state[]"]').val(dados.uf).parent().addClass("label-animate");
            } //end if.
            else {
                Toast.fire({
                    icon: 'error',
                    title: 'CEP não encontrado'
                })
            }
        });
    })

    $(() => {
        $('[name="cep[]"]').mask('00.000-000');
        $('[name="phone_1"],[name="phone_2"]').mask('(00) 000000000');
        $('[name="rg_ie"]').mask('0#');
        if ($('[name="type_person"]:checked').length) {
            $('[name="type_person"]:checked').trigger('change');
            $(".form-control").each(function() {
                if ($(this).val() != '')
                    $(this).parent().addClass("label-animate");
            });
        }
    });

    // Validar dados
    const container = $("div.error-form");
    // validate the form when it is submitted
    $("#formCreateClient").validate({
        errorContainer: container,
        errorLabelContainer: $("ol", container),
        wrapper: 'li',
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
        invalidHandler: function(event, validator) {

        },
        submitHandler: function(form) {
            $('#formCreateClient [type="submit"]').attr('disabled', true);
            form.submit();
        }
    });

    jQuery.validator.addMethod("cpf_cnpj", function(value, element) {
        value = jQuery.trim(value);

        let retorno =  $('[name="type_person"]:checked').val() === 'pf' ? validCPF(value) : validCNPJ(value);

        return this.optional(element) || retorno;

    }, $('[name="type_person"]:checked').val() === 'pf' ? 'Informe um CPF válido' : 'Informe um CNPJ válido');

    $('#add-new-address').on('click', function () {

        let countAddress = 0;
        countAddress = $('#new-addressses [name="name_address[]"]').length + 1;


        $('#new-addressses').append(`
        <div class="box box-primary">
            <div class="box-header">
                <h5 class="mb-0 d-flex justify-content-between">
                    <button class="btn btn-link" type="button" data-widget="collapse">
                        <i class="fa fa-caret-right"></i> ${countAddress}º Novo Endereço
                    </button>
                    <button type="button" class="btn btn-danger remove-address"><i class="fa fa-trash"></i></button>
                </h5>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="form-group col-md-12">
                        <label>Nome do Endereço</label>
                        <input type="text" class="form-control" name="name_address[]" autocomplete="nope">
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-3">
                        <label>CEP</label>
                        <input type="text" class="form-control" name="cep[]" autocomplete="nope">
                    </div>
                    <div class="form-group col-md-6">
                        <label>Endereço</label>
                        <input type="text" class="form-control" name="address[]" autocomplete="nope">
                    </div>
                    <div class="form-group col-md-3">
                        <label>Número</label>
                        <input type="text" class="form-control" name="number[]" autocomplete="nope">
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-6">
                        <label>Complemento</label>
                        <input type="text" class="form-control" name="complement[]" autocomplete="nope">
                    </div>
                    <div class="form-group col-md-6">
                        <label>Referência</label>
                        <input type="text" class="form-control" name="reference[]" autocomplete="nope">
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-4">
                        <label>Bairro</label>
                        <input type="text" class="form-control" name="neigh[]" autocomplete="nope">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Cidade</label>
                        <input type="text" class="form-control" name="city[]" autocomplete="nope">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Estado</label>
                        <input type="text" class="form-control" name="state[]" autocomplete="nope">
                    </div>
                </div>
            </div>
        </div>
        `);

        setTimeout(() => { if ($('.box').length !== 0) $('#no-have-address').slideUp(500) }, 500);
        $('[name="cep[]"]').mask('00.000-000');
    });

    $(document).on('click', '.remove-address', function (){
        $(this).closest('.box').slideUp(500);
        setTimeout(() => { $(this).closest('.box').remove() }, 500);
        setTimeout(() => { if ($('.box').length === 0) $('#no-have-address').slideDown(500) }, 750);
    })

</script>
@stop

@section('content')

    <div class="row">
        <div class="col-md-12 d-flex align-items-stretch grid-margin">
            <div class="row flex-grow">
                <div class="col-12">
                    <div class="error-form alert alert-warning {{ count($errors) == 0 ? 'display-none' : '' }}">
                        <h5>Existem erros no envio do formulário, veja abaixo para corrigi-los.</h5>
                        <ol>
                            @foreach($errors->all() as $error)
                                <li><label id="name-error" class="error">{{ $error }}</label></li>
                            @endforeach
                        </ol>
                    </div>
                    <form action="{{ route(('client.update')) }}" method="POST" enctype="multipart/form-data" id="formCreateClient">
                        <div class="card">
                            <div class="card-body d-flex justify-content-around">
                                <div class="form-radio form-radio-flat">
                                    <label class="form-check-label">
                                        <input type="radio" class="form-check-input" name="type_person" value="pf" {{ old() ? (old('type_person') === 'pf' ? 'checked' : '') : ($client->type === 'pf' ? 'checked' : '') }}> Pessoa Física <i class="input-helper"></i>
                                    </label>
                                </div>
                                <div class="form-radio form-radio-flat">
                                    <label class="form-check-label">
                                        <input type="radio" class="form-check-input" name="type_person" value="pj" {{ old() ? (old('type_person') === 'pj' ? 'checked' : '') : ($client->type === 'pj' ? 'checked' : '') }}> Pessoa Jurídica <i class="input-helper"></i>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="card display-none">
                            <div class="card-body">
                                <div class="header-card-body">
                                    <h4 class="card-title">Dados do Cliente</h4>
                                    <p class="card-description"> Altere o formulário abaixo com as informações do cliente </p>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <label for="name_client">Nome do Cliente <sup>*</sup></label>
                                        <input type="text" class="form-control" id="name_client" name="name_client" autocomplete="nope" value="{{ old('name_client') ?? $client->name }}" required>
                                    </div>
                                    <div class="form-group col-md-6 d-none">
                                        <label for="fantasy_client">Fantasia</label>
                                        <input type="text" class="form-control" id="fantasy_client" name="fantasy_client" autocomplete="nope" value="{{ old('fantasy_client') ?? $client->fantasy }}">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label for="phone_1">Telefone Principal</label>
                                        <input type="text" class="form-control" id="phone_1" name="phone_1" autocomplete="nope" value="{{ old('phone_1') ?? $client->phone_1 }}">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="phone_2">Telefone Secundário</label>
                                        <input type="text" class="form-control" id="phone_2" name="phone_2" autocomplete="nope" value="{{ old('phone_2') ?? $client->phone_2 }}">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="email">Endereço de E-mail</label>
                                        <input type="email" class="form-control" id="email" name="email" autocomplete="nope" value="{{ old('email') ?? $client->email }}">
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
                                        <input type="text" class="form-control" id="cpf_cnpj" name="cpf_cnpj" autocomplete="nope" value="{{ old('cpf_cnpj') ?? $client->cpf_cnpj }}">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="rg_ie">RG</label>
                                        <input type="text" class="form-control" id="rg_ie" name="rg_ie" autocomplete="nope" value="{{ old('rg_ie') ?? $client->rg_ie }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card display-none">
                            <div class="card-body">
                                <div class="header-card-body">
                                    <h4 class="card-title">Dados do Endereço</h4>
                                    <p class="card-description"> Altere o formulário abaixo com as informações de endereço </p>
                                </div>
                                <div class="accordion form-group" id="accordionAddress">
                                    @if (count($addresses))
                                        @foreach($addresses as $address)
                                        <div class="box collapsed-box box-primary">
                                            <div class="box-header">
                                                <h5 class="mb-0 d-flex justify-content-between">
                                                    <button class="btn btn-link" type="button" data-widget="collapse">
                                                        <i class="fa fa-caret-right"></i> {{ $address->name_address }}
                                                    </button>
                                                    <button type="button" class="btn btn-danger remove-address"><i class="fa fa-trash"></i></button>
                                                </h5>
                                            </div>
                                            <div class="box-body display-none">
                                                <div class="row">
                                                    <div class="form-group col-md-12">
                                                        <label>Nome do Endereço</label>
                                                        <input type="text" class="form-control" name="name_address[]" autocomplete="nope" value="{{ $address->name_address }}">
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="form-group col-md-3">
                                                        <label>CEP</label>
                                                        <input type="text" class="form-control" name="cep[]" autocomplete="nope" value="{{ $address->cep }}">
                                                    </div>
                                                    <div class="form-group col-md-6">
                                                        <label>Endereço</label>
                                                        <input type="text" class="form-control" name="address[]" autocomplete="nope" value="{{ $address->address }}">
                                                    </div>
                                                    <div class="form-group col-md-3">
                                                        <label>Número</label>
                                                        <input type="text" class="form-control" name="number[]" autocomplete="nope" value="{{ $address->number }}">
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="form-group col-md-6">
                                                        <label>Complemento</label>
                                                        <input type="text" class="form-control" name="complement[]" autocomplete="nope" value="{{ $address->complement }}">
                                                    </div>
                                                    <div class="form-group col-md-6">
                                                        <label>Referência</label>
                                                        <input type="text" class="form-control" name="reference[]" autocomplete="nope" value="{{ $address->reference }}">
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="form-group col-md-4">
                                                        <label>Bairro</label>
                                                        <input type="text" class="form-control" name="neigh[]" autocomplete="nope" value="{{ $address->neigh }}">
                                                    </div>
                                                    <div class="form-group col-md-4">
                                                        <label>Cidade</label>
                                                        <input type="text" class="form-control" name="city[]" autocomplete="nope" value="{{ $address->city }}">
                                                    </div>
                                                    <div class="form-group col-md-4">
                                                        <label>Estado</label>
                                                        <input type="text" class="form-control" name="state[]" autocomplete="nope" value="{{ $address->state }}">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    @endif
                                    <div class="alert alert-warning {{count($addresses)?'display-none':''}}" id="no-have-address"><h4 class="text-center">Não existem endereços ainda.</h4></div>
                                    <div id="new-addressses"></div>
                                </div>
                                <div class="col-md-12 text-center">
                                    <button type="button" class="btn btn-primary" id="add-new-address">Adicionar Endereço</button>
                                </div>
                            </div>
                        </div>
                        <div class="card display-none">
                            <div class="card-body d-flex justify-content-between">
                                <a href="{{ route('client.index') }}" class="btn btn-secondary col-md-3"><i class="fa fa-arrow-left"></i> Cancelar</a>
                                <button type="submit" class="btn btn-success col-md-3"><i class="fa fa-sync"></i> Atualizar</button>
                            </div>
                        </div>
                        <input type="hidden" name="client_id" value="{{ $client->id }}">
                        {{ csrf_field() }}
                    </form>
                </div>
            </div>
        </div>
    </div>

@stop
