<!--117 - F6 - nova locação-->
<!--118 - F7 - consultar cliente-->
<div class="modal fade" id="modalGeneralSearchClient" tabindex="-1" role="dialog" aria-labelledby="modalGeneralSearchClient" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Consultar cliente</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="form-group col-md-12 content-general-search-client">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Nome</th>
                                            <th>Email</th>
                                            <th>Telefone</th>
                                            <th>Situação</th>
                                            <th>Ação</th>
                                        </tr>
                                    </thead>
                                    <tfoot>
                                        <tr>
                                            <th>#</th>
                                            <th>Nome</th>
                                            <th>Email</th>
                                            <th>Telefone</th>
                                            <th>Situação</th>
                                            <th>Ação</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-around">
                <button type="button" class="btn btn-secondary col-md-3" data-bs-dismiss="modal"><i class="fa fa-times"></i> Cancelar</button>
            </div>
        </div>
    </div>
</div>

<!--119 - F8 - consultar financeiro-->
<div class="modal fade" id="modalGeneralSearchBillReceive" tabindex="-1" role="dialog" aria-labelledby="modalGeneralSearchBillReceive" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Consultar financeiro</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="form-group col-md-12 content-general-search-bill-receive">
                                <table class="table mt-2">
                                    <thead>
                                        <tr>
                                            <th>Locação</th>
                                            <th>Cliente/Endereço</th>
                                            <th>Valor</th>
                                            <th>Vencimento</th>
                                            <th>Ação</th>
                                        </tr>
                                    </thead>
                                    <tfoot>
                                        <tr>
                                            <th>Locação</th>
                                            <th>Cliente/Endereço</th>
                                            <th>Valor</th>
                                            <th>Vencimento</th>
                                            <th>Ação</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-around">
                <button type="button" class="btn btn-secondary col-md-3" data-bs-dismiss="modal"><i class="fa fa-times"></i> Cancelar</button>
            </div>
        </div>
    </div>
</div>
@include('includes.bill_to_receive.view_payment', ['modal_id' => 'modalGeneralSearchBillReceiveViewPayment'])
@include('includes.bill_to_receive.confirm_payment', ['modal_id' => 'modalGeneralSearchBillReceiveConfirmPayment', 'form_id' => 'formGeneralSearchBillReceiveConfirmPayment'])
@include('includes.bill_to_receive.reopen', ['modal_id' => 'modalGeneralSearchBillReceiveReopenPayment', 'form_id' => 'formGeneralSearchBillReceiveReopenPayment'])

<!--120 - F9 - consultar equipamentos-->
<div class="modal fade" id="modalGeneralSearchEquipment" tabindex="-1" role="dialog" aria-labelledby="modalGeneralSearchEquipment" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Consultar Equipamento</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="form-group col-md-12 content-general-search-equipment">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Equipamento</th>
                                            <th>Referência</th>
                                            <th>Estoque</th>
                                            <th>Ação</th>
                                        </tr>
                                    </thead>
                                    <tfoot>
                                        <tr>
                                            <th>#</th>
                                            <th>Equipamento</th>
                                            <th>Referência</th>
                                            <th>Estoque</th>
                                            <th>Ação</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-around">
                <button type="button" class="btn btn-secondary col-md-3" data-bs-dismiss="modal"><i class="fa fa-times"></i> Cancelar</button>
            </div>
        </div>
    </div>
</div>
<script>
    let tableGeneralSearchBillReceive;
    setTimeout(() => {
        $(document).on('keydown', function (e) {
            if (parseInt(e.keyCode) === 117) {
                Swal.fire({
                    title: 'Direcionar para nova locação',
                    html: "Você tem certeza que deseja sair da página atual para acessar a página de criar uma nova locação?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#34B1AA',
                    cancelButtonColor: '#bbb',
                    confirmButtonText: 'Sim, ser direcionado',
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = `${$('[name="base_url"]').val()}/locacao/novo`
                    }
                });
            }
            if (parseInt(e.keyCode) === 118) {
                $('#modalGeneralSearchClient').modal('show')
            }
            if (parseInt(e.keyCode) === 119) {
                $('#modalGeneralSearchBillReceive').modal('show')
            }
            if (parseInt(e.keyCode) === 120) {
                $('#modalGeneralSearchEquipment').modal('show')
            }
        });

        getTableGeneralSearchClient();
        getTableGeneralSearchBillReceive();
        getTableGeneralSearchEquipment();
    }, 500);

    const getTableGeneralSearchClient = () => {
        getTableList(
            '{{ route('ajax.client.fetch') }}',
            {
                active: 'all',
                system_search: 1
            },
            'modalGeneralSearchClient .content-general-search-client table',
            false,
            [ 0, 'desc' ],
            'POST',
            () => {},
            () => {
                $('[data-bs-toggle="tooltip"]').tooltip();
                $('#modalGeneralSearchClient .dataTables_wrapper .row .col-sm-12.col-md-6:eq(0)').remove()
                $('#modalGeneralSearchClient .dataTables_wrapper .row .col-sm-12.col-md-6').toggleClass('col-md-6 col-md-12')
                $('#modalGeneralSearchClient .dataTables_wrapper .dataTables_filter').width('100%')
                $('#modalGeneralSearchClient .dataTables_wrapper .dataTables_filter label').width('100%').addClass('d-flex flex-wrap mb-4')
                $('#modalGeneralSearchClient .dataTables_wrapper .dataTables_filter label input').addClass('ml-0').width('100%')
            },
            () => {},
            {
                bLengthChange: false,
                search: {
                    "addClass": 'form-control input-lg col-xs-12'
                }
            }
        );
    }
    const getTableGeneralSearchBillReceive = () => {
        if (typeof tableGeneralSearchBillReceive !== 'undefined') {
            tableGeneralSearchBillReceive.destroy();
        }

        tableGeneralSearchBillReceive = getTableList(
            '{{ route('ajax.bills_to_receive.fetch') }}',
            {
                start_date: sumYearsDateNow(-5),
                end_date: dateNow(),
                system_search: 1,
                client: 0,
                type: 'without_pay',
                show_client_name_list: 1
            },
            'modalGeneralSearchBillReceive .content-general-search-bill-receive table',
            true,
            [ 0, 'desc' ],
            'POST',
            () => {},
            () => {
                $('[data-bs-toggle="tooltip"]').tooltip();
                $('#modalGeneralSearchBillReceive .dataTables_wrapper .row .col-sm-12.col-md-6:eq(0)').remove()
                $('#modalGeneralSearchBillReceive .dataTables_wrapper .row .col-sm-12.col-md-6').toggleClass('col-md-6 col-md-12')
                $('#modalGeneralSearchBillReceive .dataTables_wrapper .dataTables_filter').width('100%')
                $('#modalGeneralSearchBillReceive .dataTables_wrapper .dataTables_filter label').width('100%').addClass('d-flex flex-wrap mb-4')
                $('#modalGeneralSearchBillReceive .dataTables_wrapper .dataTables_filter label input').addClass('ml-0').width('100%')
            },
            () => {},
            {
                bLengthChange: false,
                search: {
                    "addClass": 'form-control input-lg col-xs-12'
                }
            }
        );
    }
    const getTableGeneralSearchEquipment = () => {
        getTableList(
            '{{ route('ajax.equipment.fetch') }}',
            {
                system_search: 1
            },
            'modalGeneralSearchEquipment .content-general-search-equipment table',
            false,
            [ 0, 'desc' ],
            'POST',
            () => {},
            () => {
                $('[data-bs-toggle="tooltip"]').tooltip();
                $('#modalGeneralSearchEquipment .dataTables_wrapper .row .col-sm-12.col-md-6:eq(0)').remove()
                $('#modalGeneralSearchEquipment .dataTables_wrapper .row .col-sm-12.col-md-6').toggleClass('col-md-6 col-md-12')
                $('#modalGeneralSearchEquipment .dataTables_wrapper .dataTables_filter').width('100%')
                $('#modalGeneralSearchEquipment .dataTables_wrapper .dataTables_filter label').width('100%').addClass('d-flex flex-wrap mb-4')
                $('#modalGeneralSearchEquipment .dataTables_wrapper .dataTables_filter label input').addClass('ml-0').width('100%')
            },
            () => {},
            {
                bLengthChange: false,
                search: {
                    "addClass": 'form-control input-lg col-xs-12'
                }
            }
        );
    }
</script>
@include('includes.bill_to_receive.script-view', ['modal_id' => 'modalGeneralSearchBillReceiveViewPayment', 'btn_class_action' => 'btnGeneralSearchBillReceiveViewPayment'])
@include('includes.bill_to_receive.script-confirm', ['modal_id' => 'modalGeneralSearchBillReceiveConfirmPayment', 'btn_class_action' => 'btnGeneralSearchBillReceiveConfirmPayment', 'form_id' => 'formGeneralSearchBillReceiveConfirmPayment'])
@include('includes.bill_to_receive.script-reopen', ['modal_id' => 'modalGeneralSearchBillReceiveReopenPayment', 'btn_class_action' => 'btnGeneralSearchBillReceiveReopenPayment', 'form_id' => 'formGeneralSearchBillReceiveReopenPayment'])
