(function ($) {
    'use strict';
    $(function () {
        loadDoughnutGraph('receive_today');
        loadDoughnutGraph('pay_today');
        loadDoughnutGraph('delivery_today');
        loadDoughnutGraph('withdraw_today');
    });
})(jQuery)

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
                ctx.font = getPixelFontGraph() + ' sans-serif';
                ctx.fillStyle = primaryColor;
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
                ctx.font = getPixelFontGraph() + ' sans-serif';
                ctx.fillStyle = primaryColor;
                ctx.textBaseline = 'middle';
                ctx.textAlign = 'center'

                let complement_fill_text_equipment = total_equipments <= 1 ? 'equipamento' : 'equipamentos';
                let complement_fill_text_rental = total_rentals <= 1 ? 'locação' : 'locações';

                ctx.fillText(`${total_equipments} ${complement_fill_text_equipment}`, xPos, yPos - 12);
                ctx.fillText(`de ${total_rentals} ${complement_fill_text_rental}`, xPos, yPos + 12);
            }

            break;
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
                ctx.font = getPixelFontGraph() + ' sans-serif';
                ctx.fillStyle = primaryColor;
                ctx.textBaseline = 'middle';
                ctx.textAlign = 'center'

                ctx.fillText(numberToReal(total_open_payments, 'R$ '), xPos, yPos - 11);

                ctx.font = getPixelFontGraph() + ' sans-serif';

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
                ctx.font = getPixelFontGraph() + ' sans-serif';
                ctx.fillStyle = primaryColor;
                ctx.textBaseline = 'middle';
                ctx.textAlign = 'center'

                ctx.fillText(numberToReal(total_open_payments, 'R$ '), xPos, yPos - 11);

                ctx.font = getPixelFontGraph() + ' sans-serif';

                let complement_fill_text_payment = '';
                if (total_providers <= 1) {
                    complement_fill_text_payment = 'lançamento';
                } else {
                    complement_fill_text_payment = 'lançamentos';
                }
                ctx.fillText(`de ${total_providers} ${complement_fill_text_payment}`, xPos, yPos + 11);
            }

            break;

        default:
            alert(`Tipo de gráfico (${type}) não configurado.`);
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
            "url": "https://cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json"
        },
        "initComplete": () => {
            id_list_table.closest('.dataTables_wrapper').find('.dt-buttons button.dt-button').removeClass('dt-button');
        },
        dom: 'Bfrtip',
        buttons: [
            {
                className: 'btn btn-primary btn-sm',
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
                className: 'btn btn-secondary btn-sm',
                text: `<i class="fa-solid fa-arrow-up-right-from-square"></i> ${data_action_button_go_list.button_name}`,
                action: () => {
                    window.location.href = data_action_button_go_list.href;
                }
            }
        ]
    });
}

const getPixelFontGraph = () => {
    if (getWidth() < 768) {
        return '25px';
    }
    if (getWidth() < 850) {
        return '9px';
    }
    if (getWidth() < 992) {
        return '10px';
    }
    if (getWidth() >= 1800) {
        return '20px';
    }
    if (getWidth() >= 1700) {
        return '19px';
    }
    if (getWidth() >= 1600) {
        return '18px';
    }
    if (getWidth() >= 1550) {
        return '17px';
    }
    if (getWidth() >= 1500) {
        return '16px';
    }
    if (getWidth() >= 1450) {
        return '15px';
    }
    if (getWidth() >= 1400) {
        return '14px';
    }
    if (getWidth() >= 1350) {
        return '13px';
    }
    if (getWidth() >= 1300) {
        return '12px';
    }
    if (getWidth() >= 1250) {
        return '11px';
    }
    if (getWidth() >= 1200) {
        return '10px';
    }
    if (getWidth() >= 1100) {
        return '9px';
    }
    if (getWidth() >= 1000) {
        return '8px';
    }
    if (getWidth() >= 992) {
        return '7px';
    }

    return ((getWidth() * 20) / 1300).toFixed(2) + 'px'

}
