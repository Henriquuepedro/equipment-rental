let tableBillsToReceive;
let rows_selected = [];

$(function () {
    loadDaterangePickerInput($('#contentListBillToReceive input[name="intervalDates"]'), function () {
        getTable($('#contentListBillToReceive [data-bs-toggle="tab"].active').attr('id').replace('-tab', ''));
    });
    setTabRental();
    getOptionsForm('form-of-payment', $('#modalReopenPayment [name="form_payment"], #modalConfirmPayment [name="form_payment"], #modalViewPayment [name="form_payment"]'));

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

$(document).on('ifChanged', '#modalConfirmPayment .equipment, #modalWithdraw .equipment', function () {

    const check = !$(this).is(':checked');

    $(this).closest('tr').find('.flatpickr-input, select, .input-button-calendar a').attr('disabled', check);

    $(this).closest('tr').toggleClass('noSelected selected');
});

$('#contentListBillToReceive a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
    getTable(e.target.id.replace('-tab', ''));
});

const setFieldsToPayment = (btn, modal) => {
    const rental_code = btn.data('rental-code');
    const name_client = btn.data('name-client');
    const date_rental = btn.data('date-rental');
    const due_date = btn.data('due-date');
    const due_value = 'R$ ' + btn.data('due-value');
    const payment_id = btn.data('payment-id');
    const rental_payment_id = btn.data('rental-payment-id');
    const payday = btn.data('payday');

    modal.find('[name="date_payment"]').closest('.form-group').show();
    modal.find('[name="form_payment"]').closest('.form-group').show();

    if ($('#paid-tab.active').length) {
        modal.find('[name="date_payment"]').val(payday);
        modal.find('[name="form_payment"]').val(payment_id);
        modal.find('.modal-title').text('Detalhes do Pagamento');
        modal.find('[name="payment_id"]').val(rental_payment_id);
    } else {
        modal.find('[name="date_payment"]').closest('.form-group').hide();
        modal.find('[name="form_payment"]').closest('.form-group').hide();
        modal.find('.modal-title').text('Detalhes do Lançamento');
    }

    modal.find('[name="rental_code"]').val(rental_code);
    modal.find('[name="client"]').val(name_client);
    modal.find('[name="date_rental"]').val(date_rental);
    modal.find('[name="due_date"]').val(due_date);
    modal.find('[name="due_value"]').val(due_value);
    checkLabelAnimate();
    modal.modal('show');
}

$(document).on('click', '.btnViewPayment', function () {
    setFieldsToPayment($(this), $('#modalViewPayment'));
});

$(document).on('click', '.btnReopenPayment', function () {
    setFieldsToPayment($(this), $('#modalReopenPayment'));
});

$(document).on('click', '.btnConfirmPayment', function () {
    const payment_id = $(this).data('rental-payment-id');
    const rental_code = $(this).data('rental-code');
    const name_client = $(this).data('name-client');
    const date_rental = $(this).data('date-rental');
    const due_date = $(this).data('due-date');
    const due_value = 'R$ ' + $(this).data('due-value');

    $('#modalConfirmPayment').find('[name="rental_code"]').val(rental_code).closest('.form-group').show();
    $('#modalConfirmPayment').find('[name="client"]').val(name_client);
    $('#modalConfirmPayment').find('[name="date_rental"]').val(date_rental).closest('.form-group').show();
    $('#modalConfirmPayment').find('[name="due_date"]').val(due_date).closest('.form-group').show();
    $('#modalConfirmPayment').find('[name="due_value"]').val(due_value);
    $('#modalConfirmPayment').find('[name="payment_id"]').val(payment_id);
    $('#modalConfirmPayment').find('[name="date_payment"]').val((new Date()).toJSON().slice(0, 10));
    $('#modalConfirmPayment').find('[name="form_payment"]').val("");
    $('#modalConfirmPayment').find('[type="submit"]').attr('disabled', false);
    checkLabelAnimate();
    $('#modalConfirmPayment').modal('show');
});

$('#formConfirmPayment').on('submit', function (e) {
    e.preventDefault();
    const payment_id = $('[name="payment_id"]', this).val();
    const form_payment = $('[name="form_payment"]', this).val();
    const date_payment = $('[name="date_payment"]', this).val();
    const endpoint = $(this).attr('action');
    const btn = $(this).find('[type="submit"]');

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

            getTable($('#contentListBillToReceive [data-bs-toggle="tab"].active').attr('id').replace('-tab', ''));
        }, error: e => {
            console.log(e);
            let arrErrors = [];

            $.each(e.responseJSON.errors, function (index, value) {
                arrErrors.push(value);
            });

            if (!arrErrors.length && e.responseJSON.message !== undefined) {
                arrErrors.push('Você não tem permissão para fazer essa operação!');
            }

            Swal.fire({
                icon: 'warning',
                title: 'Atenção',
                html: '<ol><li>' + arrErrors.join('</li><li>') + '</li></ol>'
            });
        }
    }).always(() => {
        btn.attr('disabled', false);
    });

});

$('#formReopenPayment').on('submit', function (e) {
    e.preventDefault();
    const payment_id = $('[name="payment_id"]', this).val();
    const endpoint = $(this).attr('action');
    const btn = $(this).find('[type="submit"]');

    btn.attr('disabled', true);

    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type: 'POST',
        url: endpoint,
        data: {
            payment_id
        },
        dataType: 'json',
        success: response => {
            if (!response.success) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Atenção',
                    html: '<ol><li>' + response.message + '</li></ol>'
                });
                return false;
            }

            $('#modalReopenPayment').modal('hide');

            Toast.fire({
                icon: 'success',
                title: response.message
            });

            getTable($('#contentListBillToReceive [data-bs-toggle="tab"].active').attr('id').replace('-tab', ''));
        }, error: e => {
            console.log(e);
            let arrErrors = [];

            $.each(e.responseJSON.errors, function (index, value) {
                arrErrors.push(value);
            });

            if (!arrErrors.length && e.responseJSON.message !== undefined) {
                arrErrors.push('Você não tem permissão para fazer essa operação!');
            }

            Swal.fire({
                icon: 'warning',
                title: 'Atenção',
                html: '<ol><li>' + arrErrors.join('</li><li>') + '</li></ol>'
            });
        }
    }).always(function () {
        btn.attr('disabled', false);
    });
});

$('#contentListBillToReceive [name="clients"]').on('change', function () {
    getTable($('#contentListBillToReceive [data-bs-toggle="tab"].active').attr('id').replace('-tab', ''));
});
