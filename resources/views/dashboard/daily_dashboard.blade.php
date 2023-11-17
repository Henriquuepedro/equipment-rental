@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1 class="m-0 text-dark">Dashboard</h1>
@stop

@section('css')
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
    <style>
        .content-graph .card {
            border: 1px solid #ccc;
        }

        .content-graph .card-body.table {
            display: none;
        }

        .dataTable thead tr th, .dataTable tfoot tr th {
            background: unset;
        }
        .dataTable:not(#tableRentalToWithdrawToday) tbody tr td {
            padding: 0 8px !important;
        }
        .dataTable#tableRentalToWithdrawToday tbody tr td {
            padding: 2px 8px !important;
        }
        .aligner-wrapper .absolute.absolute-center {
            width: 25%;
        }

    </style>
@stop

@section('js')
    <script src="{{ asset('assets/vendors/justgage/raphael-2.1.4.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/justgage/justgage.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js" type="application/javascript"></script>
    <script>

        /**
         * receive_today
         */
        const loadDoughnutGraph = async type => {

            let graph_endpoint = '';
            let table_endpoint = '';
            let id_canvas = '';
            let id_list_table = '';
            let data_action_button_go_list = { custom_data: {} };
            let callbackTooltipLabel = () => {};
            let datasetCenterGraph = () => {};
            let onClickGraph = () => {};
            let response;
            let labels;
            let key_value = '';
            let total_open_payments = 0;
            let total_clients = 0;
            let total_providers = 0;
            let total_equipments = 0;
            let total_rentals = 0;
            let pageLength = 10;

            switch (type) {
                case 'receive_today':
                    graph_endpoint = $('#route_bill_to_receive_today').val() + `/${dateNow()}`;
                    table_endpoint =  $('#route_table_bill_to_receive_today').val();
                    id_canvas = $('#cavasReceiveToday');
                    id_list_table = $('#tableBillToReceiveToday');
                    data_action_button_go_list.button_name = "Contas a receber";
                    data_action_button_go_list.href = $('#route_list_table_bill_to_receive_today').val();
                    data_action_button_go_list.custom_data.date_filter = transformDateForBr(dateNow());
                    data_action_button_go_list.custom_data.only_is_open = 1;
                    data_action_button_go_list.custom_data.show_address = 0;
                    key_value = 'total';
                    pageLength = 9;

                    callbackTooltipLabel = tooltipItems => {
                        const payments = tooltipItems.raw.total_payment_client;
                        let complement = payments <= 1 ? 'lançamento' : 'lançamentos';

                        return numberToReal(tooltipItems.parsed, 'R$ ') + ` de ${payments} ${complement}`;
                    }

                    onClickGraph = item => {
                        data_action_button_go_list.custom_data.client_id = 0;
                        data_action_button_go_list.href = $('#route_list_table_bill_to_receive_today').val();
                        if (item.length) {
                            const client_id = item[0].element['$context'].raw.client_id;
                            data_action_button_go_list.custom_data.client_id = client_id;
                            data_action_button_go_list.href += `/${client_id}`;
                        }

                        loadTableGraph(id_list_table, table_endpoint, data_action_button_go_list, pageLength);
                    }

                    response = await $.getJSON(graph_endpoint);
                    labels = response.map((payment) => {
                        return payment.name;
                    });
                    total_open_payments = response.reduce((total, payment) => total + payment.total, 0);
                    total_clients = response.reduce((total, payment) => total + payment.total_payment_client, 0);

                    datasetCenterGraph = (ctx, xPos, yPos) =>  {
                        ctx.font = '20px sans-serif';
                        ctx.fillStyle = '#fff';
                        ctx.textBaseline = 'middle';
                        ctx.textAlign = 'center'

                        ctx.fillText(numberToReal(total_open_payments, 'R$ '), xPos, yPos - 11);

                        ctx.font = '12px sans-serif';

                        let complement_fill_text_payment = '';
                        if (total_clients <= 1) {
                            complement_fill_text_payment = 'lançamento';
                        } else {
                            complement_fill_text_payment = 'lançamentos';
                        }
                        ctx.fillText(`de ${total_clients} ${complement_fill_text_payment}`, xPos, yPos + 11);
                    }

                    break;
                case 'pay_today':
                    graph_endpoint = $('#route_bill_to_pay_today').val() + `/${dateNow()}`;
                    table_endpoint =  $('#route_table_bill_to_pay_today').val();
                    id_canvas = $('#cavasPayToday');
                    id_list_table = $('#tableBillToPayToday');
                    data_action_button_go_list.button_name = "Contas a receber";
                    data_action_button_go_list.href = $('#route_list_table_bill_to_pay_today').val();
                    data_action_button_go_list.custom_data.date_filter = transformDateForBr(dateNow());
                    data_action_button_go_list.custom_data.only_is_open = 1;
                    data_action_button_go_list.custom_data.show_address = 0;
                    key_value = 'total';
                    pageLength = 9;

                    callbackTooltipLabel = tooltipItems => {
                        const payments = tooltipItems.raw.total_payment_provider;
                        let complement = payments <= 1 ? 'lançamento' : 'lançamentos';
                        return numberToReal(tooltipItems.parsed, 'R$ ') + ` de ${payments} ${complement}`;
                    }

                    onClickGraph = item => {
                        data_action_button_go_list.custom_data.provider_id = 0;
                        data_action_button_go_list.href = $('#route_list_table_bill_to_pay_today').val();
                        if (item.length) {
                            const provider_id = item[0].element['$context'].raw.provider_id;
                            data_action_button_go_list.custom_data.provider_id = provider_id;
                            data_action_button_go_list.href += `/${provider_id}`;
                        }

                        loadTableGraph(id_list_table, table_endpoint, data_action_button_go_list, pageLength);
                    }

                    response = await $.getJSON(graph_endpoint);
                    labels = response.map((payment) => {
                        return payment.name;
                    });
                    total_open_payments = response.reduce((total, payment) => total + payment.total, 0);
                    total_providers = response.reduce((total, payment) => total + payment.total_payment_provider, 0);

                    datasetCenterGraph = (ctx, xPos, yPos) =>  {
                        ctx.font = '20px sans-serif';
                        ctx.fillStyle = '#fff';
                        ctx.textBaseline = 'middle';
                        ctx.textAlign = 'center'

                        ctx.fillText(numberToReal(total_open_payments, 'R$ '), xPos, yPos - 11);

                        ctx.font = '12px sans-serif';

                        let complement_fill_text_payment = '';
                        if (total_providers <= 1) {
                            complement_fill_text_payment = 'lançamento';
                        } else {
                            complement_fill_text_payment = 'lançamentos';
                        }
                        ctx.fillText(`de ${total_providers} ${complement_fill_text_payment}`, xPos, yPos + 11);
                    }

                    break;
                case 'delivery_today':
                    graph_endpoint = $('#route_rental_to_delivery_today').val();
                    table_endpoint =  $('#route_table_rental_to_delivery_today').val();
                    id_canvas = $('#cavasDeliveryToday');
                    id_list_table = $('#tableRentalToDeliveryToday');
                    data_action_button_go_list.button_name = "Locação";
                    data_action_button_go_list.href = $('#route_list_table_rental_to_delivery_today').val();
                    data_action_button_go_list.custom_data.start_date = dateNow();
                    data_action_button_go_list.custom_data.end_date = dateNow();
                    data_action_button_go_list.custom_data.type_to_today = 1;
                    data_action_button_go_list.custom_data.response_simplified = 1;
                    data_action_button_go_list.custom_data.type = 'deliver';
                    key_value = 'total';
                    pageLength = 8;

                    callbackTooltipLabel = tooltipItems => {
                        const equipments = tooltipItems.raw.total;
                        const rentals = tooltipItems.raw.rentals;
                        let complement_equipment = equipments <= 1 ? 'equipamento' : 'equipamentos';
                        let complement_rental = rentals <= 1 ? 'locação' : 'locações';

                        return `${equipments} ${complement_equipment} de ${rentals} ${complement_rental}`;
                    }

                    onClickGraph = item => {
                        data_action_button_go_list.custom_data.client = 0;
                        data_action_button_go_list.href = $('#route_list_table_rental_to_delivery_today').val();
                        if (item.length) {
                            const client_id = item[0].element['$context'].raw.client_id;
                            data_action_button_go_list.custom_data.client = client_id;
                            data_action_button_go_list.href += `/${client_id}`;
                        }
                        data_action_button_go_list.href += '#deliver';

                        loadTableGraph(id_list_table, table_endpoint, data_action_button_go_list, pageLength);
                    }

                    response = await $.getJSON(graph_endpoint);
                    labels = response.map((equipment) => {
                        return equipment.name;
                    });
                    total_equipments = response.reduce((total, equipment) => total + equipment.total, 0);
                    total_rentals = response.reduce((rentals, rental) => rentals + rental.rentals, 0);

                    datasetCenterGraph = (ctx, xPos, yPos) =>  {
                        ctx.font = '17px sans-serif';
                        ctx.fillStyle = '#fff';
                        ctx.textBaseline = 'middle';
                        ctx.textAlign = 'center'

                        let complement_fill_text_equipment = total_equipments <= 1 ? 'equipamento' : 'equipamentos';
                        let complement_fill_text_rental = total_rentals <= 1 ? 'locação' : 'locações';

                        ctx.fillText(`${total_equipments} ${complement_fill_text_equipment}`, xPos, yPos - 12);
                        ctx.fillText(`de ${total_rentals} ${complement_fill_text_rental}`, xPos, yPos + 12);
                    }

                    break;
                case 'withdraw_today':
                    graph_endpoint = $('#route_rental_to_withdraw_today').val();
                    table_endpoint =  $('#route_table_rental_to_withdraw_today').val();
                    id_canvas = $('#cavasWithdrawToday');
                    id_list_table = $('#tableRentalToWithdrawToday');
                    data_action_button_go_list.button_name = "Locação";
                    data_action_button_go_list.href = $('#route_list_table_rental_to_withdraw_today').val();
                    data_action_button_go_list.custom_data.start_date = dateNow();
                    data_action_button_go_list.custom_data.end_date = dateNow();
                    data_action_button_go_list.custom_data.type_to_today = 1;
                    data_action_button_go_list.custom_data.response_simplified = 1;
                    data_action_button_go_list.custom_data.type = 'withdraw';
                    key_value = 'total';
                    pageLength = 8;

                    callbackTooltipLabel = tooltipItems => {
                        const equipments = tooltipItems.raw.total;
                        const rentals = tooltipItems.raw.rentals;
                        let complement_equipment = equipments <= 1 ? 'equipamento' : 'equipamentos';
                        let complement_rental = rentals <= 1 ? 'locação' : 'locações';

                        return `${equipments} ${complement_equipment} de ${rentals} ${complement_rental}`;
                    }

                    onClickGraph = item => {
                        data_action_button_go_list.custom_data.client = 0;
                        data_action_button_go_list.href = $('#route_list_table_rental_to_withdraw_today').val();
                        if (item.length) {
                            const client_id = item[0].element['$context'].raw.client_id;
                            data_action_button_go_list.custom_data.client = client_id;
                            data_action_button_go_list.href += `/${client_id}`;
                        }
                        data_action_button_go_list.href += '#withdraw';

                        loadTableGraph(id_list_table, table_endpoint, data_action_button_go_list, pageLength);
                    }

                    response = await $.getJSON(graph_endpoint);
                    labels = response.map((equipment) => {
                        return equipment.name;
                    });
                    total_equipments = response.reduce((total, equipment) => total + equipment.total, 0);
                    total_rentals = response.reduce((rentals, rental) => rentals + rental.rentals, 0);

                    datasetCenterGraph = (ctx, xPos, yPos) =>  {
                        ctx.font = '17px sans-serif';
                        ctx.fillStyle = '#fff';
                        ctx.textBaseline = 'middle';
                        ctx.textAlign = 'center'

                        let complement_fill_text_equipment = total_equipments <= 1 ? 'equipamento' : 'equipamentos';
                        let complement_fill_text_rental = total_rentals <= 1 ? 'locação' : 'locações';

                        ctx.fillText(`${total_equipments} ${complement_fill_text_equipment}`, xPos, yPos - 12);
                        ctx.fillText(`de ${total_rentals} ${complement_fill_text_rental}`, xPos, yPos + 12);
                    }

                    break;

                default:
                    alert('Tipo de gráfico não configurado.');
                    return false;
            }

            const doughnutChartCanvas = id_canvas.get(0).getContext("2d");
            const doughnutPieData = {
                datasets: [
                    {
                        data: response
                    }
                ],
                labels
            };
            const doughnutPieOptions = {
                cutoutPercentage: 75,
                animationEasing: "easeOutBounce",
                animateRotate: true,
                animateScale: true,
                responsive: true,
                maintainAspectRatio: true,
                showScale: true,
                borderWidth: 1,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: tooltipItems => {
                                return callbackTooltipLabel(tooltipItems);
                            }
                        }
                    }
                },
                parsing: {
                    key: key_value
                },
                onClick: (evt, item) => {
                    if (evt.chart.boxes[0].legendItems.length) {
                        const cards = id_canvas.closest('.content-graph')

                        cards.find('.card-body.graph').fadeOut(500);
                        setTimeout(() => {
                            cards.find('.card-body.table').fadeIn(500);
                        }, 500);

                        onClickGraph(item);
                    }
                },
                onHover: event => {
                    event.native.target.style.cursor = 'pointer';
                }
            };
            const doughnutLabel = {
                id: 'doughnutLabel',
                beforeDatasetsDraw(chart) {
                    const { ctx } = chart;

                    if (chart.getDatasetMeta(0).data.length) {
                        ctx.save();

                        const xPos = chart.getDatasetMeta(0).data[0].x;
                        const yPos = chart.getDatasetMeta(0).data[0].y;

                        datasetCenterGraph(ctx, xPos, yPos);
                    }
                },afterDraw: function(chart) {
                    if (chart.data.datasets[0].data.every(item => item === 0)) {
                        let ctx = chart.ctx;
                        let width = chart.width;
                        let height = chart.height;

                        chart.clear();
                        ctx.save();

                        ctx.font = '20px sans-serif';
                        ctx.fillStyle = '#19d895';
                        ctx.textBaseline = 'middle';
                        ctx.textAlign = 'center'

                        ctx.fillText('Tudo bem! Nada a fazer 🎉', width / 2, height / 2);
                        ctx.restore();
                    }
                }
            }

            new Chart(doughnutChartCanvas, {
                type: 'doughnut',
                data: doughnutPieData,
                options: doughnutPieOptions,
                plugins: [doughnutLabel]
            });
        }

        const loadTableGraph = (id_list_table, url, data_action_button_go_list, pageLength) => {
            const data_default = {
                _token: $('meta[name="csrf-token"]').attr('content')
            };

            const data = {
                ...data_default,
                ...data_action_button_go_list.custom_data
            }

            if ($.fn.dataTable.isDataTable('#' + id_list_table.prop('id'))) {
                id_list_table.DataTable().destroy();
                id_list_table.find('tbody').empty();
            }

            id_list_table.dataTable({
                "responsive": false,
                "processing": true,
                "autoWidth": false,
                "sortable": true,
                "searching": false,
                "pageLength": pageLength,
                "serverSide": true,
                "serverMethod": "post",
                "ajax": {
                    url,
                    pages: 2,
                    type: 'POST',
                    data,
                    error: function(jqXHR, ajaxOptions, thrownError) {
                        console.log(jqXHR, ajaxOptions, thrownError);
                    }
                },
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json"
                },
                "initComplete": () => {
                    id_list_table.closest('.dataTables_wrapper').find('.dt-buttons button.dt-button').removeClass('dt-button');
                },
                dom: 'Bfrtip',
                buttons: [
                    {
                        className: 'btn btn-primary',
                        text: '<i class="fa-solid fa-rotate-left"></i> Retornar ao Gráfico',
                        action: () => {
                            const cards = id_list_table.closest('.content-graph')

                            cards.find('.card-body.table').fadeOut(500);
                            setTimeout(() => {
                                cards.find('.card-body.graph').fadeIn(500);
                            }, 500);
                        }
                    },
                    {
                        className: 'btn btn-secondary',
                        text: `<i class="fa-solid fa-arrow-up-right-from-square"></i> ${data_action_button_go_list.button_name}`,
                        action: () => {
                            window.location.href = data_action_button_go_list.href;
                        }
                    }
                ]
            });
        }

        (function ($) {
            'use strict';
            $(function () {
                loadDoughnutGraph('receive_today');
                loadDoughnutGraph('pay_today');
                loadDoughnutGraph('delivery_today');
                loadDoughnutGraph('withdraw_today');
            });
        })(jQuery)
    </script>
@stop

@section('content')
<div class="row">
    <div class="col-12 grid-margin">
        <div class="card card-statistics">
            <div class="row">



                {{-- Entregar hoje --}}
                <div class="col-md-6 grid-margin stretch-card content-graph">
                    <div class="card">
                        <div class="card-body graph">
                            <h4 class="card-title">Equipamentos Para Entregar Hoje</h4>
                            <div class="row d-flex justify-content-center">
                                <div class="col-md-7 aligner-wrapper">
                                    <canvas class="my-4 my-md-auto" id="cavasDeliveryToday"></canvas>
                                </div>

                            </div>
                        </div>
                        <div class="card-body table">
                            <div class="row mt-2">
                                <div class="col-md-12">
                                    <table id="tableRentalToDeliveryToday" class="table">
                                        <thead>
                                            <tr>
                                                <th>Locação</th>
                                                <th>Cliente/Endereço</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>



                {{-- Retirar hoje --}}
                <div class="col-md-6 grid-margin stretch-card content-graph">
                    <div class="card">
                        <div class="card-body graph">
                            <h4 class="card-title">Equipamentos Para Retirar Hoje</h4>
                            <div class="row d-flex justify-content-center">
                                <div class="col-md-7 aligner-wrapper">
                                    <canvas class="my-4 my-md-auto" id="cavasWithdrawToday"></canvas>
                                </div>

                            </div>
                        </div>
                        <div class="card-body table">
                            <div class="row mt-2">
                                <div class="col-md-12">
                                    <table id="tableRentalToWithdrawToday" class="table">
                                        <thead>
                                        <tr>
                                            <th>Locação</th>
                                            <th>Cliente/Endereço</th>
                                            <th>Equipamentos</th>
                                        </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>



                {{-- Contas a receber hoje --}}
                <div class="col-md-6 grid-margin stretch-card content-graph">
                    <div class="card">
                        <div class="card-body graph">
                            <h4 class="card-title">Contas a receber hoje</h4>
                            <div class="row d-flex justify-content-center">
                                <div class="col-md-7 aligner-wrapper">
                                    <canvas class="my-4 my-md-auto" id="cavasReceiveToday"></canvas>
                                </div>

                            </div>
                        </div>
                        <div class="card-body table">
                            <div class="row mt-2">
                                <div class="col-md-12">
                                    <table id="tableBillToReceiveToday" class="table">
                                        <thead>
                                            <tr>
                                                <th>Locação</th>
                                                <th>Cliente</th>
                                                <th>Valor</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>



                {{-- Contas A pagar Hoje --}}
                <div class="col-md-6 grid-margin stretch-card content-graph">
                    <div class="card">
                        <div class="card-body graph">
                            <h4 class="card-title">Contas A pagar Hoje</h4>
                            <div class="row d-flex justify-content-center">
                                <div class="col-md-7 aligner-wrapper">
                                    <canvas class="my-4 my-md-auto" id="cavasPayToday"></canvas>
                                </div>

                            </div>
                        </div>
                        <div class="card-body table">
                            <div class="row mt-2">
                                <div class="col-md-12">
                                    <table id="tableBillToPayToday" class="table">
                                        <thead>
                                            <tr>
                                                <th>Compra</th>
                                                <th>Fornecedor</th>
                                                <th>Valor</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


            </div>
        </div>
    </div>
</div>

<input type="hidden" id="route_bill_to_receive_today" value="{{ route('ajax.bills_to_receive.getBillsForDateAndClient') }}">
<input type="hidden" id="route_table_bill_to_receive_today" value="{{ route('ajax.bills_to_receive.fetchBillForDate') }}">
<input type="hidden" id="route_list_table_bill_to_receive_today" value="{{ route('bills_to_receive.index', array('filter_start_date' => dateNowInternational(null, DATE_INTERNATIONAL), 'filter_end_date' => dateNowInternational(null, DATE_INTERNATIONAL))) }}">

<input type="hidden" id="route_bill_to_pay_today" value="{{ route('ajax.bills_to_pay.getBillsForDateAndProvider') }}">
<input type="hidden" id="route_table_bill_to_pay_today" value="{{ route('ajax.bills_to_pay.fetchBillForDate') }}">
<input type="hidden" id="route_list_table_bill_to_pay_today" value="{{ route('bills_to_pay.index', array('filter_start_date' => dateNowInternational(null, DATE_INTERNATIONAL), 'filter_end_date' => dateNowInternational(null, DATE_INTERNATIONAL))) }}">

<input type="hidden" id="route_rental_to_delivery_today" value="{{ route('ajax.rental.getRentalsForDateAndClient', array('date' => dateNowInternational(null, DATE_INTERNATIONAL), 'type' => 'deliver')) }}">
<input type="hidden" id="route_table_rental_to_delivery_today" value="{{ route('ajax.rental.fetch') }}">
<input type="hidden" id="route_list_table_rental_to_delivery_today" value="{{ route('rental.index', array('filter_start_date' => dateNowInternational(null, DATE_INTERNATIONAL), 'filter_end_date' => dateNowInternational(null, DATE_INTERNATIONAL), 'date_filter_by' => 'expected_delivery')) }}">

<input type="hidden" id="route_rental_to_withdraw_today" value="{{ route('ajax.rental.getRentalsForDateAndClient', array('date' => dateNowInternational(null, DATE_INTERNATIONAL), 'type' => 'withdraw')) }}">
<input type="hidden" id="route_table_rental_to_withdraw_today" value="{{ route('ajax.rental.fetch') }}">
<input type="hidden" id="route_list_table_rental_to_withdraw_today" value="{{ route('rental.index', array('filter_start_date' => dateNowInternational(null, DATE_INTERNATIONAL), 'filter_end_date' => dateNowInternational(null, DATE_INTERNATIONAL), 'date_filter_by' => 'expected_withdraw')) }}">
@stop
