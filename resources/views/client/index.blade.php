@extends('adminlte::page')

@section('title', 'Listagem de Clientes')

@section('content_header')
    <h1 class="m-0 text-dark">Listagem de Clientes</h1>
@stop

@section('css')
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
    @if(in_array('BillsToReceiveView', $permissions)) <link href="{{ asset('assets/css/views/bills_to_receive.css') }}" rel="stylesheet"> @endif
@stop

@section('js')
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js" type="application/javascript"></script>
    <script>
        var tableClient;
        $(function () {
            tableClient = getTableClient(false);
        });

        const getTableClient = (stateSave = true) => {

            const active = $('#active').val();

            return $("#tableClients").DataTable({
                "responsive": true,
                "processing": true,
                "autoWidth": false,
                "serverSide": true,
                "sortable": true,
                "searching": true,
                "stateSave": stateSave,
                "serverMethod": "post",
                "order": [[ 0, 'desc' ]],
                "ajax": {
                    url: '{{ route('ajax.client.fetch') }}',
                    pages: 2,
                    type: 'POST',
                    data: { "_token": $('meta[name="csrf-token"]').attr('content'), active },
                    error: function(jqXHR, ajaxOptions, thrownError) {
                        console.log(jqXHR, ajaxOptions, thrownError);
                    }
                },
                "initComplete": function( settings, json ) {
                    $('[data-toggle="tooltip"]').tooltip();
                },
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Portuguese-Brasil.json"
                }
            });
        }

        $(document).on('click', '.btnRemoveClient', function (){
            const client_id = $(this).data('client-id');
            const client_name = $(this).closest('tr').find('td:eq(1)').text();

            Swal.fire({
                title: 'Exclusão de Cliente',
                html: "Você está prestes a excluir definitivamente o cliente <br><strong>"+client_name+"</strong><br>Deseja continuar?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#bbb',
                confirmButtonText: 'Sim, excluir',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        type: 'POST',
                        url: "{{ route('ajax.client.delete') }}",
                        data: { client_id },
                        dataType: 'json',
                        success: response => {
                            $('[data-toggle="tooltip"]').tooltip('dispose')
                            tableClient.destroy();
                            $("#tableClients tbody").empty();
                            tableClient = getTableClient();
                            Toast.fire({
                                icon: response.success ? 'success' : 'error',
                                title: response.message
                            });
                        }, error: e => {
                            if (e.status !== 403 && e.status !== 422)
                                console.log(e);
                        },
                        complete: function(xhr) {
                            if (xhr.status === 403) {
                                Toast.fire({
                                    icon: 'error',
                                    title: 'Você não tem permissão para fazer essa operação!'
                                });
                                $(`button[data-client-id="${client_id}"]`).trigger('blur');
                            }
                            if (xhr.status === 422) {

                                let arrErrors = [];

                                $.each(xhr.responseJSON.errors, function( index, value ) {
                                    arrErrors.push(value);
                                });

                                if (!arrErrors.length && xhr.responseJSON.message !== undefined)
                                    arrErrors.push('Você não tem permissão para fazer essa operação!');

                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Atenção',
                                    html: '<ol><li>'+arrErrors.join('</li><li>')+'</li></ol>'
                                });
                            }
                        }
                    });
                }
            })
        });

        $('#active').on('change', function(){
            tableClient.destroy();
            $("#tableClients tbody").empty();
            tableClient = getTableClient();
        });

        $(document).on('click', '.btnViewBillClient', function(){
            const client_id     = $(this).data('client-id');
            const client_name   = $(this).data('client-name');

            $('#contentListBillToReceive [name="clients"]').empty().append(`<option value="${client_id}">${client_name}</option>`);

            $('#modalFinancialStatement').modal();

            $('#contentListBillToReceive [name="clients"]').trigger('change');
        })
    </script>
    @if(in_array('BillsToReceiveView', $permissions)) <script src="{{ asset('assets/js/views/bill_to_receive/index.js') }}" type="application/javascript"></script> @endif
@stop

@section('content')
    <div class="row">
        <div class="col-md-12 grid-margin">
            @if(session('success'))
                <div class="alert alert-animate alert-success"><i class="fa fa-check-circle"></i> {{session('success')}}</div>
            @endif
            @if(session('warning'))
                <div class="alert alert-animate alert-danger mt-2">{{session('warning')}}</div>
            @endif
            <div class="card">
                <div class="card-body">
                    <div class="header-card-body justify-content-between flex-wrap">
                        <h4 class="card-title no-border">Clientes Cadastrados</h4>
                        @if(in_array('ClientCreatePost', $permissions))
                        <a href="{{ route('client.create') }}" class="mb-3 btn btn-primary col-md-3 btn-rounded btn-fw"><i class="fas fa-plus"></i> Novo Cadastro</a>
                        @endif
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 form-group">
                            <label>Situação</label>
                            <select class="form-control" id="active">
                                <option value="1">Ativo</option>
                                <option value="0">Inativo</option>
                                <option value="all">Todos</option>
                            </select>
                        </div>
                    </div>
                    <table id="tableClients" class="table table-bordered">
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
                        <tbody>
                        </tbody>
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
    @if(in_array('BillsToReceiveView', $permissions))
        <div class="modal fade" id="modalFinancialStatement" tabindex="-1" role="dialog" aria-labelledby="modalFinancialStatement" aria-hidden="true">
            <div class="modal-dialog modal-md" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Ficha Financeira</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-0">
                        @include('includes.bill_to_receive.content', ['show_select_client' => false, 'card_title' => false, 'billStartFilterDate' => '01/01/2020', 'billEndFilterDate' => dateInternationalToDateBrazil(dateNowInternational(), DATE_BRAZIL)])
                    </div>
                    <div class="modal-footer d-flex justify-content-around">
                        <button type="button" class="btn btn-secondary col-md-3" data-dismiss="modal"><i class="fa fa-times"></i> Cancelar</button>
                    </div>
                </div>
            </div>
        </div>
        @include('includes.bill_to_receive.confirm_payment')
        @include('includes.bill_to_receive.view_payment')
        @include('includes.bill_to_receive.reopen')
    @endif
@stop
