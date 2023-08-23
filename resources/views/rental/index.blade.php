@extends('adminlte::page')

@section('title', 'Listagem de Locações')

@section('content_header')
    <h1 class="m-0 text-dark">Listagem de Locações</h1>
@stop

@section('css')
    <link href="{{ asset('vendor/icheck/skins/all.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/material_blue.css">
    <style>
        #tableRentals .badge.badge-lg {
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
        let tableRental;

        $(function () {
            moment.locale('pt-br');
            loadDaterangePickerInput($('input[name="intervalDates"]'), function () {});

            setTabRental();
        });

        const setTabRental = () => {
            const url = window.location.href;
            const splitUrl = url.split('#');
            let tab = 'deliver';

            if (splitUrl.length === 2)
                tab = splitUrl[1];

            $(`#${tab}-tab`).tab('show');
            getTable(tab, false);
        }

        const loadCountsTabRental = () => {
            $('.nav-tabs.tickets-tab-switch').each(function(){
                $(this).find('li a .badge').html('<i class="fa fa-spin fa-spinner" style="margin-right: 0px"></i>');
            })
        }

        const disabledLoadData = () => {
            $('a[data-toggle="tab"], input[name="intervalDates"], select[name="clients"]').prop('disabled', true);
        }

        const enabledLoadData = () => {
            $('a[data-toggle="tab"], input[name="intervalDates"], select[name="clients"]').prop('disabled', false);
        }

        const getCountsTabRentals = () => {
            const start_date = $('input[name="intervalDates"]').data('daterangepicker').startDate.format('YYYY-MM-DD');
            const end_date   = $('input[name="intervalDates"]').data('daterangepicker').endDate.format('YYYY-MM-DD');

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'POST',
                url: "{{ route('ajax.rental.get-qty-type-rentals') }}",
                data: {
                    client: $('[name="clients"]').val(),
                    start_date,
                    end_date
                },
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
                        $(`button[data-rental-id="${rental_id}"]`).trigger('blur');
                    }
                }
            });
        }

        const getTable = (typeRentals, stateSave = true) => {

            loadCountsTabRental();
            disabledLoadData();

            $('[data-toggle="tooltip"]').tooltip('dispose');

            if (typeof tableRental !== 'undefined') {
                tableRental.destroy();

                $("#tableRentals tbody").empty();
            }

            getCountsTabRentals();

            const start_date = $('input[name="intervalDates"]').data('daterangepicker').startDate.format('YYYY-MM-DD');
            const end_date   = $('input[name="intervalDates"]').data('daterangepicker').endDate.format('YYYY-MM-DD');

            tableRental = $("#tableRentals").DataTable({
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
                    url: '{{ route('ajax.rental.fetch') }}',
                    pages: 2,
                    type: 'POST',
                    data: {
                        "_token": $('meta[name="csrf-token"]').attr('content'),
                        type: typeRentals,
                        start_date,
                        end_date,
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
                modal_id = '#modalDeliver';
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
                        } else if (type === 'withdraw') {
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

                    $.each(response.data, function( index, value ) {
                        loadVehicles(value.vehicle_suggestion, `${modal_id} .equipmentsRentalTable tbody tr[id-rental-equipment="${value.id}"] select[name="vechicles[]"]`, false)
                        loadDrivers(value.driver_suggestion, `${modal_id} .equipmentsRentalTable tbody tr[id-rental-equipment="${value.id}"] select[name="drivers[]"]`, false)
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
                        $(`button[data-rental-id="${rental_id}"]`).trigger('blur');
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
            const rental_id = $(this).data('rental-id');
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
                                $(`button[data-rental-id="${rental_id}"]`).trigger('blur');
                            }
                        }
                    });
                }
            })
        });

        $(document).on('click', '.btnDeliver', function(){

            const rental_id = $(this).data('rental-id');

            showModalEquipment('deliver', rental_id);
        });

        $(document).on('click', '.btnWithdraw', function(){

            const rental_id = $(this).data('rental-id');

            showModalEquipment('withdraw', rental_id);
        });

        $(document).on('ifChanged', '#modalDeliver .equipment, #modalWithdraw .equipment', function() {

            const check = !$(this).is(':checked');

            $(this).closest('tr').find('.flatpickr-input, select').attr('readonly', check);
            $(this).closest('tr').find('.input-button-calendar a').attr('disabled', check);

            $(this).closest('tr').toggleClass('noSelected selected');
        });

        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            getTable(e.target.id.replace('-tab',''), false);
        });

        $('[name="intervalDates"], [name="clients"]').change(function(){
            getTable($('[data-toggle="tab"].active').attr('id').replace('-tab',''), false);
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
                url: "{{ route('ajax.vehicle.get-vehicle') }}" + `/${vehicle_id}`,
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
                    modal_id = '#modalDeliver';
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

                        // if (response.rental_updated) {
                            $(modal_id).modal('hide');
                        // } else {
                        //     showModalEquipment(name_operation, $('[name="rental_id[]"]').val());
                        // }

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
    </script>

    @include('includes.driver.modal-script')
    @include('includes.vehicle.modal-script')
@stop

@section('content')
    <div class="row profile-page">
        <div class="col-md-12 grid-margin">
            @if(session('success'))
                <div class="alert alert-animate alert-success"><i class="fa fa-check-circle"></i> {{session('success')}}</div>
            @endif
            @if(session('warning'))
                <div class="alert alert-animate alert-danger mt-2">{{session('warning')}}</div>
            @endif
            <div class="card">
                <div class="card-body">
                    <div class="header-card-body justify-content-between flex-wrap">
                        <h4 class="card-title no-border">Locações Realizadas</h4>
                        @if(in_array('RentalCreatePost', $permissions))
                        <a href="{{ route('rental.create') }}" class="mb-3 btn btn-primary col-md-3 btn-rounded btn-fw"><i class="fas fa-plus"></i> Nova Locação</a>
                        @endif
                    </div>
                    <div class="row">
                        <div class="col-md-9 form-group">
                            <label>Cliente</label>
                            <select class="form-control" name="clients">
                                <option value="0">Todos</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}">{{ $client->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Data de Criação</label>
                            <input type="text" name="intervalDates" class="form-control" value="{{ $settings['intervalDates']['start'] . ' - ' . $settings['intervalDates']['finish'] }}" />
                        </div>
                    </div>
                    <div class="nav-scroller mt-3">
                        <ul class="nav nav-tabs tickets-tab-switch" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="deliver-tab" data-toggle="tab" href="#deliver" role="tab" aria-controls="deliver" aria-selected="true">Para Entregar<div class="badge">13</div></a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="withdraw-tab" data-toggle="tab" href="#withdraw" role="tab" aria-controls="withdraw" aria-selected="false">Para Retirar<div class="badge">50 </div></a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="finished-tab" data-toggle="tab" href="#finished" role="tab" aria-controls="finished" aria-selected="false">Finalizados<div class="badge">29 </div>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="tab-content tab-content-basic">
                        <div class="tab-pane fade show active" id="deliver" role="tabpanel" aria-labelledby="deliver">

                        </div>
                        <div class="tab-pane fade" id="withdraw" role="tabpanel" aria-labelledby="withdraw">

                        </div>
                        <div class="tab-pane fade" id="finished" role="tabpanel" aria-labelledby="finished">

                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-12">
                            <table id="tableRentals" class="table table-bordered mt-2">
                                <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Cliente/Endereço</th>
                                    <th>Criado Em</th>
                                    <th>Ação</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                                <tfoot>
                                <tr>
                                    <th>Código</th>
                                    <th>Cliente/Endereço</th>
                                    <th>Criado Em</th>
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
    <div class="modal fade" id="modalDeliver" tabindex="-1" role="dialog" aria-labelledby="modalDeliver" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form action="{{ route('ajax.rental.delivery_equipment') }}" method="POST" id="formUpdateDeliver">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirmar entrega do equipamento</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <table class="table col-md-12 equipmentsRentalTable">
                                <thead>
                                    <th style="width: 5%">Entregar</th>
                                    <th style="width: 30%">Equipamento</th>
                                    <th style="width: 20%">Data da Entrega</th>
                                    <th style="width: 20%">Veículo</th>
                                    <th style="width: 20%">Motorista</th>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        <div class="row mt-4 display-none">
                            <div class="col-md-12">
                                <h4 class="text-center">Data da entrega está divergente da data prevista de entrega, deseja realizar alteração na forma de pagamento da locação?</h4>
                            </div>
                            <div class="col-md-12 d-flex flex-wrap justify-content-evenly">
                                <button type="button" class="col-md-2 btn btn-primary">SIM</button>
                                <button type="button" class="col-md-2 btn btn-warning">NÃO</button>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-around">
                        <button type="button" class="btn btn-secondary col-md-3" data-dismiss="modal"><i class="fa fa-times"></i> Cancelar</button>
                        <button type="submit" class="btn btn-success col-md-3"><i class="fa fa-check"></i> Confirmar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modalWithdraw" tabindex="-1" role="dialog" aria-labelledby="modalWithdraw" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form action="{{ route('ajax.rental.withdrawal_equipment') }}" method="POST" id="formUpdateWithdraw">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirmar retirada do equipamento</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <table class="table col-md-12 equipmentsRentalTable">
                                <thead>
                                <th style="width: 5%">Retirar</th>
                                <th style="width: 30%">Equipamento</th>
                                <th style="width: 20%">Data da Retirada</th>
                                <th style="width: 20%">Veículo</th>
                                <th style="width: 20%">Motorista</th>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        <div class="row mt-4 display-none">
                            <div class="col-md-12">
                                <h4 class="text-center">Data da entrega está divergente da data prevista de retirada, deseja realizar alteração na forma de pagamento da locação?</h4>
                            </div>
                            <div class="col-md-12 d-flex flex-wrap justify-content-evenly">
                                <button type="button" class="col-md-2 btn btn-primary">SIM</button>
                                <button type="button" class="col-md-2 btn btn-warning">NÃO</button>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-around">
                        <button type="button" class="btn btn-secondary col-md-3" data-dismiss="modal"><i class="fa fa-times"></i> Cancelar</button>
                        <button type="submit" class="btn btn-success col-md-3"><i class="fa fa-check"></i> Confirmar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
