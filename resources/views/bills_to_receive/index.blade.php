@extends('adminlte::page')

@section('title', 'Listagem de Contas a Receber')

@section('content_header')
    <h1 class="m-0 text-dark">Listagem de Contas a Receber</h1>
@stop

@section('css')
    <link href="{{ asset('vendor/icheck/skins/all.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/material_blue.css">
    <style>
        #tableBillsToReceive .badge.badge-lg {
            padding: 0.2rem 0.3rem;
        }
        .tickets-tab-switch .nav-item .nav-link.active .badge {
            background: #fff;
            color: #2196f3;
        }
        .dropdown-menu .dropdown-item:hover {
            background: rgba(33, 150, 243, 0.35);
        }
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
        .flatpickr-input {
            border-bottom-right-radius: 0 !important;
            border-top-right-radius: 0 !important;;
        }
        .equipmentsRentalTable tr.noSelected {
            background-color: rgba(255,175,0,.1);
        }
        .equipmentsRentalTable tr.selected {
            background-color: rgba(27,255,0,.1);
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr" type="application/javascript"></script>
    <script src="https://npmcdn.com/flatpickr@4.6.6/dist/l10n/pt.js" type="application/javascript"></script>
    <script>
        let tableBillsToReceive;

        $(function () {
            setTabRental();
            getOptionsForm('form-of-payment', $('#modalConfirmPayment [name="form_payment"], #modalViewPayment [name="form_payment"]'));
        });

        const setTabRental = () => {
            const url = window.location.href;
            const splitUrl = url.split('#');
            let tab = 'late';

            if (splitUrl.length === 2) {
                tab = splitUrl[1];
            }

            $(`#${tab}-tab`).tab('show');
            getTable(tab, false);
        }

        const loadCountsTabRental = () => {
            $('.nav-tabs.tickets-tab-switch').each(function(){
                $(this).find('li a .badge').html('<i class="fa fa-spin fa-spinner" style="margin-right: 0px"></i>');
            })
        }

        const disabledLoadData = () => {
            $('a[data-toggle="tab"], select[name="clients"]').prop('disabled', true);
        }

        const enabledLoadData = () => {
            $('a[data-toggle="tab"], select[name="clients"]').prop('disabled', false);
        }

        const getCountsTabRentals = () => {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'POST',
                data: {
                    client: $('[name="clients"]').val()
                },
                url: "{{ route('ajax.bills_to_receive.get-qty-type-rentals') }}",
                dataType: 'json',
                success: response => {

                    $.each(response, function( index, value ) {
                        $(`#${index}-tab .badge`).text(value)
                    });

                }, error: e => {
                    console.log(e);
                },
                complete: function(xhr) {
                    if (xhr.status === 403) {
                        Toast.fire({
                            icon: 'error',
                            title: 'Você não tem permissão para fazer essa operação!'
                        });
                        $(`button[rental-id="${rental_id}"]`).trigger('blur');
                    }
                }
            });
        }

        const getTable = (typeRentals, stateSave = true) => {

            loadCountsTabRental();
            disabledLoadData();

            $('[data-toggle="tooltip"]').tooltip('dispose');

            if (typeof tableBillsToReceive !== 'undefined') {
                tableBillsToReceive.destroy();

                $("#tableBillsToReceive tbody").empty();
            }

            getCountsTabRentals();

            tableBillsToReceive = $("#tableBillsToReceive").DataTable({
                "responsive": true,
                "processing": true,
                "autoWidth": false,
                "serverSide": true,
                "sortable": true,
                "searching": true,
                "stateSave": stateSave,
                "serverMethod": "post",
                "order": [[ 0, 'desc' ]],
                "ajax": {
                    url: '{{ route('ajax.bills_to_receive.fetch') }}',
                    pages: 2,
                    type: 'POST',
                    data: {
                        "_token": $('meta[name="csrf-token"]').attr('content'),
                        type: typeRentals,
                        client: $('[name="clients"]').val()
                    },
                    error: function(jqXHR, ajaxOptions, thrownError) {
                        console.log(jqXHR, ajaxOptions, thrownError);
                    }, complete: () => {
                        enabledLoadData();
                    }
                },
                "initComplete": function( settings, json ) {
                    enabledLoadData();
                },
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Portuguese-Brasil.json"
                }
            });
        }

        const showModalEquipment = (type, rental_id) => {
            let url = '';
            let modal_id = '';

            if (type === 'deliver') {
                url = "{{ route('ajax.rental.get-equipments-to-deliver') }}";
                modal_id = '#modalConfirmPayment';
            } else if (type === 'withdraw') {
                url = "{{ route('ajax.rental.get-equipments-to-withdraw') }}";
                modal_id = '#modalWithdraw';
            }

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'POST',
                url,
                data: { rental_id },
                dataType: 'json',
                success: response => {
                    console.log(response);

                    if (!response.success) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Atenção',
                            html: `<ol><li>${response.data}</li></ol>`
                        });
                        return false;
                    }

                    let equipments = '';
                    let date_operation = '';

                    $.each(response.data, function( index, value ) {

                        if (type === 'deliver') {
                            date_operation = value.expected_delivery_date;
                        } else if (type === 'deliver') {
                            date_operation = value.expected_withdrawal_date;
                        }

                        equipments += `
                        <tr id-rental-equipment="${value.id}" class="noSelected">
                            <td>
                                <div class="form-group">
                                    <input type="checkbox" class="equipment" name="checked[]" value="${index}">
                                    <input type="hidden" name="rental_equipment_id[]" value="${value.id}">
                                    <input type="hidden" name="rental_id[]" value="${value.rental_id}">
                                </div>
                            </td>
                            <td>
                                <div class="form-group">
                                    <input type="text" class="form-control d-flex align-items-center" value="${value.name ?? 'Caçamba '+value.volume+'m³'} - ${value.reference}" disabled>
                                </div>
                            </td>
                            <td>
                                <div class="form-group flatpickr d-flex no-margin">
                                    <input type="tel" class="form-control flatpickr-input" name="date[]" value="${getTodayDateBr(true, false)}" data-inputmask="'alias': 'datetime'" data-inputmask-inputformat="dd/mm/yyyy HH:MM" im-insert="false" data-input readonly>
                                    <div class="input-button-calendar col-md-3 no-padding">
                                        <a class="input-button pull-left btn-primary" title="toggle" data-toggle disabled>
                                            <i class="fa fa-calendar text-white"></i>
                                        </a>
                                        <a class="input-button pull-right btn-primary" title="clear" data-clear disabled>
                                            <i class="fa fa-times text-white"></i>
                                        </a>
                                    </div>
                                </div>
                                <small class="${date_operation === null ? 'd-none' : ''}">Previsão: ${transformDateForBr(date_operation)}</small>
                            </td>
                            <td>
                                <div class="form-group">
                                    <select class="form-control vehicles" name="vechicles[]" vehicle-suggestion="${value.vehicle_suggestion}" readonly></select>
                                </div>
                            </td>
                            <td>
                                <div class="form-group">
                                    <select class="form-control drivers" name="drivers[]" driver-suggestion="${value.driver_suggestion}" readonly></select>
                                </div>
                            </td>
                        </tr>`
                    });

                    $(`${modal_id} .equipmentsRentalTable tbody`).empty().append(equipments);

                    if ($(modal_id).is(':not(:visible)')) {
                        $(modal_id).modal();
                    }

                    console.log(response.data);

                    $.each(response.data, function( index, value ) {
                        loadVehicles(value.vehicle_suggestion, `${modal_id} .equipmentsRentalTable tbody tr[id-rental-equipment="${value.id}"] select[name="vechicles[]"]`)
                        loadDrivers(value.driver_suggestion, `${modal_id} .equipmentsRentalTable tbody tr[id-rental-equipment="${value.id}"] select[name="drivers[]"]`)
                    });

                    $(`${modal_id} [type="checkbox"]`).iCheck({
                        checkboxClass: 'icheckbox_square-blue',
                        radioClass: 'iradio_square-blue',
                        increaseArea: '20%' // optional
                    });

                    $('.flatpickr').flatpickr({
                        enableTime: true,
                        dateFormat: "d/m/Y H:i",
                        time_24hr: true,
                        wrap: true,
                        clickOpens: false,
                        allowInput: true,
                        locale: "pt"
                    });

                    console.log(response);
                }, error: e => {
                    console.log(e);
                },
                complete: function(xhr) {
                    if (xhr.status === 403) {
                        Toast.fire({
                            icon: 'error',
                            title: 'Você não tem permissão para fazer essa operação!'
                        });
                        $(`button[rental-id="${rental_id}"]`).trigger('blur');
                    }
                }
            });

            $(`${modal_id} [type="checkbox"]`).iCheck({
                checkboxClass: 'icheckbox_square-blue',
                radioClass: 'iradio_square-blue',
                increaseArea: '20%' // optional
            });

            $('.flatpickr').flatpickr({
                enableTime: true,
                dateFormat: "d/m/Y H:i",
                time_24hr: true,
                wrap: true,
                clickOpens: false,
                allowInput: true,
                locale: "pt"
            });
        }

        $(document).on('click', '.btnRemoveRental', function (){
            const rental_id = $(this).attr('rental-id');
            const rental_name = $(this).closest('tr').find('td:eq(1)').html();

            Swal.fire({
                title: 'Exclusão de Locação',
                html: "<h4>Você está prestes a excluir definitivamente uma locação</h4> <br><strong>"+rental_name+"</strong><br>Deseja continuar?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#bbb',
                confirmButtonText: 'Sim, excluir',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        type: 'POST',
                        url: "{{ route('ajax.rental.delete') }}",
                        data: { rental_id },
                        dataType: 'json',
                        success: response => {
                            getTable();
                            Toast.fire({
                                icon: response.success ? 'success' : 'error',
                                title: response.message
                            })
                        }, error: e => {
                            console.log(e);
                        },
                        complete: function(xhr) {
                            if (xhr.status === 403) {
                                Toast.fire({
                                    icon: 'error',
                                    title: 'Você não tem permissão para fazer essa operação!'
                                });
                                $(`button[rental-id="${rental_id}"]`).trigger('blur');
                            }
                        }
                    });
                }
            })
        });

        $(document).on('click', '.btnDeliver', function(){

            const rental_id = $(this).attr('rental-id');

            showModalEquipment('deliver', rental_id);
        });

        $(document).on('click', '.btnWithdraw', function(){

            const rental_id = $(this).attr('rental-id');

            showModalEquipment('withdraw', rental_id);
        });

        $(document).on('ifChanged', '#modalConfirmPayment .equipment, #modalWithdraw .equipment', function() {

            const check = !$(this).is(':checked');

            $(this).closest('tr').find('.flatpickr-input, select, .input-button-calendar a').attr('disabled', check);

            $(this).closest('tr').toggleClass('noSelected selected');
        });

        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            getTable(e.target.id.replace('-tab',''), false);
        });

        $('.modal .equipmentsRentalTable tbody').on('change', 'select[name="vechicles[]"]', function (){
            const vehicle_id = $(this).val();
            if (vehicle_id == '0') {
                return false;
            }

            const el = $(this).closest('tr');
            const driver_actual = parseInt(el.find('[name="drivers[]"]').val());

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'GET',
                data: { vehicle_id },
                url: "{{ route('ajax.vehicle.get-vehicle') }}",
                async: true,
                success: response => {
                    console.log(response);
                    if (response.driver_id && el.find('[name="drivers[]"]').val() === '0') {
                        el.find('[name="drivers[]"]').val(response.driver_id)
                    } else if (response.driver_id && driver_actual !== parseInt(response.driver_id)) {
                        Swal.fire({
                            title: 'Alteração de Motorista',
                            html: `O veículo selecionado contém relacionado o motorista <b>${response.driver_name}</b>. <br>Deseja atualizar?`,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#19d895',
                            cancelButtonColor: '#bbb',
                            confirmButtonText: 'Sim, atualizar',
                            cancelButtonText: 'Não atualizar',
                            reverseButtons: true
                        }).then((result) => {
                            if (result.isConfirmed) {
                                el.find('[name="drivers[]"]').val(response.driver_id)
                            }
                        })
                    }
                }
            });
        });

        $("#formUpdateDeliver, #formUpdateWithdraw").validate({
            rules: {
                date: {
                    required: true
                },
                vechicles: {
                    required: true
                },
                drivers: {
                    required: true
                }
            },
            messages: {
                date: {
                    required: 'Informe a data.'
                },
                vechicles: {
                    required: 'Informe o veículo.'
                },
                drivers: {
                    required: 'Informe o motorista.'
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
                let id_form         = $('.modal:visible form').attr('id');
                let getForm         = $('#' + id_form);
                let modal_id        = '';
                let type            = id_form.replace('formUpdate', '');
                let name_operation  = type.toLowerCase();

                if (type=== 'Deliver') {
                    modal_id = '#modalConfirmPayment';
                } else if (type === 'Withdraw') {
                    modal_id = '#modalWithdraw';
                }

                getForm.find('button[type="submit"]').attr('disabled', true);

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    type: 'POST',
                    url: getForm.attr('action'),
                    data: getForm.serialize(),
                    dataType: 'json',
                    success: response => {
                        console.log(response);

                        getForm.find('button[type="submit"]').attr('disabled', false);

                        if (!response.success) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Atenção',
                                html: '<ol><li>' + response.message + '</li></ol>'
                            });
                            return false;
                        }

                        Toast.fire({
                            icon: 'success',
                            title: response.message
                        });

                        if (response.rental_updated) {
                            $(modal_id).modal('hide');
                        } else {
                            showModalEquipment(name_operation, $('[name="rental_id[]"]').val());
                        }

                        loadCountsTabRental();
                        getTable($('[data-toggle="tab"].active').attr('id').replace('-tab',''), false);
                    }, error: e => {
                        console.log(e);
                        getForm.find('button[type="submit"]').attr('disabled', false);
                        let arrErrors = [];

                        $.each(e.responseJSON.errors, function( index, value ) {
                            arrErrors.push(value);
                        });

                        if (!arrErrors.length && e.responseJSON.message !== undefined) {
                            arrErrors.push('Você não tem permissão para fazer essa operação!');
                        }

                        Swal.fire({
                            icon: 'warning',
                            title: 'Atenção',
                            html: '<ol><li>'+arrErrors.join('</li><li>')+'</li></ol>'
                        });
                    }
                });
            }
        });

        $(document).on('click', '.btnViewPayment', function() {
            const rental_code   = $(this).data('rental-code');
            const name_client   = $(this).data('name-client');
            const date_rental   = $(this).data('date-rental');
            const due_date      = $(this).data('due-date');
            const payment_id    = $(this).data('payment-id');
            const payday        = $(this).data('payday');

            $('#modalViewPayment').find('[name="date_payment"]').closest('.form-group').show();
            $('#modalViewPayment').find('[name="form_payment"]').closest('.form-group').show();

            if ($('#paid-tab.active').length) {
                $('#modalViewPayment').find('[name="date_payment"]').val(payday);
                $('#modalViewPayment').find('[name="form_payment"]').val(payment_id);
            } else {
                $('#modalViewPayment').find('[name="date_payment"]').closest('.form-group').hide();
                $('#modalViewPayment').find('[name="form_payment"]').closest('.form-group').hide();
            }

            $('#modalViewPayment').find('[name="rental_code"]').val(rental_code);
            $('#modalViewPayment').find('[name="client"]').val(name_client);
            $('#modalViewPayment').find('[name="date_rental"]').val(date_rental);
            $('#modalViewPayment').find('[name="due_date"]').val(due_date);
            checkLabelAnimate();
            $('#modalViewPayment').modal();
        });

        $(document).on('click', '.btnConfirmPayment', function() {
            const payment_id    = $(this).data('rental-payment-id');
            const rental_code   = $(this).data('rental-code');
            const name_client   = $(this).data('name-client');
            const date_rental   = $(this).data('date-rental');
            const due_date      = $(this).data('due-date');

            $('#modalConfirmPayment').find('[name="rental_code"]').val(rental_code);
            $('#modalConfirmPayment').find('[name="client"]').val(name_client);
            $('#modalConfirmPayment').find('[name="date_rental"]').val(date_rental);
            $('#modalConfirmPayment').find('[name="due_date"]').val(due_date);
            $('#modalConfirmPayment').find('[name="payment_id"]').val(payment_id);
            $('#modalConfirmPayment').find('[name="date_payment"]').val((new Date()).toJSON().slice(0, 10));
            $('#modalConfirmPayment').find('[name="form_payment"]').val("");
            $('#modalConfirmPayment').find('[type="submit"]').attr('disabled', false);
            checkLabelAnimate();
            $('#modalConfirmPayment').modal();
        });

        $('#formConfirmPayment').on('submit', function(e) {
            e.preventDefault();
            const payment_id    = $('[name="payment_id"]', this).val();
            const form_payment  = $('[name="form_payment"]', this).val();
            const date_payment  = $('[name="date_payment"]', this).val()
            const endpoint      = $(this).attr('action');
            const btn           = $(this).find('[type="submit"]');

            btn.attr('disabled', true);

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'POST',
                url: endpoint,
                data: {
                    payment_id,
                    form_payment,
                    date_payment
                },
                dataType: 'json',
                success: response => {
                    console.log(response);

                    if (!response.success) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Atenção',
                            html: '<ol><li>' + response.message + '</li></ol>'
                        });
                        return false;
                    }

                    $('#modalConfirmPayment').modal('hide');

                    Toast.fire({
                        icon: 'success',
                        title: response.message
                    });

                    getTable($('[data-toggle="tab"].active').attr('id').replace('-tab',''), false);
                }, error: e => {
                    console.log(e);
                    let arrErrors = [];

                    $.each(e.responseJSON.errors, function( index, value ) {
                        arrErrors.push(value);
                    });

                    if (!arrErrors.length && e.responseJSON.message !== undefined) {
                        arrErrors.push('Você não tem permissão para fazer essa operação!');
                    }

                    Swal.fire({
                        icon: 'warning',
                        title: 'Atenção',
                        html: '<ol><li>'+arrErrors.join('</li><li>')+'</li></ol>'
                    });
                }, always: () => {
                    btn.attr('disabled', false);
                }
            });

        });

        $('[name="clients"]').on('change', function(){
            getTable($('[data-toggle="tab"].active').attr('id').replace('-tab',''), false);
        });
    </script>

    @include('includes.driver.modal-script')
    @include('includes.vehicle.modal-script')
@stop

@section('content')
    <div class="row profile-page">
        <div class="col-md-12 grid-margin">
            @if(session('success'))
                <div class="alert-animate alert-success"><i class="fa fa-check-circle"></i> {{session('success')}}</div>
            @endif
            @if(session('warning'))
                <div class="alert-animate alert-danger mt-2">{{session('warning')}}</div>
            @endif
            <div class="card">
                <div class="card-body">
                    <div class="header-card-body justify-content-between flex-wrap">
                        <h4 class="card-title no-border">Contas a Receber</h4>
                    </div>
                    <div class="row">
                        <div class="col-md-12 form-group">
                            <label>Cliente</label>
                            <select class="form-control" name="clients">
                                <option value="0">Todos</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}">{{ $client->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        {{--<div class="col-md-3 form-group">
                                <label>Data do Vencimento</label>
                                <input type="text" name="intervalDates" class="form-control" value="{{ $settings['intervalDates']['start'] . ' - ' . $settings['intervalDates']['finish'] }}" />
                            </div>--}}
                    </div>
                    <div class="nav-scroller mt-3">
                        <ul class="nav nav-tabs tickets-tab-switch" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="late-tab" data-toggle="tab" href="#late" role="tab" aria-controls="late" aria-selected="true">Atrasado<div class="badge">13</div></a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="without_pay-tab" data-toggle="tab" href="#without_pay" role="tab" aria-controls="without_pay" aria-selected="false">Não Pago<div class="badge">50 </div></a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="paid-tab" data-toggle="tab" href="#paid" role="tab" aria-controls="paid" aria-selected="false">Pago<div class="badge">29 </div>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="tab-content tab-content-basic">
                        <div class="tab-pane fade show active" id="late" role="tabpanel" aria-labelledby="late">

                        </div>
                        <div class="tab-pane fade" id="without_pay" role="tabpanel" aria-labelledby="without_pay">

                        </div>
                        <div class="tab-pane fade" id="paid" role="tabpanel" aria-labelledby="paid">

                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-12">
                            <table id="tableBillsToReceive" class="table table-bordered mt-2">
                                <thead>
                                <tr>
                                    <th>Locação</th>
                                    <th>Cliente/Endereço</th>
                                    <th>Vencimento</th>
                                    <th>Ação</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                                <tfoot>
                                <tr>
                                    <th>Locação</th>
                                    <th>Cliente/Endereço</th>
                                    <th>Vencimento</th>
                                    <th>Ação</th>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modalConfirmPayment" tabindex="-1" role="dialog" aria-labelledby="modalConfirmPayment" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <form action="{{ route('ajax.bills_to_receive.confirm_payment') }}" method="POST" id="formConfirmPayment">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirmar pagamento</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="form-group col-md-3">
                                <label>Locação</label>
                                <input type="text" class="form-control" name="rental_code" value="" disabled>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Cliente</label>
                                <input type="text" class="form-control" name="client" value="" disabled>
                            </div>
                            <div class="form-group col-md-3">
                                <label>Data da Locação</label>
                                <input type="text" class="form-control" name="date_rental" value="" disabled>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-4">
                                <label>Data de Vencimento</label>
                                <input type="text" class="form-control" name="due_date" value="" disabled>
                            </div>
                            <div class="form-group col-md-4">
                                <label>Forma de Pagamento</label>
                                <select class="form-control" name="form_payment" required></select>
                            </div>
                            <div class="form-group col-md-4">
                                <label>Data de Pagamento</label>
                                <input type="date" class="form-control" name="date_payment" value="" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-around">
                        <button type="button" class="btn btn-secondary col-md-3" data-dismiss="modal"><i class="fa fa-times"></i> Cancelar</button>
                        <button type="submit" class="btn btn-success col-md-3"><i class="fa fa-check"></i> Confirmar Pagamento</button>
                    </div>
                    <input type="hidden" class="form-control" name="payment_id">
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalViewPayment" tabindex="-1" role="dialog" aria-labelledby="modalViewPayment" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Visualizar pagamento</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="form-group col-md-3">
                            <label>Locação</label>
                            <input type="text" class="form-control" name="rental_code" value="" disabled>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Cliente</label>
                            <input type="text" class="form-control" name="client" value="" disabled>
                        </div>
                        <div class="form-group col-md-3">
                            <label>Data da Locação</label>
                            <input type="text" class="form-control" name="date_rental" value="" disabled>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-4">
                            <label>Data de Vencimento</label>
                            <input type="text" class="form-control" name="due_date" value="" disabled>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Forma de Pagamento</label>
                            <select class="form-control" name="form_payment" disabled></select>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Data de Pagamento</label>
                            <input type="text" class="form-control" name="date_payment" value="" disabled>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary col-md-3" data-dismiss="modal"><i class="fa fa-times"></i> Cancelar</button>
                </div>
            </div>
        </div>
    </div>
@stop
