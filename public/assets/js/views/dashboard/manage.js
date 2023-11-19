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
        let lineChartCanvas = $("#lineChart").get(0).getContext("2d");
        new Chart(lineChartCanvas, {
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

        let areaChartCanvas = $("#areaChart").get(0).getContext("2d");
        let gradientStrokeFill_1 = areaChartCanvas.createLinearGradient(1, 2, 1, 280);
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
        let lineChartCanvas = $("#areaChart").get(0).getContext("2d");
        new Chart(lineChartCanvas, {
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

        var barChartCanvas = $("#barChart").get(0).getContext("2d");
        new Chart(barChartCanvas, {
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
