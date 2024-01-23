@extends('adminlte::page')

@section('title', 'Alterar Empresa')

@section('content_header')
    <h1 class="m-0 text-dark">Alterar Empresa</h1>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/material_blue.css">
    <style>

        .flatpickr a.input-button,
        .flatpickr button.input-button{
            height: calc(1.5em + 0.75rem + 3px);
            width: 50%;
            /*text-align: center;*/
            /*padding-top: 13%;*/
            cursor: pointer;
            border: 1px solid transparent;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .flatpickr a.input-button:last-child,
        .flatpickr button.input-button:last-child{
            border-bottom-right-radius: 5px;
            border-top-right-radius: 5px;
        }
        [name="plan_expiration_date"] {
            border-bottom-right-radius: 0 !important;
            border-top-right-radius: 0 !important;;
        }
    </style>
@stop

@section('js')
<script src="{{ asset('assets/js/shared/file-upload.js') }}" type="application/javascript"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr" type="application/javascript"></script>
<script src="https://npmcdn.com/flatpickr@4.6.6/dist/l10n/pt.js" type="application/javascript"></script>
<script>
    $(function(){

        $('[name="cep"]').mask('00.000-000');
        $('#cpf_cnpj').mask($('#cpf_cnpj').val().length === 11 ? '000.000.000-00' : '00.000.000/0000-00');
        $('[name="phone_1"],[name="phone_2"],[name="phone_modal"]').mask('(00) 000000000');

        const src = document.getElementById("profile-logo");
        const target = document.getElementById("src-profile-logo");
        showImage(src,target);
        loadSearchZipcode('#formUpdateCompany [name="cep"]', $('#formUpdateCompany'));
        $('.flatpickr').flatpickr({
            enableTime: true,
            dateFormat: "d/m/Y H:i",
            time_24hr: true,
            wrap: true,
            clickOpens: false,
            allowInput: true,
            locale: "pt",
            onClose: function (selectedDates, dateStr, instance) {
                checkLabelAnimate();
            }
        });
    });

    const showImage = (src,target) => {
        let fr = new FileReader();
        // when image is loaded, set the src of the image where you want to display it
        fr.onload = function(e) { target.src = this.result; };
        src.addEventListener("change",function() {
            // fill fr with image data
            fr.readAsDataURL(src.files[0]);
        });
    }

    // Validar dados
    $("#formUpdateCompany").validate({
        rules: {
            name: {
                required: true
            },
            phone_1: {
                required: true,
                rangelength: [13, 14]
            },
            phone_2: {
                rangelength: [13, 14]
            },
            email: {
                required: true,
                email: true
            },
            address: {
                required: true
            },
            neigh: {
                required: true
            },
            city: {
                required: true
            },
            state: {
                required: true
            }
        },
        messages: {
            name: {
                required: 'Digite o nome/razão social da empresa.'
            },
            phone_1: {
                required: "O campo telefone primário é um campo obrigatório.",
                rangelength: "O campo telefone primário deve ser um telefone válido."
            },
            phone_2: {
                rangelength: "O campo telefone secundário deve ser um telefone válido."
            },
            email: {
                required: "Informe um e-mail comercial válido.",
                email: "Informe um e-mail comercial válido."
            },
            address: {
                required: "Informe o endereço para a empresa."
            },
            neigh: {
                required: "Informe o bairro para a empresa."
            },
            city: {
                required: "Informe a cidade para a empresa."
            },
            state: {
                required: "Informe o estado para a empresa."
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

    $('[name="type_person"]').on('change', function(){
        const type = $(this).val();
        const form = $(this).closest('form');

        if (type === 'pf') {
            form.find('label[for="name"]').html('Nome Completo');
            form.find('#name').closest('.form-group').removeClass('col-md-5').addClass('col-md-10');
            form.find('label[for="cpf_cnpj"]').text('CPF');
            form.find('#fantasy').val('').closest('.form-group').addClass('d-none');
            form.find('[name="cpf_cnpj"]').mask('000.000.000-00');
        }
        else if (type === 'pj') {
            form.find('label[for="name"]').html('Razão Social');
            form.find('#name').closest('.form-group').removeClass('col-md-10').addClass('col-md-5');
            form.find('label[for="cpf_cnpj"]').text('CNPJ');
            form.find('#fantasy').closest('.form-group').removeClass('d-none');
            form.find('[name="cpf_cnpj"]').mask('00.000.000/0000-00');
        }

        form.find(".card").each(function() {
            $(this).slideDown('slow');
        });

        setTimeout(() => {
            $('[name="state"], [name="city"]').select2()
        }, 500)
    });

</script>
@stop

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
                    <form action="{{ route(empty($company) ? 'master.company.insert' : 'master.company.update', empty($company) ? [] : array('id' => $company->id)) }}" method="POST" enctype="multipart/form-data" id="formUpdateCompany">
                        <div class="card">
                            <div class="card-body d-flex flex-wrap">
                                <div class="header-card-body">
                                    <h4 class="card-title">Dados da Empresa</h4>
                                    <p class="card-description"> Preencha o formulário abaixo com as novas informações da empresa </p>
                                </div>
                                <div class="col-md-9 no-padding">
                                    <div class="row d-flex justify-content-around">
                                        <div class="form-radio form-radio-flat">
                                            <label class="form-check-label">
                                                <input type="radio" class="form-check-input" name="type_person" value="pf" @if(!empty($company)) disabled @endif @if(old('type_person', $company->type_person ?? 'pj') === 'pf') checked @endif> Pessoa Física <i class="input-helper"></i>
                                            </label>
                                        </div>
                                        <div class="form-radio form-radio-flat">
                                            <label class="form-check-label">
                                                <input type="radio" class="form-check-input" name="type_person" value="pj" @if(!empty($company)) disabled @endif @if(old('type_person', $company->type_person ?? 'pj') === 'pj') checked @endif> Pessoa Jurídica <i class="input-helper"></i>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="form-group {{ ($company->type_person ?? 'pj') == 'pf' ? 'col-md-10' : 'col-md-5'}}">
                                            <label for="name">{{ ($company->type_person ?? 'pj') == 'pf' ? 'Nome Completo' : 'Razão Social' }}</label>
                                            <input type="text" class="form-control" name="name" id="name" value="{{ old('name', $company->name ?? '') }}" required>
                                        </div>
                                        @if (($company->type_person ?? 'pj') == 'pj')
                                            <div class="form-group col-md-5">
                                                <label for="fantasy">Fantasia</label>
                                                <input type="text" class="form-control" name="fantasy" id="fantasy" value="{{ old('fantasy', $company->fantasy ?? '') }}">
                                            </div>
                                        @endif
                                        <div class="form-group col-md-2">
                                            <div class="switch d-flex mt-4">
                                                <input type="checkbox" class="check-style check-xs" name="status" id="status" {{ old('active', $company->status ?? 'on') ? 'checked' : '' }}>
                                                <label for="status" class="check-style check-xs"></label>&nbsp;Ativo
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="form-group col-md-5">
                                            <label for="cpf_cnpj">{{ ($company->type_person ?? 'pj') == 'pf' ? 'CPF' : 'CNPJ' }}</label>
                                            <input type="tel" class="form-control" id="cpf_cnpj" name="cpf_cnpj" value="{{ old('cpf_cnpj', $company->cpf_cnpj ?? '') }}" {{ empty($company) ? '' : 'disabled' }}>
                                        </div>
                                        <div class="form-group col-md-7">
                                            <label for="email">E-mail Comercial</label>
                                            <input type="email" class="form-control" name="email" id="email" value="{{ old('email', $company->email ?? '') }}" required>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="form-group col-md-3">
                                            <label for="phone_1">Telefone Primário</label>
                                            <input type="tel" class="form-control" name="phone_1" id="phone_1" value="{{ old('phone_1', $company->phone_1 ?? '') }}" required>
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label for="phone_2">Telefone Secundário</label>
                                            <input type="tel" class="form-control" name="phone_2" id="phone_2" value="{{ old('phone_2', $company->phone_2 ?? '') }}">
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label for="contact">Contato</label>
                                            <input type="text" class="form-control" name="contact" id="contact" value="{{ old('contact', $company->contact ?? '') }}">
                                        </div>
                                        <div class="col-md-3 mt-4">
                                            <div class="form-group flatpickr d-flex">
                                                <label class="label-date-btns">Data de Expiração</label>
                                                <input type="tel" name="plan_expiration_date" class="form-control col-md-9" value="{{ old('plan_expiration_date', dateInternationalToDateBrazil($company->plan_expiration_date ?? sumDate(dateNowInternational(), null, null, 15))) }}" data-inputmask="'alias': 'datetime'" data-inputmask-inputformat="dd/mm/yyyy HH:MM" im-insert="false" data-input>
                                                <div class="input-button-calendar col-md-3 no-padding">
                                                    <a class="input-button pull-left btn-primary" title="toggle" data-toggle>
                                                        <i class="fa fa-calendar text-white"></i>
                                                    </a>
                                                    <a class="input-button pull-right btn-primary" title="clear" data-clear>
                                                        <i class="fa fa-times text-white"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="row">
                                        <div class="form-group col-md-12 no-padding mt-4">
                                            <div class="logo-company text-center col-md-12">
                                                <img src="{{ $company->logo ?? '' }}" style="max-height:100px; max-width: 100%" id="src-profile-logo">
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
                                            <input type="tel" class="form-control" name="cep" id="cep" value="{{ old('cep', $company->cep ?? '') }}">
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label for="address">Endereço</label>
                                            <input type="text" class="form-control" name="address" id="address" value="{{ old('address', $company->address ?? '') }}">
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label for="number">Número</label>
                                            <input type="text" class="form-control" name="number" id="number" value="{{ old('number', $company->number ?? '') }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 no-padding">
                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            <label for="complement">Complemento</label>
                                            <input type="text" class="form-control" name="complement" id="complement" value="{{ old('complement', $company->complement ?? '') }}">
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label for="reference">Referência</label>
                                            <input type="text" class="form-control" name="reference" id="reference" value="{{ old('reference', $company->reference ?? '') }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 no-padding">
                                    <div class="row">
                                        <div class="form-group col-md-4">
                                            <label for="neigh">Bairro</label>
                                            <input type="text" class="form-control" name="neigh" id="neigh" value="{{ old('neigh', $company->neigh ?? '') }}">
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label for="city">Cidade</label>
                                            <input type="text" class="form-control" name="city" id="city" value="{{ old('city', $company->city ?? '') }}">
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label for="state">Estado</label>
                                            <input type="text" class="form-control" name="state" id="state" value="{{ old('state', $company->state ?? '') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body d-flex justify-content-between">
                                <a href="{{ route('master.company.index') }}" class="btn btn-secondary col-md-3"><i class="fa fa-arrow-left"></i> Cancelar</a>
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
