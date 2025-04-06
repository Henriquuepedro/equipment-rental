let tableBillsToReceive;
let rows_selected = [];

$(function () {
    loadDaterangePickerInput($('#contentListBillToReceive input[name="intervalDates"]'), function () {
        getTable($('#contentListBillToReceive [data-bs-toggle="tab"].active').attr('id').replace('-tab', ''));
    });
    setTabRental();

    tableBillsToReceive.on('click', 'tbody tr', function (e) {
        const tag_name = $(e.target).prop("tagName");
        if (inArray(tag_name, ['I', 'BUTTON'])) {
            return;
        }

        if (parseInt($('#contentListBillToReceive [name="clients"]').val()) === 0 || $('#contentListBillToReceive #paid-tab').hasClass('active')) {
            return;
        }

        e.currentTarget.classList.toggle('selected');
        recalculateTotals();
    });
});

const recalculateTotals = () => {
    $('#contentListBillToReceive .values .price').text('R$ ' + numberToReal(tableBillsToReceive.rows('.selected').data().pluck(2).reduce((accumulator, object) => accumulator + realToNumber(object.replace('R$ ', '')), 0)));
    $('#contentListBillToReceive .values .quantity').text(tableBillsToReceive.rows('.selected').data().length);
}

const setTabRental = () => {
    const url = window.location.href;
    const splitUrl = url.split('#');
    let tab = 'without_pay';

    if (splitUrl.length === 2) {
        tab = splitUrl[1];
    }

    $(`#contentListBillToReceive #${tab}-tab`).tab('show');
    getTable(tab);
}

const loadCountsTabRental = () => {
    $('#contentListBillToReceive .nav-tabs.tickets-tab-switch').each(function () {
        $(this).find('li a .badge').html('<i class="fa fa-spin fa-spinner" style="margin-right: 0px"></i>');
    })
}

const disabledLoadData = () => {
    $('#contentListBillToReceive a[data-bs-toggle="tab"], #contentListBillToReceive select[name="clients"]').prop('disabled', true);
    $('#contentListBillToReceive .table.dataTable thead tr th[aria-controls="tableBillsToReceive"]:eq(3), #contentListBillToReceive .dataTables_scrollFoot .table.dataTable tfoot tr th:eq(3)').html('<div class="col-md-12"><i class="fa fa-spin fa-spinner text-center"></i></div>');
}

const enabledLoadData = () => {
    $('#contentListBillToReceive a[data-bs-toggle="tab"], #contentListBillToReceive select[name="clients"]').prop('disabled', false);
}

const getCountsTabRentals = () => {
    const start_date = $('#contentListBillToReceive input[name="intervalDates"]').data('daterangepicker').startDate.format('YYYY-MM-DD');
    const end_date   = $('#contentListBillToReceive input[name="intervalDates"]').data('daterangepicker').endDate.format('YYYY-MM-DD');

    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type: 'POST',
        data: {
            client: $('#contentListBillToReceive [name="clients"]').val(),
            start_date,
            end_date
        },
        url: $('[name="route_bills_to_receive_get_qty_type_rentals"]').val(),
        dataType: 'json',
        success: response => {

            $.each(response, function (index, value) {
                $(`#contentListBillToReceive #${index}-tab .badge`).text(value)
            });

        }, error: e => {
            console.log(e);
        },
        complete: function (xhr) {
            if (xhr.status === 403) {
                Toast.fire({
                    icon: 'error',
                    title: 'Você não tem permissão para fazer essa operação!'
                });
                $(`#contentListBillToReceive button[rental-id="${rental_id}"]`).trigger('blur');
            }
        }
    });
}

const getTable = typeRentals => {
    loadCountsTabRental();
    disabledLoadData();
    const client_id = parseInt($('#contentListBillToReceive [name="clients"]').val());

    $('#contentListBillToReceive [data-bs-toggle="tooltip"]').tooltip('dispose');

    if (typeof tableBillsToReceive !== 'undefined') {
        tableBillsToReceive.destroy();
    }

    $("#contentListBillToReceive #tableBillsToReceive tbody").empty();

    if (isNaN(client_id) || client_id === null) {
        tableBillsToReceive = $("#contentListBillToReceive #tableBillsToReceive").DataTable();
        enabledLoadData();
        return;
    }

    tableBillsToReceive = $("#contentListBillToReceive #tableBillsToReceive").DataTable({
        "processing": true,
        "autoWidth": false,
        "sortable": true,
        "searching": true,
        "order": [[ 3, 'asc' ]],
        "language": {
            "url": "https://cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json"
        },
        columnDefs: [
            {
                targets: 2,
                render: function (data, type, row) {
                    if (type === 'sort') {
                        return parseFloat(row.due_value);
                    }
                    return data;
                }
            },
            {
                targets: 3,
                render: function (data, type, row) {
                    if (type === 'sort') {
                        return row.due_date;
                    }
                    return data;
                }
            }
        ],
        dom: 'Bfrtip',
        buttons: [
            'pageLength',
            {
                className: typeRentals === 'without_pay' ? 'btn btn-primary btn-sm' : 'd-none',
                text: '<i class="fa-solid fa-list-check"></i> Selecionado todos',
                enabled: parseInt($('#contentListBillToReceive [name="clients"]').val()) !== 0 && typeRentals === 'without_pay',
                attr: {
                    title: parseInt($('#contentListBillToReceive [name="clients"]').val()) === 0 ? 'Selecione um cliente para efetuar múltiplos pagamentos' : '',
                    "data-bs-toggle": "tooltip"
                },
                action: function (e, dt, node, config) {
                    if (tableBillsToReceive.rows('.selected').data().length === tableBillsToReceive.rows().data().length) {
                        tableBillsToReceive.rows().every(function(rowIdx, tableLoop, rowLoop){
                            $(tableBillsToReceive.row(rowIdx).node()).removeClass('selected');
                        });
                    } else {
                        tableBillsToReceive.rows().every(function (rowIdx, tableLoop, rowLoop) {
                            $(tableBillsToReceive.row(rowIdx).node()).addClass('selected');
                        });
                    }
                    recalculateTotals();
                }
            },
            {
                className: typeRentals === 'without_pay' ? 'btn btn-primary btn-sm' : 'd-none',
                text: '<i class="fa-solid fa-check"></i> Pagar selecionados',
                enabled: parseInt($('#contentListBillToReceive [name="clients"]').val()) !== 0 && typeRentals === 'without_pay',
                attr: {
                    id: 'pay_all_parcels',
                    title: parseInt($('#contentListBillToReceive [name="clients"]').val()) === 0 ? 'Selecione um cliente para efetuar múltiplos pagamentos' : '',
                    "data-bs-toggle": "tooltip"
                },
                action: function (e, dt, node, config) {
                    if (parseInt($('#contentListBillToReceive [name="clients"]').val()) === 0) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Atenção',
                            html: 'Selecione um cliente para efetuar múltiplos pagamentos'
                        });
                        return false;
                    }
                    if (tableBillsToReceive.rows('.selected').data().length === 0) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Atenção',
                            html: 'Nenhum pagamento selecionado'
                        });
                        return false;
                    }

                    const payment_ids = tableBillsToReceive.rows('.selected').data().pluck('payment_id').join('-');
                    const name_client = $(`#contentListBillToReceive select[name="clients"] option[value="${$('[name="clients"]').val()}"]`).text();
                    const due_value = 'R$ ' + numberToReal(tableBillsToReceive.rows('.selected').data().pluck(2).reduce((accumulator, object) => accumulator + realToNumber(object.replace('R$ ', '')), 0));

                    $('#modalConfirmPayment').find('[name="rental_code"]').closest('.form-group').hide();
                    $('#modalConfirmPayment').find('[name="client"]').val(name_client);
                    $('#modalConfirmPayment').find('[name="date_rental"]').closest('.form-group').hide();
                    $('#modalConfirmPayment').find('[name="due_date"]').closest('.form-group').hide();
                    $('#modalConfirmPayment').find('[name="due_value"]').val(due_value);
                    $('#modalConfirmPayment').find('[name="payment_id"]').val(payment_ids);
                    $('#modalConfirmPayment').find('[name="date_payment"]').val((new Date()).toJSON().slice(0, 10));
                    $('#modalConfirmPayment').find('[name="form_payment"]').val("");
                    $('#modalConfirmPayment').find('[type="submit"]').attr('disabled', false);
                    $('#modalConfirmPayment').modal('show')
                    checkLabelAnimate();
                }
            }
        ],
    });

    tableBillsToReceive.processing(true);

    getCountsTabRentals();

    const start_date = $('#contentListBillToReceive input[name="intervalDates"]').data('daterangepicker').startDate.format('YYYY-MM-DD');
    const end_date  = $('#contentListBillToReceive input[name="intervalDates"]').data('daterangepicker').endDate.format('YYYY-MM-DD');

    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type: 'POST',
        url: $('[name="route_bills_to_receive_fetch"]').val(),
        data: {
            type: typeRentals,
            client: $('#contentListBillToReceive [name="clients"]').val(),
            start_date,
            end_date,
            show_client_name_list: $('#contentListBillToReceive [name="clients"]').is(':visible') ? 1 : 0
        },
        dataType: 'json',
        success: response => {
            $(response.data).each(function(k,v){
                tableBillsToReceive.row.add(v);
            });
            tableBillsToReceive.draw(false);
        }, complete: () => {
            tableBillsToReceive.processing(false);
            enabledLoadData();
            $('#contentListBillToReceive #tableBillsToReceive_wrapper .dt-buttons button.dt-button').removeClass('dt-button');
            $('#contentListBillToReceive [data-bs-toggle="tooltip"]').tooltip();
            $('#contentListBillToReceive .table.dataTable thead tr th[aria-controls="tableBillsToReceive"]:eq(3), #contentListBillToReceive .dataTables_scrollFoot .table.dataTable tfoot tr th:eq(3)').text(typeRentals === 'paid' ? 'Pagamento' : 'Vencimento');
            recalculateTotals();
        }
    });
}
$('#contentListBillToReceive a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
    getTable(e.target.id.replace('-tab', ''));
});

$('#contentListBillToReceive [name="clients"]').on('change', function () {
    getTable($('#contentListBillToReceive [data-bs-toggle="tab"].active').attr('id').replace('-tab', ''));
});
