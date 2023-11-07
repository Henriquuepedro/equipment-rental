@extends('adminlte::page')

@section('title', 'Listagem de Fornecedores')

@section('content_header')
    <h1 class="m-0 text-dark">Listagem de Fornecedores</h1>
@stop

@section('css')
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
    @if(in_array('BillsToPayView', $permissions)) <link href="{{ asset('assets/css/views/bills_to_pay.css') }}" rel="stylesheet"> @endif
@stop

@section('js')
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js" type="application/javascript"></script>
    <script src="//cdn.datatables.net/plug-ins/1.13.7/api/processing().js" type="application/javascript"></script>
    <script>
        var tableProvider;
        $(function () {
            tableProvider = getTableProvider(false);
        });

        const getTableProvider = (stateSave = true) => {
            return $("#tableProviders").DataTable({
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
                    url: '{{ route('ajax.provider.fetch') }}',
                    pages: 2,
                    type: 'POST',
                    data: { "_token": $('meta[name="csrf-token"]').attr('content') },
                    error: function(jqXHR, ajaxOptions, thrownError) {
                        console.log(jqXHR, ajaxOptions, thrownError);
                    }
                },
                "initComplete": function( settings, json ) {
                    $('[data-toggle="tooltip"]').tooltip();
                },
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json"
                }
            });
        }

        $(document).on('click', '.btnRemoveProvider', function (){
            const provider_id = $(this).attr('provider-id');
            const provider_name = $(this).closest('tr').find('td:eq(1)').text();

            Swal.fire({
                title: 'Exclusão de Fornecedor',
                html: "Você está prestes a excluir definitivamente o fornecedor <br><strong>"+provider_name+"</strong><br>Deseja continuar?",
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
                        url: "{{ route('ajax.provider.delete') }}",
                        data: { provider_id },
                        dataType: 'json',
                        success: response => {
                            $('[data-toggle="tooltip"]').tooltip('dispose')
                            tableProvider.destroy();
                            $("#tableProviders tbody").empty();
                            tableProvider = getTableProvider();
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
                                $(`button[provider-id="${provider_id}"]`).trigger('blur');
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

        $(document).on('click', '.btnViewBillProvider', function(){
            const providers_id     = $(this).data('provider-id');
            const providers_name   = $(this).data('provider-name');

            $('#contentListBillToPay [name="providers"]').empty().append(`<option value="${providers_id}">${providers_name}</option>`);

            $('#modalFinancialStatement').modal();

            $('#contentListBillToPay [name="providers"]').trigger('change');
            $('#contentListBillToPay #without_pay-tab').trigger('click');
        });
    </script>
    @if(in_array('BillsToPayView', $permissions)) <script src="{{ asset('assets/js/views/bill_to_pay/index.js') }}" type="application/javascript"></script> @endif
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
                        <h4 class="card-title no-border">Fornecedores Cadastrados</h4>
                        @if(in_array('ProviderCreatePost', $permissions))
                        <a href="{{ route('provider.create') }}" class="mb-3 btn btn-primary col-md-3 btn-rounded btn-fw"><i class="fas fa-plus"></i> Novo Cadastro</a>
                        @endif
                    </div>
                    <table id="tableProviders" class="table table-bordered">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Telefone</th>
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
                                <th>Ação</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @if(in_array('BillsToPayView', $permissions))
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
                        @include('includes.bill_to_pay.content', ['show_select_provider' => false, 'card_title' => false, 'billStartFilterDate' => '01/01/2020', 'billEndFilterDate' => dateInternationalToDateBrazil(dateNowInternational(), DATE_BRAZIL)])
                    </div>
                    <div class="modal-footer d-flex justify-content-around">
                        <button type="button" class="btn btn-secondary col-md-3" data-dismiss="modal"><i class="fa fa-times"></i> Cancelar</button>
                    </div>
                </div>
            </div>
        </div>
        @include('includes.bill_to_pay.confirm_payment')
        @include('includes.bill_to_pay.view_payment')
        @include('includes.bill_to_pay.reopen')
    @endif
@stop
