let tableBillsToPay;
let rows_selected = [];

$(function () {
    loadDaterangePickerInput($('#contentListBillToPay input[name="intervalDates"]'), function () {
        getTable($('[data-bs-toggle="tab"].active').attr('id').replace('-tab',''));
    });
    setTabBill();
    getOptionsForm('form-of-payment', $('#modalReopenPayment [name="form_payment"], #modalConfirmPayment [name="form_payment"], #modalViewPayment [name="form_payment"]'));

    tableBillsToPay.on('click', 'tbody tr', function (e) {
        const tag_name = $(e.target).prop("tagName");
        if (inArray(tag_name, ['I', 'BUTTON'])) {
            return;
        }

        if (parseInt($('#contentListBillToPay [name="providers"]').val()) === 0 || $('#contentListBillToPay #paid-tab').hasClass('active')) {
            return;
        }

        e.currentTarget.classList.toggle('selected');
        recalculateTotals();
    });
});

const recalculateTotals = () => {
    $('#contentListBillToPay .values .price').text('R$ ' + numberToReal(tableBillsToPay.rows('.selected').data().pluck(2).reduce((accumulator,object) => accumulator + realToNumber(object.replace('R$ ', '')) ,0)));
    $('#contentListBillToPay .values .quantity').text(tableBillsToPay.rows('.selected').data().length);
}

const setTabBill = () => {
    const url = window.location.href;
    const splitUrl = url.split('#');
    let tab = 'without_pay';

    if (splitUrl.length === 2) {
        tab = splitUrl[1];
    }

    $(`#contentListBillToPay #${tab}-tab`).tab('show');
    getTable(tab);
}

const loadCountsTabBill = () => {
    $('#contentListBillToPay .nav-tabs.tickets-tab-switch').each(function(){
        $(this).find('li a .badge').html('<i class="fa fa-spin fa-spinner" style="margin-right: 0px"></i>');
    })
}

const disabledLoadData = () => {
    $('#contentListBillToPay a[data-bs-toggle="tab"], #contentListBillToPay select[name="providers"]').prop('disabled', true);
    $('#contentListBillToPay .table.dataTable thead tr th[aria-controls="tableBillsToPay"]:eq(3), #contentListBillToPay .dataTables_scrollFoot .table.dataTable tfoot tr th:eq(3)').html('<div class="col-md-12"><i class="fa fa-spin fa-spinner text-center"></i></div>');
}

const enabledLoadData = () => {
    $('#contentListBillToPay a[data-bs-toggle="tab"], #contentListBillToPay select[name="providers"]').prop('disabled', false);
}

const getCountsTabBills = () => {
    const start_date = $('#contentListBillToPay input[name="intervalDates"]').data('daterangepicker').startDate.format('YYYY-MM-DD');
    const end_date   = $('#contentListBillToPay input[name="intervalDates"]').data('daterangepicker').endDate.format('YYYY-MM-DD');

    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type: 'POST',
        data: {
            provider: $('#contentListBillToPay [name="providers"]').val(),
            start_date,
            end_date
        },
        url: $('[name="route_bills_to_pay_get_qty_type_rentals"]').val(),
        dataType: 'json',
        success: response => {

            $.each(response, function( index, value ) {
                $(`#contentListBillToPay #${index}-tab .badge`).text(value)
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
                $(`#contentListBillToPay button[bill-id="${bill_id}"]`).trigger('blur');
            }
        }
    });
}

const getTable = typeBills => {
    loadCountsTabBill();
    disabledLoadData();
    const provider_id = parseInt($('#contentListBillToPay [name="providers"]').val());

    $('#contentListBillToPay [data-bs-toggle="tooltip"]').tooltip('dispose');

    if (typeof tableBillsToPay !== 'undefined') {
        tableBillsToPay.destroy();
    }

    $("#contentListBillToPay #tableBillsToPay tbody").empty();

    if (isNaN(provider_id) || provider_id === null) {
        tableBillsToPay = $("#contentListBillToPay #tableBillsToPay").DataTable();
        enabledLoadData();
        return;
    }

    tableBillsToPay = $("#contentListBillToPay #tableBillsToPay").DataTable({
        "processing": true,
        "autoWidth": false,
        "sortable": true,
        "searching": true,
        "order": [[ 3, 'asc' ]],
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json"
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
                        console.log(row)
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
                className: typeBills === 'without_pay' ? 'btn btn-primary btn-sm' : 'd-none',
                text: '<i class="fa-solid fa-list-check"></i> Selecionado todos',
                enabled: parseInt($('#contentListBillToPay [name="providers"]').val()) !== 0 && typeBills === 'without_pay',
                attr: {
                    title: parseInt($('#contentListBillToPay [name="providers"]').val()) === 0 ? 'Selecione um fornecedor para efetuar múltiplos pagamentos' : '',
                    "data-bs-toggle": "tooltip"
                },
                action: function ( e, dt, node, config ) {
                    if (tableBillsToPay.rows('.selected').data().length === tableBillsToPay.rows().data().length) {
                        tableBillsToPay.rows().every(function(rowIdx, tableLoop, rowLoop){
                            $(tableBillsToPay.row(rowIdx).node()).removeClass('selected');
                        });
                    } else {
                        tableBillsToPay.rows().every(function (rowIdx, tableLoop, rowLoop) {
                            $(tableBillsToPay.row(rowIdx).node()).addClass('selected');
                        });
                    }
                    recalculateTotals();
                }
            },
            {
                className: typeBills === 'without_pay' ? 'btn btn-primary btn-sm' : 'd-none',
                text: '<i class="fa-solid fa-check"></i> Pagar selecionados',
                enabled: parseInt($('#contentListBillToPay [name="providers"]').val()) !== 0 && typeBills === 'without_pay',
                attr:  {
                    id: 'pay_all_parcels',
                    title: parseInt($('#contentListBillToPay [name="providers"]').val()) === 0 ? 'Selecione um fornecedor para efetuar múltiplos pagamentos' : '',
                    "data-bs-toggle": "tooltip"
                },
                action: function ( e, dt, node, config ) {
                    if (parseInt($('#contentListBillToPay [name="providers"]').val()) === 0) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Atenção',
                            html: 'Selecione um fornecedor para efetuar múltiplos pagamentos'
                        });
                        return false;
                    }
                    if (tableBillsToPay.rows('.selected').data().length === 0) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Atenção',
                            html: 'Nenhum pagamento selecionado'
                        });
                        return false;
                    }

                    const payment_ids = tableBillsToPay.rows('.selected').data().pluck('payment_id').join('-');
                    const name_client   = $(`#contentListBillToPay select[name="providers"] option[value="${$('[name="providers"]').val()}"]`).text();
                    const due_value     = 'R$ ' + numberToReal(tableBillsToPay.rows('.selected').data().pluck(2).reduce((accumulator,object) => accumulator + realToNumber(object.replace('R$ ', '')) ,0));

                    $('#modalConfirmPayment').find('[name="bill_code"]').closest('.form-group').hide();
                    $('#modalConfirmPayment').find('[name="provider"]').val(name_client);
                    $('#modalConfirmPayment').find('[name="date_bill"]').closest('.form-group').hide();
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
        ]
    });

    tableBillsToPay.processing(true);

    getCountsTabBills();

    const start_date = $('#contentListBillToPay input[name="intervalDates"]').data('daterangepicker').startDate.format('YYYY-MM-DD');
    const end_date   = $('#contentListBillToPay input[name="intervalDates"]').data('daterangepicker').endDate.format('YYYY-MM-DD');

    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type: 'POST',
        url: $('[name="route_bills_to_pay_fetch"]').val(),
        data: {
            type: typeBills,
            provider: $('#contentListBillToPay [name="providers"]').val(),
            start_date,
            end_date,
            show_provider_name_list: $('#contentListBillToPay [name="providers"]').is(':visible') ? 1 : 0
        },
        dataType: 'json',
        success: response => {
            $(response.data).each(function(k,v){
                tableBillsToPay.row.add(v);
            });
            tableBillsToPay.draw(false);
        }, complete: () => {
            tableBillsToPay.processing(false);
            enabledLoadData();
            $('#contentListBillToPay #tableBillsToPay_wrapper .dt-buttons button.dt-button').removeClass('dt-button');
            $('#contentListBillToPay [data-bs-toggle="tooltip"]').tooltip();
            $('#contentListBillToPay .table.dataTable thead tr th[aria-controls="tableBillsToPay"]:eq(3), #contentListBillToPay .dataTables_scrollFoot .table.dataTable tfoot tr th:eq(3)').text(typeBills === 'paid' ? 'Pagamento' : 'Vencimento');
            recalculateTotals();
        }
    });
}

const setFieldsToPayment = (btn, modal) => {
    const bill_code         = btn.data('bill-code');
    const name_provider     = btn.data('name-provider');
    const date_bill         = btn.data('date-bill');
    const due_date          = btn.data('due-date');
    const due_value         = 'R$ ' + btn.data('due-value');
    const payment_id        = btn.data('payment-id');
    const bill_payment_id   = btn.data('bill-payment-id');
    const payday            = btn.data('payday');
    const description       = btn.data('description');

    modal.find('[name="date_payment"]').closest('.form-group').show();
    modal.find('[name="form_payment"]').closest('.form-group').show();

    if ($('#paid-tab.active').length) {
        modal.find('[name="date_payment"]').val(payday);
        modal.find('[name="form_payment"]').val(payment_id);
        modal.find('.modal-title').text('Detalhes do Pagamento');
        modal.find('[name="payment_id"]').val(bill_payment_id);
    } else {
        modal.find('[name="date_payment"]').closest('.form-group').hide();
        modal.find('[name="form_payment"]').closest('.form-group').hide();
        modal.find('.modal-title').text('Detalhes do Lançamento');
    }

    modal.find('[name="bill_code"]').val(bill_code);
    modal.find('[name="provider"]').val(name_provider);
    modal.find('[name="date_bill"]').val(date_bill);
    modal.find('[name="due_date"]').val(due_date);
    modal.find('[name="due_value"]').val(due_value);
    modal.find('#observationDiv').html(description);

    modal.modal('show');

    checkLabelAnimate();
    new Quill('#observationDiv').enable(false);
}

$('#contentListBillToPay a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
    getTable(e.target.id.replace('-tab',''));
});

$(document).on('click', '#contentListBillToPay .btnViewPayment', function() {
    setFieldsToPayment($(this), $('#modalViewPayment'));
});

$(document).on('click', '#contentListBillToPay .btnReopenPayment', function() {
    setFieldsToPayment($(this), $('#modalReopenPayment'));
});

$(document).on('click', '#contentListBillToPay .btnConfirmPayment', function() {
    const payment_id    = $(this).data('bill-payment-id');
    const bill_code     = $(this).data('bill-code');
    const name_provider = $(this).data('name-provider');
    const date_bill     = $(this).data('date-bill');
    const due_date      = $(this).data('due-date');
    const due_value     = 'R$ ' + $(this).data('due-value');

    $('#modalConfirmPayment').find('[name="bill_code"]').val(bill_code).closest('.form-group').show();
    $('#modalConfirmPayment').find('[name="provider"]').val(name_provider);
    $('#modalConfirmPayment').find('[name="date_bill"]').val(date_bill).closest('.form-group').show();
    $('#modalConfirmPayment').find('[name="due_date"]').val(due_date).closest('.form-group').show();
    $('#modalConfirmPayment').find('[name="due_value"]').val(due_value);
    $('#modalConfirmPayment').find('[name="payment_id"]').val(payment_id);
    $('#modalConfirmPayment').find('[name="date_payment"]').val((new Date()).toJSON().slice(0, 10));
    $('#modalConfirmPayment').find('[name="form_payment"]').val("");
    $('#modalConfirmPayment').find('[type="submit"]').attr('disabled', false);
    checkLabelAnimate();
    $('#modalConfirmPayment').modal('show');
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

            getTable($('[data-bs-toggle="tab"].active').attr('id').replace('-tab',''));
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
        }
    }).always(() => {
        btn.attr('disabled', false);
    });
});

$('#formReopenPayment').on('submit', function(e) {
    e.preventDefault();
    const payment_id    = $('[name="payment_id"]', this).val();
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

            getTable($('#contentListBillToPay [data-bs-toggle="tab"].active').attr('id').replace('-tab',''));
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
        }
    }).always(function() {
        btn.attr('disabled', false);
    });
});

$('#contentListBillToPay [name="providers"]').on('change', function(){
    getTable($('#contentListBillToPay [data-bs-toggle="tab"].active').attr('id').replace('-tab',''));
});
