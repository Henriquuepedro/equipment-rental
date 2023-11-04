@extends('adminlte::page')

@section('title', 'Fluxo de Caixa')

@section('content_header')
    <h1 class="m-0 text-dark">Fluxo de Caixa</h1>
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
        [name^="date_filter"] {
            border-bottom-right-radius: 0 !important;
            border-top-right-radius: 0 !important;;
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr" type="application/javascript"></script>
    <script src="https://npmcdn.com/flatpickr@4.6.6/dist/l10n/pt.js" type="application/javascript"></script>
    <script>
        let date_actual_received = '';
        let date_actual_paid = '';
        let tableReceived, tablePaid;

        $(function(){
            $('[name="date_filter"]').inputmask();
            $('.flatpickr').flatpickr({
                dateFormat: "d/m/Y",
                wrap: true,
                clickOpens: false,
                allowInput: true,
                locale: "pt"
            });

            loadCashFlow();
        });

        $('[name="date_filter"]').on('change', function(){
            loadCashFlow();
        })

        const loadCashFlow = async () => {
            const date = transformDateForEn($('[name="date_filter"]').val());

            if (date === false) {
                $('#received').text('R$ ' + numberToReal(0));
                $('#paid').text('R$ ' + numberToReal(0));
                $('#liquid').text('R$ ' + numberToReal(0));
                return;
            }

            $('#received').html('<i class="fa fa-spinner fa-spin"></i>');
            $('#paid').html('<i class="fa fa-spinner fa-spin"></i>');
            $('#liquid').html('<i class="fa fa-spinner fa-spin"></i>');

            date_actual_paid = date_actual_received = '';
            $('#accordion .collapse').collapse('hide');

            const received = await $.get(`{{ route('ajax.bills_to_receive.get-bills-for-date') }}/${date}`);
            const paid = await $.get(`{{ route('ajax.bills_to_pay.get-bills-for-date') }}/${date}`);

            const total_received = received.total;
            const total_paid     = paid.total;
            const total_liquid   = total_received - total_paid;
            const card_liquid    = $('#card-liquid');

            $('#received').text('R$ ' + numberToReal(total_received));
            $('#paid').text('R$ ' + numberToReal(total_paid));
            $('#liquid').text('R$ ' + numberToReal(total_liquid));

            card_liquid
                .removeClass('border-primary')
                .removeClass('border-success')
                .removeClass('border-danger')
                .find('a i')
                .removeClass('text-primary')
                .removeClass('text-success')
                .removeClass('text-danger');

            if (total_liquid < 0) {
                card_liquid
                    .addClass('border-danger')
                    .find('a i')
                    .addClass('text-danger');
            } else if (total_liquid > 0) {
                card_liquid
                    .addClass('border-success')
                    .find('a i')
                    .addClass('text-success');
            } else {
                card_liquid
                    .addClass('border-primary')
                    .find('a i')
                    .addClass('text-primary');
            }
        }

        $('#collapseReceived').on('show.bs.collapse', function(){
            const date_filter = $('[name="date_filter"]').val();
            if (date_actual_received === date_filter) {
                return;
            }

            if (typeof tableReceived !== 'undefined') {
                tableReceived.destroy();
            }

            $("#tableReceived tbody").empty();
            tableReceived = getTable($('#tableReceived'), date_filter, '{{ route('ajax.bills_to_receive.fetchBillForDate') }}');

            date_actual_received = date_filter;
        });

        $('#collapsePaid').on('show.bs.collapse', function(){
            const date_filter = $('[name="date_filter"]').val();
            if (date_actual_paid === date_filter) {
                return;
            }

            if (typeof tablePaid !== 'undefined') {
                tablePaid.destroy();
            }

            $("#tablePaid tbody").empty();
            tablePaid = getTable($('#tablePaid'), date_filter, '{{ route('ajax.bills_to_pay.fetchBillForDate') }}');

            date_actual_paid = date_filter;
        });

        const getTable = (elTable, date_filter, url) => {
            return elTable.DataTable({
                "processing": true,
                "autoWidth": false,
                "serverSide": true,
                "sortable": true,
                "searching": true,
                "serverMethod": "post",
                "order": [[ 0, 'desc' ]],
                "ajax": {
                    url,
                    pages: 2,
                    type: 'POST',
                    data: { "_token": $('meta[name="csrf-token"]').attr('content'), date_filter },
                    error: function(jqXHR, ajaxOptions, thrownError) {
                        console.log(jqXHR, ajaxOptions, thrownError);
                    }
                },
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Portuguese-Brasil.json"
                }
            });
        }
    </script>
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
            <div class="card" id="contentListBillToReceive">
                <div class="card-body">
                    <div class="header-card-body justify-content-between flex-wrap">
                        <h4 class="card-title no-border">Fluxo de Caixa</h4>
                    </div>
                    <div class="row">
                        <div class="form-group flatpickr col-md-3 d-flex">
                            <label class="label-date-btns">Data</label>
                            <input type="tel" name="date_filter" class="form-control col-md-8" value="{{ date('d/m/Y') }}" data-inputmask="'alias': 'datetime'" data-inputmask-inputformat="dd/mm/yyyy" im-insert="false" data-input>
                            <div class="input-button-calendar col-md-4 no-padding">
                                <a class="input-button pull-left btn-primary" title="toggle" data-toggle>
                                    <i class="fa fa-calendar text-white"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="accordion col-md-12" id="accordion" role="tablist">
                            <div class="card border-success">
                                <div class="card-header" role="tab" id="headingReceived">
                                    <h6 class="mb-0">
                                        <a data-toggle="collapse" href="#collapseReceived" aria-expanded="false" aria-controls="collapseReceived">
                                            <i class="fa fa-plus text-success"></i> Entradas <span style="padding-left: 2.25em" id="received">R$ 0,00</span>
                                        </a>
                                    </h6>
                                </div>
                                <div id="collapseReceived" class="collapse" role="tabpanel" aria-labelledby="headingReceived" data-parent="#accordion">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <table id="tableReceived" class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>Código</th>
                                                            <th>Cliente/Endereço</th>
                                                            <th>Valor</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody></tbody>
                                                    <tfoot>
                                                        <tr>
                                                            <th>Código</th>
                                                            <th>Cliente/Endereço</th>
                                                            <th>Valor</th>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card border-danger">
                                <div class="card-header" role="tab" id="headingPaid">
                                    <h6 class="mb-0">
                                        <a class="collapsed" data-toggle="collapse" href="#collapsePaid" aria-expanded="false" aria-controls="collapsePaid">
                                            <i class="fa fa-minus text-danger"></i> Saídas <span style="padding-left: 3.2em" id="paid">R$ 0,00</span>
                                        </a>
                                    </h6>
                                </div>
                                <div id="collapsePaid" class="collapse" role="tabpanel" aria-labelledby="headingPaid" data-parent="#accordion">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <table id="tablePaid" class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>Código</th>
                                                            <th>Fornecedor</th>
                                                            <th>Valor</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody></tbody>
                                                    <tfoot>
                                                        <tr>
                                                            <th>Código</th>
                                                            <th>Fornecedor</th>
                                                            <th>Valor</th>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card border-primary" id="card-liquid">
                                <div class="card-header" role="tab" id="headingEqual">
                                    <h6 class="mb-0">
                                        <a class="collapsed" data-toggle="collapse" aria-expanded="false" aria-controls="collapseEqual" style="cursor: default">
                                            <i class="fa fa-equals text-primary"></i> Resultado <span style="padding-left: 1.75em" id="liquid">R$ 0,00</span>
                                        </a>
                                    </h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
