var charts_started = false;
$(function () {
    /* ChartJS */

    'use strict';
});

const initCharts = () => {
    if (charts_started) {
        return;
    }

    charts_started = true;

    newClientsForMonth();
    rentalsForMonth();
    billsForMonth();
    clientsTopRentals();
    rentalsLate();
    billingOpenLate();
}

const newClientsForMonth = () => {
    $.getJSON($('#route_new_clients_for_month').val(), function(response) {
        let labels = [];
        let data = [];
        let max_registers = 0;
        let step_size = 0;

        for (const property in response) {
            labels.push(property);
            data.push(response[property]);

            if (response[property] > max_registers) {
                max_registers = response[property];
            }
        }

        step_size = getStepSizeChart(max_registers);

        let lineData = {
            labels,
            datasets: [{
                data,
                backgroundColor: ChartColor[0],
                borderColor: ChartColor[0],
                borderWidth: 3,
                fill: 'false',
                label: "Clientes"
            }]
        };
        let lineOptions = {
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Mês',
                        font: {
                            weight: 'bold'
                        }
                    }
                },
                y: {
                    title: {
                        display: true,
                        text: 'Número de clientes',
                        font: {
                            weight: 'bold'
                        }
                    },
                    ticks: {
                        display: true,
                        autoSkip: false,
                        maxRotation: 0,
                        stepSize: step_size,
                        min: 0,
                        max: max_registers
                    }
                }
            },
        }
        let newClientsChartCanvas = $("#newClientsChart").get(0).getContext("2d");
        new Chart(newClientsChartCanvas, {
            type: 'line',
            data: lineData,
            options: lineOptions
        });
    });
}

const rentalsForMonth = () => {
    $.getJSON($('#route_rentals_for_month').val(), function(response) {
        let labels = [];
        let data = [];
        let max_registers = 0;
        let step_size = 0;

        for (const property in response) {
            labels.push(property);
            data.push(response[property]);

            if (response[property] > max_registers) {
                max_registers = response[property];
            }
        }

        step_size = getStepSizeChart(max_registers);

        let rentalsDoneChartCanvas = $("#rentalsDoneChart").get(0).getContext("2d");
        let gradientStrokeFill_1 = rentalsDoneChartCanvas.createLinearGradient(1, 2, 1, 280);
        gradientStrokeFill_1.addColorStop(0, "rgba(20, 88, 232, 0.37)");
        gradientStrokeFill_1.addColorStop(1, "rgba(255,255,255,0.4)")
        let lineData = {
            labels,
            datasets: [{
                data,
                backgroundColor: gradientStrokeFill_1,
                borderColor: ChartColor[0],
                borderWidth: 3,
                fill: true,
                label: "Locações"
            }]
        };
        let lineOptions = {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                filler: {
                    propagate: false
                },
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Mês',
                        font: {
                            weight: 'bold'
                        }
                    }
                },
                y: {
                    title: {
                        display: true,
                        text: 'Locações por mês',
                        font: {
                            weight: 'bold'
                        }
                    },
                    ticks: {
                        display: true,
                        autoSkip: false,
                        maxRotation: 0,
                        stepSize: step_size,
                        min: 0,
                        max: max_registers
                    }
                }
            }
        }

        new Chart(rentalsDoneChartCanvas, {
            type: 'line',
            data: lineData,
            options: lineOptions
        });
    });
}

const billsForMonth = () => {
    $.getJSON($('#route_bills_for_month').val(), function(response) {
        let labels = [];
        let data = [];
        let max_registers = 0;
        let step_size = 0;

        for (const property in response) {
            labels.push(property);
            data.push(response[property]);

            if (response[property] > max_registers) {
                max_registers = response[property];
            }
        }

        step_size = getStepSizeChart(max_registers);

        var billingChartCanvas = $("#billingChart").get(0).getContext("2d");
        new Chart(billingChartCanvas, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Faturamento',
                    data,
                    backgroundColor: ChartColor[0],
                    borderColor: ChartColor[0],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: tooltipItems => {
                                return `${tooltipItems.dataset.label}: ${numberToReal(tooltipItems.raw, 'R$ ')}`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Mês',
                            font: {
                                weight: 'bold'
                            }
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Faturamento por mês',
                            font: {
                                weight: 'bold'
                            }
                        },
                        ticks: {
                            display: true,
                            autoSkip: false,
                            maxRotation: 0,
                            stepSize: step_size,
                            min: 0,
                            max: max_registers,
                            callback: function(val, index) {
                                // Hide every 2nd tick label
                                return numberToReal(val, 'R$ ');
                            }
                        }
                    }
                }
            }
        });
    });
}

const clientsTopRentals = () => {
    $.getJSON($('#route_clients_top_rentals').val(), function(response) {
        $('#top_clients_rental').empty();
        $(response).each(function (key, value) {
            $('#top_clients_rental').append(
                `<li>
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex">
                        <div class="ml-3">
                            <h6 class="mb-0"><a href="${$('#route_update_client').val()}/${value.client_id}">${value.name}</a></h6>
                            <small class="text-muted">${value.email ?? '&nbsp;'}</small>
                        </div>
                    </div>
                    <div>
                        <small class="d-block mb-0">${value.total}</small>
                    </div>
                </div>
            </li>`);
        });
    });
}

const rentalsLate = () => {
    $.getJSON($('#route_rentals_late_by_type').val(), function(response) {
        let data = [
            response.to_delivery ?? 0,
            response.to_withdraw ?? 0,
            response.no_date_to_withdraw ?? 0
        ];
        let max_registers = Math.max.apply(null, data);
        let step_size = getStepSizeChart(max_registers);

        let lineData = {
            labels: ["Para entregar atrasado", "Para retirar atrasado", "Sem data de retirada"],
            datasets: [{
                data,
                backgroundColor: [ChartColor[0], ChartColor[1], ChartColor[3]],
                borderColor: [ChartColor[0], ChartColor[1], ChartColor[3]],
                borderWidth: 1,
                label: "Locações atrasadas"
            }]
        };
        let lineOptions = {
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: tooltipItems => {
                            const rentals = tooltipItems.raw;
                            let complement_rental = rentals <= 1 ? 'locação' : 'locações';

                            return `${rentals} ${complement_rental}`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    title: {
                        display: false,
                    }
                },
                y: {
                    title: {
                        display: true,
                        text: 'Quantidade de locações',
                        font: {
                            weight: 'bold'
                        }
                    },
                    ticks: {
                        display: true,
                        autoSkip: false,
                        maxRotation: 0,
                        stepSize: step_size,
                        min: 0,
                        max: max_registers
                    }
                }
            },
        }
        let rentalsLateChartCanvas = $("#rentalsLateChart").get(0).getContext("2d");
        new Chart(rentalsLateChartCanvas, {
            type: 'bar',
            data: lineData,
            options: lineOptions
        });
    });
}

const billingOpenLate = () => {
    $.getJSON($('#route_dashboard_get_billing_open_late').val(), function(response) {
        let data = [
            {...{x: "Receber atrasado"}, ...response.receive},
            {...{x: "Pagar atrasado"}, ...response.pay}
        ];
        let max_registers = Math.max.apply(null, [data[0].total_value, data[1].total_value]);
        let step_size = getStepSizeChart(max_registers);

        let lineData = {
            labels: ["Receber atrasado", "Pagar atrasado"],
            datasets: [{
                data,
                backgroundColor: [ChartColor[1], ChartColor[2]],
                borderColor: [ChartColor[1], ChartColor[2]],
                borderWidth: 1,
                label: "Pagamentos atrasadas",
                parsing: {
                    yAxisKey: 'total_value'
                }
            }]
        };
        let lineOptions = {
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: tooltipItems => {
                            const payments = tooltipItems.raw;
                            let complement_payment = payments.total_count <= 1 ? 'pagamento' : 'pagamentos';

                            return `${numberToReal(payments.total_value, 'R$ ')} de ${payments.total_count} ${complement_payment}`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    title: {
                        display: false,
                    }
                },
                y: {
                    title: {
                        display: true,
                        text: 'Valores de pagamentos',
                        font: {
                            weight: 'bold'
                        }
                    },
                    ticks: {
                        display: true,
                        autoSkip: false,
                        maxRotation: 0,
                        stepSize: step_size,
                        min: 0,
                        max: max_registers,
                        callback: function(val, index) {
                            // Hide every 2nd tick label
                            return numberToReal(val, 'R$ ');
                        }
                    }
                }
            }
        }
        let billingOpenLateChartCanvas = $("#billingOpenLateChart").get(0).getContext("2d");
        new Chart(billingOpenLateChartCanvas, {
            type: 'bar',
                data: lineData,
            options: lineOptions
        });
    });
}

const getStepSizeChart = max_registers => {
    if (max_registers <= 10) {
        return 1;
    }
    if (max_registers <= 20) {
        return 2;
    }
    if (max_registers <= 50) {
        return 5;
    }
    if (max_registers <= 100) {
        return 10;
    }
    if (max_registers <= 200) {
        return 20;
    }
    if (max_registers <= 500) {
        return 50;
    }
    if (max_registers <= 1000) {
        return 100;
    }
    if (max_registers <= 2000) {
        return 200;
    }
    if (max_registers <= 5000) {
        return 500;
    }
    if (max_registers <= 10000) {
        return 1000;
    }
    if (max_registers <= 20000) {
        return 2000;
    }
    if (max_registers <= 50000) {
        return 5000;
    }
    if (max_registers <= 100000) {
        return 10000;
    }

    return Math.ceil(max_registers/10);
}
