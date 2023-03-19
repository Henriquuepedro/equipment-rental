@extends('adminlte::page')

@section('title', 'Listagem de Contas a Pagar')

@section('content_header')
    <h1 class="m-0 text-dark">Listagem de Contas a Pagar</h1>
@stop

@section('css')
    <link href="{{ asset('vendor/icheck/skins/all.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/material_blue.css">
    <style>
        .tickets-tab-switch .nav-item .nav-link.active .badge {
            background: #fff;
            color: #2196f3;
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr" type="application/javascript"></script>
    <script src="https://npmcdn.com/flatpickr@4.6.6/dist/l10n/pt.js" type="application/javascript"></script>
    <script>
        let tableBillsToReceive;

        $(function () {
            loadDaterangePickerInput($('input[name="intervalDates"]'), function () { getTable($('[data-toggle="tab"].active').attr('id').replace('-tab',''), false); });
            setTabBill();
            getOptionsForm('form-of-payment', $('#modalConfirmPayment [name="form_payment"], #modalViewPayment [name="form_payment"]'));
        });

        const setTabBill = () => {
            const url = window.location.href;
            const splitUrl = url.split('#');
            let tab = 'late';

            if (splitUrl.length === 2) {
                tab = splitUrl[1];
            }

            $(`#${tab}-tab`).tab('show');
            getTable(tab, false);
        }

        const loadCountsTabBill = () => {
            $('.nav-tabs.tickets-tab-switch').each(function(){
                $(this).find('li a .badge').html('<i class="fa fa-spin fa-spinner" style="margin-right: 0px"></i>');
            })
        }

        const disabledLoadData = () => {
            $('a[data-toggle="tab"], select[name="providers"]').prop('disabled', true);
        }

        const enabledLoadData = () => {
            $('a[data-toggle="tab"], select[name="providers"]').prop('disabled', false);
        }

        const getCountsTabBills = () => {
            const start_date = $('input[name="intervalDates"]').data('daterangepicker').startDate.format('YYYY-MM-DD');
            const end_date   = $('input[name="intervalDates"]').data('daterangepicker').endDate.format('YYYY-MM-DD');

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'POST',
                data: {
                    provider: $('[name="providers"]').val(),
                    start_date,
                    end_date
                },
                url: "{{ route('ajax.bills_to_pay.get-qty-type-bills') }}",
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
                        $(`button[bill-id="${bill_id}"]`).trigger('blur');
                    }
                }
            });
        }

        const getTable = (typeBills, stateSave = true) => {

            loadCountsTabBill();
            disabledLoadData();

            $('[data-toggle="tooltip"]').tooltip('dispose');

            if (typeof tableBillsToReceive !== 'undefined') {
                tableBillsToReceive.destroy();

                $("#tableBillsToReceive tbody").empty();
            }

            getCountsTabBills();

            const start_date = $('input[name="intervalDates"]').data('daterangepicker').startDate.format('YYYY-MM-DD');
            const end_date   = $('input[name="intervalDates"]').data('daterangepicker').endDate.format('YYYY-MM-DD');

            tableBillsToReceive = $("#tableBillsToReceive").DataTable({
                "responsive": true,
                "processing": true,
                "autoWidth": false,
                "serverSide": true,
                "sortable": true,
                "searching": true,
                "stateSave": stateSave,
                "serverMethod": "post",
                "order": [[ 3, 'asc' ]],
                "ajax": {
                    url: '{{ route('ajax.bills_to_pay.fetch') }}',
                    pages: 2,
                    type: 'POST',
                    data: {
                        "_token": $('meta[name="csrf-token"]').attr('content'),
                        type: typeBills,
                        provider: $('[name="providers"]').val(),
                        start_date,
                        end_date
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

        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            getTable(e.target.id.replace('-tab',''), false);
        });

        $(document).on('click', '.btnViewPayment', function() {
            const bill_code     = $(this).data('bill-code');
            const name_provider = $(this).data('name-provider');
            const date_bill     = $(this).data('date-bill');
            const due_date      = $(this).data('due-date');
            const due_value     = 'R$ ' + $(this).data('due-value');
            const payment_id    = $(this).data('payment-id');
            const payday        = $(this).data('payday');

            $('#modalViewPayment').find('[name="date_payment"]').closest('.form-group').show();
            $('#modalViewPayment').find('[name="form_payment"]').closest('.form-group').show();

            if ($('#paid-tab.active').length) {
                $('#modalViewPayment').find('[name="date_payment"]').val(payday);
                $('#modalViewPayment').find('[name="form_payment"]').val(payment_id);
                $('#modalViewPayment').find('.modal-title').text('Detalhes do Pagamento');
            } else {
                $('#modalViewPayment').find('[name="date_payment"]').closest('.form-group').hide();
                $('#modalViewPayment').find('[name="form_payment"]').closest('.form-group').hide();
                $('#modalViewPayment').find('.modal-title').text('Detalhes do Lançamento');
            }

            $('#modalViewPayment').find('[name="bill_code"]').val(bill_code);
            $('#modalViewPayment').find('[name="provider"]').val(name_provider);
            $('#modalViewPayment').find('[name="date_bill"]').val(date_bill);
            $('#modalViewPayment').find('[name="due_date"]').val(due_date);
            $('#modalViewPayment').find('[name="due_value"]').val(due_value);
            checkLabelAnimate();
            $('#modalViewPayment').modal();
        });

        $(document).on('click', '.btnConfirmPayment', function() {
            const payment_id    = $(this).data('bill-payment-id');
            const bill_code     = $(this).data('bill-code');
            const name_provider = $(this).data('name-provider');
            const date_bill     = $(this).data('date-bill');
            const due_date      = $(this).data('due-date');
            const due_value     = 'R$ ' + $(this).data('due-value');

            $('#modalConfirmPayment').find('[name="bill_code"]').val(bill_code);
            $('#modalConfirmPayment').find('[name="provider"]').val(name_provider);
            $('#modalConfirmPayment').find('[name="date_bill"]').val(date_bill);
            $('#modalConfirmPayment').find('[name="due_date"]').val(due_date);
            $('#modalConfirmPayment').find('[name="due_value"]').val(due_value);
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

        $('[name="providers"]').on('change', function(){
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
                        <h4 class="card-title no-border">Contas a Pagar</h4>
                        @if(in_array('BillsToPayCreatePost', $permissions))
                            <a href="{{ route('bills_to_pay.create') }}" class="mb-3 btn btn-primary col-md-3 btn-rounded btn-fw"><i class="fas fa-plus"></i> Novo Pagamento</a>
                        @endif
                    </div>
                    <div class="row">
                        <div class="col-md-9 form-group">
                            <label>Fornecedor</label>
                            <select class="form-control" name="providers">
                                <option value="0">Todos</option>
                                @foreach($providers as $provider)
                                    <option value="{{ $provider->id }}">{{ $provider->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Data do Vencimento</label>
                            <input type="text" name="intervalDates" class="form-control" value="{{ $settings['intervalDates']['start'] . ' - ' . $settings['intervalDates']['finish'] }}" />
                        </div>
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
                                    <th>#</th>
                                    <th>Fornecedor</th>
                                    <th>Valor</th>
                                    <th>Vencimento</th>
                                    <th>Ação</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                                <tfoot>
                                <tr>
                                    <th>#</th>
                                    <th>Fornecedor</th>
                                    <th>Valor</th>
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
                <form action="{{ route('ajax.bills_to_pay.confirm_payment') }}" method="POST" id="formConfirmPayment">
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
                                <input type="text" class="form-control" name="bill_code" value="" disabled>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Fornecedor</label>
                                <input type="text" class="form-control" name="provider" value="" disabled>
                            </div>
                            <div class="form-group col-md-3">
                                <label>Data da Locação</label>
                                <input type="text" class="form-control" name="date_bill" value="" disabled>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-3">
                                <label>Data de Vencimento</label>
                                <input type="text" class="form-control" name="due_date" value="" disabled>
                            </div>
                            <div class="form-group col-md-3">
                                <label>Valor</label>
                                <input type="text" class="form-control" name="due_value" value="" disabled>
                            </div>
                            <div class="form-group col-md-3">
                                <label>Forma de Pagamento</label>
                                <select class="form-control" name="form_payment" required></select>
                            </div>
                            <div class="form-group col-md-3">
                                <label>Data de Pagamento</label>
                                <input type="date" class="form-control" name="date_payment" value="" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-around">
                        <button type="button" class="btn btn-secondary col-md-3" data-dismiss="modal"><i class="fa fa-times"></i> Cancelar</button>
                        <button type="submit" class="btn btn-success col-md-4"><i class="fa fa-check"></i> Confirmar Pagamento</button>
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
                    <h5 class="modal-title"></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="form-group col-md-3">
                            <label>Locação</label>
                            <input type="text" class="form-control" name="bill_code" value="" disabled>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Fornecedor</label>
                            <input type="text" class="form-control" name="provider" value="" disabled>
                        </div>
                        <div class="form-group col-md-3">
                            <label>Data da Locação</label>
                            <input type="text" class="form-control" name="date_bill" value="" disabled>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-3">
                            <label>Data de Vencimento</label>
                            <input type="text" class="form-control" name="due_date" value="" disabled>
                        </div>
                        <div class="form-group col-md-3">
                            <label>Valor</label>
                            <input type="text" class="form-control" name="due_value" value="" disabled>
                        </div>
                        <div class="form-group col-md-3">
                            <label>Forma de Pagamento</label>
                            <select class="form-control" name="form_payment" disabled></select>
                        </div>
                        <div class="form-group col-md-3">
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
