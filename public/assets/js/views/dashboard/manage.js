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

        step_size = Math.ceil(max_registers/10);

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

        step_size = Math.ceil(max_registers/10);

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

        step_size = Math.ceil(max_registers/10);

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
        let step_size = Math.ceil(max_registers/10);

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
            response.receive ?? 0,
            response.pay ?? 0
        ];
        let max_registers = Math.max.apply(null, data);
        let step_size = Math.ceil(max_registers/10);

        let lineData = {
            labels: ["Receber atrasado", "Pagar atrasado"],
            datasets: [{
                data,
                backgroundColor: [ChartColor[1], ChartColor[2]],
                borderColor: [ChartColor[1], ChartColor[2]],
                borderWidth: 1,
                label: "Pagamentos atrasadas"
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
                            let complement_payment = payments <= 1 ? 'pagamento' : 'pagamento';

                            return `${payments} ${complement_payment}`;
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
                        text: 'Quantidade de pagamentos',
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
        let billingOpenLateChartCanvas = $("#billingOpenLateChart").get(0).getContext("2d");
        new Chart(billingOpenLateChartCanvas, {
            type: 'bar',
            data: lineData,
            options: lineOptions
        });
    });
}
