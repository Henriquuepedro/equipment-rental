@extends('adminlte::page')

@section('title', 'Cadastro de Equipamento')

@section('content_header')
    <h1 class="m-0 text-dark">Cadastro de Equipamento</h1>
@stop

@section('css')
@stop

@section('js')
<script>
    $(() => {
        $('[name="cep"]').mask('00.000-000');
        $('[name="phone_1"],[name="phone_2"]').mask('(00) 000000000');
        $('[name="stock"]').mask('0#');
        $('[name="value"]').mask('#.##0,00', { reverse: true });
        $('[name="day_start[]"], [name="day_end[]"]').mask('0#');
        $('[name="value_period[]"]').mask('#.##0,00', { reverse: true });
        if ($('[name="type_equipament"]:checked').length) {
            $('[name="type_equipament"]:checked').trigger('change');
            $(".form-control").each(function() {
                if ($(this).val() != '')
                    $(this).parent().addClass("label-animate");
            });
        }
    });

    $('[name="type_equipament"]').on('change', function(){
        const type = $(this).val();

        if (type === 'cacamba') {
            $('#name').val('').closest('.form-group').addClass('d-none');
            $('#volume').closest('.form-group').removeClass('d-none');
        }
        else if (type === 'others') {
            $('#volume').val($('#volume option:eq(0)').val()).closest('.form-group').addClass('d-none');
            $('#name').closest('.form-group').removeClass('d-none');
        }

        $('.error-form').slideUp('slow');
        $(".card").each(function() {
            $(this).slideDown('slow');
        });
    });

    // Validar dados
    const container = $("div.error-form");
    // validate the form when it is submitted
    $("#formCreateEquipament").validate({
        errorContainer: container,
        errorLabelContainer: $("ol", container),
        wrapper: 'li',
        rules: {
            name: {
                name_valid: true
            },
            volume: {
                volume: true
            },
            reference: {
                required: true
            }
        },
        messages: {
            reference: {
                required: "Informe uma referência/código/numeração para seu equipamento"
            }
        },
        invalidHandler: function(event, validator) {
            $('html, body').animate({scrollTop:0}, 'slow');
        },
        submitHandler: function(form) {
            let verifyPeriod = verifyPeriodComplet();
            if (!verifyPeriod[0]) {
                Toast.fire({
                    icon: 'warning',
                    title: `Finalize o cadastro do ${verifyPeriod[1]}º período, para finalizar o cadastro.`
                });
                return false;
            }

            $('#formCreateEquipament [type="submit"]').attr('disabled', true);
            form.submit();
        }
    });


    jQuery.validator.addMethod("name_valid", function(value, element) {
        console.log(element);
        value = jQuery.trim(value);
        return value !== "";

    }, 'Informe um nome para o equipamento');

    jQuery.validator.addMethod("volume", function(value, element) {
        value = jQuery.trim(value);
        return value !== "Selecione ...";

    }, 'Selecione um volume para a caçamba');

    $('#add-new-period').on('click', function () {

        const verifyPeriod = verifyPeriodComplet();
        if (!verifyPeriod[0]) {
            Toast.fire({
                icon: 'warning',
                title: `Finalize o cadastro do ${verifyPeriod[1]}º período, para adicionar um novo.`
            });
            return false;
        }

        let countPeriod = 0;
        countPeriod = $('.period').length + 1;

        $('#new-periods').append(`
            <div class="period display-none">
                <div class="row">
                    <div class="form-group col-md-2">
                        <label>${countPeriod}º Período</label>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Dia Inicial</label>
                        <input type="text" class="form-control" name="day_start[]" autocomplete="nope">
                    </div>
                    <div class="form-group col-md-3">
                        <label>Dia Final</label>
                        <input type="text" class="form-control" name="day_end[]" autocomplete="nope">
                    </div>
                    <div class="form-group col-md-3">
                        <label>Valor</label>
                        <input type="text" class="form-control" name="value_period[]" autocomplete="nope">
                    </div>
                    <div class="form-group col-md-1">
                        <label>&nbsp;</label>
                        <button type="button" class="btn btn-danger remove-period col-md-12"><i class="fa fa-trash"></i></button>
                    </div>
                </div>
            </div>
        `).find('.period').slideDown('slow');

        $('[name="day_start[]"], [name="day_end[]"]').mask('0#');
        $('[name="value_period[]"]').mask('#.##0,00', { reverse: true });
        $('#no-have-period').slideUp(500);
    });

    $(document).on('click', '.remove-period', function (){
        $(this).closest('.period').slideUp(500);
        setTimeout(() => { if ($('.period').length === 0) $('#no-have-period').slideDown(500) }, 600);
        setTimeout(() => { $(this).closest('.period').remove() }, 500);
    });

    const verifyPeriodComplet = () => {
        cleanBorderPeriod();

        const periodCount = $('.period').length;
        let existError = false;
        for (let countPeriod = 0; countPeriod < periodCount; countPeriod++) {
            if (!$(`[name="day_start[]"]:eq(${countPeriod})`).val().length) {
                $(`[name="day_start[]"]:eq(${countPeriod})`).css('border', '1px solid red');
                existError = true;
            }
            if (!$(`[name="day_end[]"]:eq(${countPeriod})`).val().length) {
                $(`[name="day_end[]"]:eq(${countPeriod})`).css('border', '1px solid red');
                existError = true;
            }
            if (!$(`[name="value_period[]"]:eq(${countPeriod})`).val().length) {
                $(`[name="value_period[]"]:eq(${countPeriod})`).css('border', '1px solid red');
                existError = true;
            }
            if (existError) return [false, (countPeriod + 1)];
        }
        return [true];
    }

    const cleanBorderPeriod = () => {
        $('[name="day_start[]"]').removeAttr('style');
        $('[name="day_end[]"]').removeAttr('style');
        $('[name="value_period[]"]').removeAttr('style');
    }


    $(document).on('keydown', function(e){
        if(e.keyCode == 13){
            return false;
        }
    });
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
                    <form action="{{ route(('equipament.insert')) }}" method="POST" enctype="multipart/form-data" id="formCreateEquipament">
                        <div class="card">
                            <div class="card-body d-flex justify-content-around">
                                <div class="form-radio form-radio-flat">
                                    <label class="form-check-label">
                                        <input type="radio" class="form-check-input" name="type_equipament" value="cacamba" @if(old('type_equipament') === 'cacamba') checked @endif> Caçamba <i class="input-helper"></i>
                                    </label>
                                </div>
                                <div class="form-radio form-radio-flat">
                                    <label class="form-check-label">
                                        <input type="radio" class="form-check-input" name="type_equipament" value="others" @if(old('type_equipament') === 'others') checked @endif> Outros <i class="input-helper"></i>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="card display-none">
                            <div class="card-body">
                                <div class="header-card-body">
                                    <h4 class="card-title">Dados do Equipamento</h4>
                                    <p class="card-description"> Preencha o formulário abaixo com as informações do novo equipamento </p>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-4 label-animate">
                                        <label for="volume">Volume <sup>*</sup></label>
                                        <select class="form-control " id="volume" name="volume">
                                            <option {{ old('volume') == '' ? 'selected' : '' }}>Selecione ...</option>
                                            <option value="3" {{ old('volume') == 3 ? 'selected' : '' }}>3m³</option>
                                            <option value="4" {{ old('volume') == 4 ? 'selected' : '' }}>4m³</option>
                                            <option value="5" {{ old('volume') == 5 ? 'selected' : '' }}>5m³</option>
                                            <option value="6" {{ old('volume') == 6 ? 'selected' : '' }}>6m³</option>
                                            <option value="7" {{ old('volume') == 7 ? 'selected' : '' }}>7m³</option>
                                            <option value="8" {{ old('volume') == 8 ? 'selected' : '' }}>8m³</option>
                                            <option value="9" {{ old('volume') == 9 ? 'selected' : '' }}>9m³</option>
                                            <option value="10" {{ old('volume') == 10 ? 'selected' : '' }}>10m³</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="name">Nome do Equipamento <sup>*</sup></label>
                                        <input type="text" class="form-control" id="name" name="name" autocomplete="nope" value="{{ old('name') }}">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="reference">Referência <sup>*</sup></label>
                                        <input type="text" class="form-control" id="reference" name="reference" autocomplete="nope" value="{{ old('reference') }}" required>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="manufacturer">Fabricante </label>
                                        <input type="text" class="form-control" id="manufacturer" name="manufacturer" autocomplete="nope" value="{{ old('manufacturer') }}">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label for="value">Valor Por Dia</label>
                                        <input type="text" class="form-control" id="value" name="value" autocomplete="nope" value="{{ old('value') }}">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="stock">Estoque</label>
                                        <label for="stock"></label><input type="text" class="form-control" id="stock" name="stock" autocomplete="nope" value="{{ old('stock') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card display-none">
                            <div class="card-body">
                                <div class="header-card-body">
                                    <h4 class="card-title">Valores</h4>
                                    <p class="card-description"> Preencha o formulário abaixo com as informações de valores, defindo por intervalos</p>
                                </div>
                                @if (old('day_start') && count(old('day_start')))
                                    @for($period = 0; $period < count(old('day_start')); $period++)
                                        @php
                                            $numberNewPeriod = $period + 1;
                                        @endphp
                                        <div class="period">
                                            <div class="row">
                                                <div class="form-group col-md-2">
                                                    <label>{{ $numberNewPeriod }}º Período</label>
                                                </div>
                                                <div class="form-group col-md-3">
                                                    <label>Dia Inicial</label>
                                                    <input type="text" class="form-control" name="day_start[]" autocomplete="nope" value="{{ old('day_start')[$period] }}">
                                                </div>
                                                <div class="form-group col-md-3">
                                                    <label>Dia Final</label>
                                                    <input type="text" class="form-control" name="day_end[]" autocomplete="nope" value="{{ old('day_end')[$period] }}">
                                                </div>
                                                <div class="form-group col-md-3">
                                                    <label>Valor</label>
                                                    <input type="text" class="form-control" name="value_period[]" autocomplete="nope" value="{{ old('value_period')[$period] }}">
                                                </div>
                                                <div class="form-group col-md-1">
                                                    <label>&nbsp;</label>
                                                    <button type="button" class="btn btn-danger remove-period col-md-12"><i class="fa fa-trash"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                    @endfor
                                @endif
                                <div class="alert alert-warning mt-2 {{old('day_start')?'display-none':''}}" id="no-have-period"><h4 class="text-center no-margin">Não existem períodos ainda.</h4></div>
                                <div id="new-periods" class="mt-2"></div>
                                <div class="col-md-12 text-center">
                                    <button type="button" class="btn btn-primary" id="add-new-period">Adicionar Novo Período</button>
                                </div>
                                <div class="col-md-12 text-center mt-2">
                                    <p class="text-danger">Caso opte por não adicionar períodos, no cadastro de uma nova locação será segerido o valor por dia cadastrado no equipamento.</p>
                                </div>
                            </div>
                        </div>
                        <div class="card display-none">
                            <div class="card-body d-flex justify-content-between">
                                <a href="{{ route('equipament.index') }}" class="btn btn-secondary col-md-3"><i class="fa fa-arrow-left"></i> Cancelar</a>
                                <button type="submit" class="btn btn-success col-md-3"><i class="fa fa-save"></i> Cadastrar</button>
                            </div>
                        </div>
                        {{ csrf_field() }}
                    </form>
                </div>
            </div>
        </div>
    </div>

@stop
