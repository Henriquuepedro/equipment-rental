@extends('adminlte::page')

@section('title', 'Cadastro de Fornecedor')

@section('content_header')
    <h1 class="m-0 text-dark">Cadastro de Fornecedor</h1>
@stop

@section('css')
    <style>
        #descriptionDiv {
            border-top-right-radius: 0;
            border-top-left-radius: 0;
        }
    </style>
@stop

@section('js')
<script>
    $(function(){
        new Quill('#descriptionDiv', {
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline'],
                    [{ 'align': [] }],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    [{ 'color': [] }, { 'background': [] }],
                    ['link']
                ]
            },
            theme: 'snow' // or 'bubble'
        });
        getOptionsForm('form-of-payment', $('#formCreateBillsToPay [name="form_payment"]'));
        $('#value').maskMoney({thousands: '.', decimal: ',', allowZero: true});
    });

    // Validar dados
    $("#formCreateBillsToPay").validate({
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
            $('#formCreateBillsToPay [type="submit"]').attr('disabled', true);

            $("#description").val($("#descriptionDiv .ql-editor").html());

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'POST',
                url: $('#formCreateBillsToPay').attr('action'),
                data: $('#formCreateBillsToPay').serialize(),
                success: response => {
                    console.log(response);

                    if (!response.success) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Atenção',
                            html: '<ol><li>' + response.message + '</li></ol>'
                        });
                    } else {
                        Swal.fire({
                            title: 'Concluído',
                            html: `<h4>${response.message}</h4>`,
                            icon: 'success',
                            showCancelButton: true,
                            confirmButtonColor: '#2196f3',
                            cancelButtonColor: '#15b67d',
                            confirmButtonText: 'Voltar para a listagem',
                            cancelButtonText: 'Gerar novo pagamento',
                            reverseButtons: true,
                            allowOutsideClick: false
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = $('#back_page').attr('href');
                            } else {
                                window.location.reload()
                            }
                        });
                    }

                }, error: e => {
                    console.log(e);

                    let arrErrors = []

                    $.each(e.responseJSON.errors, function( index, value ) {
                        arrErrors.push(value);
                    });

                    if (!arrErrors.length && e.responseJSON.message !== undefined) {
                        arrErrors.push('Não foi possível identificar o motivo do erro, recarregue a página e tente novamente!');
                    }

                    Swal.fire({
                        icon: 'warning',
                        title: 'Atenção',
                        html: '<ol><li>'+arrErrors.join('</li><li>')+'</li></ol>'
                    });
                },
                complete: function(e) {
                    if (e.status === 403) {
                        Toast.fire({
                            icon: 'error',
                            title: 'Você não tem permissão para fazer essa operação!'
                        });
                    }
                    $('#formCreateBillsToPay [type="submit"]').attr('disabled', false);
                }
            });
        }
    });


    $('#is_parceled').change(function (){
        const check = $(this).is(':checked');

        if (check) {
            $('#add_parcel, #del_parcel, .automatic_parcel_distribution_parent').slideDown(500);
            $('#parcels').show().append(
                createParcel(0)
            ).find('.form-group').slideDown(500).find('[name="value_parcel[]"]').maskMoney({thousands: '.', decimal: ',', allowZero: true});

            recalculeParcels();
        }
        else {
            $('#add_parcel, #del_parcel, #parcels, .automatic_parcel_distribution_parent').slideUp(500);
            $('#automatic_parcel_distribution').prop('checked', true);
            setTimeout(() => { $('#parcels .form-group').remove() }, 550)
        }
    })

    $('#parcels').on('keyup change', '[name="due_day[]"]', function(){
        let days = parseInt($(this).val());
        const el = $(this).closest('.form-group');

        el.find('[name="due_date[]"]').val(sumDaysDateNow(days));
    });

    $('#parcels').on('blur', '[name="due_date[]"]', function(){
        const dataVctoInput = $(this).val();
        if (dataVctoInput === '') return false;

        const diasVcto = calculateDays(getTodayDateEn(false), dataVctoInput);
        const el = $(this).closest('.form-group');

        el.find('[name="due_day[]"]').val(diasVcto);
    });

    $('#add_parcel').click(function(){
        const parcels = $('#parcels .parcel').length;

        if (parcels == 12) {
            Swal.fire({
                icon: 'warning',
                title: 'Atenção',
                html: '<ol><li>É permitido adicionar até 12 vencimentos.</li></ol>'
            });
            return false;
        }

        $('#parcels').show().append(
            createParcel(parcels)
        ).find('.form-group').slideDown(500).find('[name="value_parcel[]"]').maskMoney({thousands: '.', decimal: ',', allowZero: true});

        $('#del_parcel').attr('disabled', false);

        recalculeParcels();
    });

    $('#del_parcel').click(function(){
        const dues = $('#parcels .form-group').length - 1;

        $(`#parcels .form-group:eq(${dues})`).remove();

        if (dues === 1) {
            $('#del_parcel').attr('disabled', true);
        }

        recalculeParcels();

    });

    $('#automatic_parcel_distribution').change(function(){
        const check = $(this).is(':checked');

        if (check) {
            $('#parcels .form-group [name="value_parcel[]"]').attr('disabled', true);
            recalculeParcels();
        } else {
            $('#parcels .form-group [name="value_parcel[]"]').attr('disabled', false);
        }

    });

    const createParcel = due => {
        const disabledValue = $('#automatic_parcel_distribution').is(':checked') ? 'disabled' : '';
        return `<div class="form-group mt-1 parcel display-none">
            <div class="d-flex align-items-center justify-content-between">
                <div class="input-group col-md-12 no-padding">
                    <div class="input-group-prepend stock-Equipment-payment col-md-3 no-padding">
                        <span class="input-group-text col-md-12 no-border-radius "><strong>${(due+1)}º Vencimento</strong></span>
                    </div>
                    <input type="text" class="form-control col-md-2 text-center" name="due_day[]" value="${calculateDays(sumMonthsDateNow(0), sumMonthsDateNow(due))}">
                    <input type="date" class="form-control col-md-4 text-center" name="due_date[]" value="${sumMonthsDateNow(due)}">
                    <div class="input-group-prepend col-md-1 no-padding">
                        <span class="input-group-text pl-3 pr-3 col-md-12"><strong>R$</strong></span>
                    </div>
                    <input type="text" class="form-control col-md-2 no-border-radius text-center" name="value_parcel[]" value="0,00" ${disabledValue}>
                </div>
            </div>
        </div>`
    }

    const recalculeParcels = () => {
        if ($('#automatic_parcel_distribution').is(':checked')) {
            const parcels = $('#parcels .form-group').length;
            const netValue = realToNumber($('#value').val());

            let valueSumParcel = parseFloat(0.00);
            let valueParcel = netValue / parcels;

            for (let count = 0; count < parcels; count++) {

                if((count + 1) === parcels) valueParcel = netValue - valueSumParcel;

                valueSumParcel += parseFloat((netValue / parcels).toFixed(2));
                $(`#parcels .form-group [name="value_parcel[]"]:eq(${count})`).val(numberToReal(valueParcel));
            }
        }
    }
</script>

@include('includes.provider.modal-script')

    @if(in_array('BillsToPayCreatePost', $permissions))    <script src="{{ asset('assets/js/views/provider/form.js') }}" type="application/javascript"></script>    @endif

@stop

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
                    <form action="{{ route(('ajax.bills_to_pay.new-bill-to-pay')) }}" method="POST" enctype="multipart/form-data" id="formCreateBillsToPay">
                        <div class="card">
                            <div class="card-body">
                                <div class="header-card-body">
                                    <h4 class="card-title">Dados do Fornecedor</h4>
                                    <p class="card-description"> Preencha o formulário abaixo com as informações do novo fornecedor </p>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-12 label-animate">
                                        @include('includes.provider.form')
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-12 mt-3">
                                        <h5>Descrição</h5>
                                        <div id="descriptionDiv" class="quill-container"></div>
                                        <textarea type="hidden" class="d-none" name="description" id="description"></textarea>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label for="value">Valor</label>
                                        <input type="text" class="form-control" id="value" name="value" autocomplete="nope" value="{{ old('value') ?? '0,00' }}">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Forma de Pagamento</label>
                                        <select class="form-control" name="form_payment" required></select>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <div class="switch d-flex mt-4">
                                            <input type="checkbox" class="check-style check-xs" name="is_parceled" id="is_parceled">
                                            <label for="is_parceled" class="check-style check-xs"></label>&nbsp;Gerar Parcelamento
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12 d-flex justify-content-between payment-hidden mt-4">
                                        <div class="form-group display-none automatic_parcel_distribution_parent col-md-6">
                                            <div class="switch d-flex mt-1">
                                                <input type="checkbox" class="check-style check-xs" name="automatic_parcel_distribution" id="automatic_parcel_distribution" checked>
                                                <label for="automatic_parcel_distribution" class="check-style check-xs"></label>&nbsp;Distribuir Valores
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <button type="button" class="btn btn-success display-none" id="add_parcel"><i class="fa fa-plus"></i> Parcela</button>
                                        </div>
                                        <div class="form-group">
                                            <button type="button" class="btn btn-danger display-none" id="del_parcel" disabled><i class="fa fa-trash"></i> Parcela</button>
                                        </div>
                                    </div>
                                    <div class="col-md-12 display-none payment-hidden" id="parcels"></div>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body d-flex justify-content-between">
                                <a href="{{ route('bills_to_pay.index') }}" class="btn btn-secondary col-md-3" id="back_page"><i class="fa fa-arrow-left"></i> Cancelar</a>
                                <button type="submit" class="btn btn-success col-md-3"><i class="fa fa-save"></i> Cadastrar</button>
                            </div>
                        </div>
                        {{ csrf_field() }}
                    </form>
                </div>
            </div>
        </div>
    </div>
    @if(in_array('BillsToPayCreatePost', $permissions)) @include('includes.provider.modal-create') @endif
@stop
