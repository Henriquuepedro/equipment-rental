$(function () {
    /* ChartJS */

    'use strict';

    newClientsForMonth();
    rentalsForMonth();
    billsForMonth();
    clientsTopRentals();
});

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
            elements: {
                line: {
                    tension: 0
                },
                point: {
                    radius: 3
                }
            },
            legend: {
                display: false
            },
            legendCallback: chart => {
                var text = [];
                text.push('<div class="chartjs-legend"><ul>');
                for (var i = 0; i < chart.data.datasets.length; i++) {
                    text.push('<li>');
                    text.push('<span style="background-color:' + chart.data.datasets[i].borderColor + '">' + '</span>');
                    text.push(chart.data.datasets[i].label);
                    text.push('</li>');
                }
                text.push('</ul></div>');
                return text.join("");
            },
            scales: {
                xAxes: [{
                    display: true,
                    scaleLabel: {
                        display: true,
                        labelString: 'Mês',
                        fontSize: 12,
                        lineHeight: 2,
                        fontColor: chartFontcolor
                    },
                    gridLines: {
                        display: false,
                        drawBorder: false,
                        color: 'transparent',
                        zeroLineColor: '#eeeeee'
                    }
                }],
                yAxes: [{
                    display: true,
                    scaleLabel: {
                        display: true,
                        labelString: 'Número de clientes',
                        fontSize: 12,
                        lineHeight: 2,
                        fontColor: chartFontcolor
                    },
                    ticks: {
                        fontColor: chartFontcolor,
                        display: true,
                        autoSkip: false,
                        maxRotation: 0,
                        stepSize: step_size,
                        min: 0,
                        max: max_registers
                    },
                    gridLines: {
                        drawBorder: false,
                        color: chartGridLineColor,
                        zeroLineColor: chartGridLineColor
                    }
                }]
            },
        }
        let lineChartCanvas = $("#lineChart").get(0).getContext("2d");
        let lineChart = new Chart(lineChartCanvas, {
            type: 'line',
            data: lineData,
            options: lineOptions
        });
        document.getElementById('line-traffic-legend').innerHTML = lineChart.generateLegend();
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

        var areaChartCanvas = $("#areaChart").get(0).getContext("2d");
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
                }
            },
            scales: {
                xAxes: [{
                    display: true,
                    scaleLabel: {
                        display: true,
                        labelString: 'Mês',
                        fontColor: chartFontcolor,
                        fontSize: 12,
                        lineHeight: 2
                    },
                    ticks: {
                        autoSkip: true,
                        autoSkipPadding: 35,
                        maxRotation: 0,
                        maxTicksLimit: 10,
                        fontColor: chartFontcolor
                    },
                    gridLines: {
                        display: false,
                        drawBorder: false,
                        color: chartGridLineColor,
                        zeroLineColor: chartGridLineColor
                    }
                }],
                yAxes: [{
                    display: true,
                    scaleLabel: {
                        display: true,
                        labelString: 'Locações por mês',
                        fontSize: 12,
                        fontColor: chartFontcolor,
                        lineHeight: 2
                    },
                    ticks: {
                        display: true,
                        autoSkip: false,
                        maxRotation: 0,
                        stepSize: step_size,
                        min: 0,
                        max: max_registers,
                        fontColor: chartFontcolor
                    },
                    gridLines: {
                        drawBorder: false,
                        color: chartGridLineColor,
                        zeroLineColor: chartGridLineColor
                    }
                }]
            },
            legend: {
                display: false
            },
            legendCallback: function (chart) {
                var text = [];
                text.push('<div class="chartjs-legend"><ul>');
                for (var i = 0; i < chart.data.datasets.length; i++) {
                    text.push('<li>');
                    text.push('<span style="background-color:' + chart.data.datasets[i].borderColor + '">' + '</span>');
                    text.push(chart.data.datasets[i].label);
                    text.push('</li>');
                }
                text.push('</ul></div>');
                return text.join("");
            },
            elements: {
                line: {
                    tension: 0
                },
                point: {
                    radius: 3
                }
            }
        }
        let lineChartCanvas = $("#areaChart").get(0).getContext("2d");
        let lineChart = new Chart(lineChartCanvas, {
            type: 'line',
            data: lineData,
            options: lineOptions
        });
        document.getElementById('area-traffic-legend').innerHTML = lineChart.generateLegend();
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
        var barChart = new Chart(barChartCanvas, {
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
                layout: {
                    padding: {
                        left: 0,
                        right: 0,
                        top: 0,
                        bottom: 0
                    }
                },
                scales: {
                    xAxes: [{
                        display: true,
                        scaleLabel: {
                            display: true,
                            labelString: 'Mês',
                            fontColor: chartFontcolor,
                            fontSize: 12,
                            lineHeight: 2
                        },
                        ticks: {
                            fontColor: chartFontcolor,
                            stepSize: 50,
                            min: 0,
                            max: 150,
                            autoSkip: true,
                            autoSkipPadding: 15,
                            maxRotation: 0,
                            maxTicksLimit: 10
                        },
                        gridLines: {
                            display: false,
                            drawBorder: false,
                            color: chartGridLineColor,
                            zeroLineColor: chartGridLineColor
                        }
                    }],
                    yAxes: [{
                        display: true,
                        scaleLabel: {
                            display: true,
                            labelString: 'Faturamento por mês',
                            fontColor: chartFontcolor,
                            fontSize: 12,
                            lineHeight: 2
                        },
                        ticks: {
                            display: true,
                            autoSkip: false,
                            maxRotation: 0,
                            fontColor: chartFontcolor,
                            stepSize: step_size,
                            min: 0,
                            max: max_registers
                        },
                        gridLines: {
                            drawBorder: false,
                            color: chartGridLineColor,
                            zeroLineColor: chartGridLineColor
                        }
                    }]
                },
                legend: {
                    display: false
                },
                legendCallback: function (chart) {
                    var text = [];
                    text.push('<div class="chartjs-legend"><ul>');
                    for (var i = 0; i < chart.data.datasets.length; i++) {
                        text.push('<li>');
                        text.push('<span style="background-color:' + chart.data.datasets[i].backgroundColor + '">' + '</span>');
                        text.push(chart.data.datasets[i].label);
                        text.push('</li>');
                    }
                    text.push('</ul></div>');
                    return text.join("");
                },
                elements: {
                    point: {
                        radius: 0
                    }
                },
                tooltips: {
                    callbacks: {
                        label: function(tooltipItem, data) {
                            return 'R$ ' + numberToReal(tooltipItem.yLabel);
                        }
                    }
                }
            }
        });
        document.getElementById('bar-traffic-legend').innerHTML = barChart.generateLegend();
    });
}

const clientsTopRentals = () => {
    $.getJSON($('#route_clients_top_rentals').val(), function(response) {
        $(response).each(function (key, value) {
            $('#top_clients_rental').append(
                `<li>
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex">
                        <div class="ml-3">
                            <h6 class="mb-0">${value.name}</h6>
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
