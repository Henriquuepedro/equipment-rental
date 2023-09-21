@extends('adminlte::page')

@section('title', 'Listagem de Empresas')

@section('content_header')
    <h1 class="m-0 text-dark">Listagem de Empresas</h1>
@stop

@section('css')
@stop

@section('js')
    <script>
        var tableCompany;
        $(function () {
            tableCompany = getTable(false);
        });

        const getTable = (stateSave = true) => {

            const status = $('#status').val();

            return $("#tableCompanies").DataTable({
                "responsive": true,
                "processing": true,
                "autoWidth": false,
                "serverSide": true,
                "sortable": true,
                "searching": true,
                "stateSave": stateSave,
                "serverMethod": "post",
                "order": [[ 5, 'desc' ]],
                "ajax": {
                    url: '{{ route('ajax.master.company.fetch') }}',
                    pages: 2,
                    type: 'POST',
                    data: { "_token": $('meta[name="csrf-token"]').attr('content'), status },
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

        $('#status').on('change', function(){
            tableCompany.destroy();
            $("#tableCompanies tbody").empty();
            tableCompany = getTable();
        });

        $(document).on('click', '.btn-add-expiration-time', function () {
            const company_id = $(this).data('company-id');
            const expiration_date = $(`#tableCompanies_wrapper [data-company-id="${company_id}"]`).closest('tr').find('td:eq(5)').text();
            const company_name = $(`#tableCompanies_wrapper [data-company-id="${company_id}"]`).closest('tr').find('td:eq(0)').text();
            $('#addExpirationTime [name="company_id"]').val(company_id);
            $('#addExpirationTime .expiration_date').text(expiration_date);
            $('#addExpirationTime .company_name').text(company_name);
            $('#addExpirationTime').modal();
        });

        $('#formAddExpirationTime').submit(function(){
            let getForm = $(this);

            getForm.find('button[type="submit"]').attr('disabled', true);

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'POST',
                url: getForm.attr('action'),
                data: getForm.serialize(),
                dataType: 'json',
                success: response => {
                    getForm.find('button[type="submit"]').attr('disabled', false);

                    Toast.fire({
                        icon: response.success ? 'success' : 'error',
                        title: response.message
                    })

                    if (response.success) {
                        $('#addExpirationTime').modal('hide');
                        $('#addExpirationTime [name="type"]').val('');
                        $('#addExpirationTime [name="time"]').val('');
                        $(`#tableCompanies_wrapper [data-company-id="${response.company_id}"]`).closest('tr').find('td:eq(5)').text(response.new_expiration_date);
                    }

                }, error: e => {
                    getForm.find('button[type="submit"]').attr('disabled', false);
                    let arrErrors = [];

                    $.each(e.responseJSON.errors, function( index, value ) {
                        arrErrors.push(value);
                    });
                    Swal.fire({
                        icon: 'warning',
                        title: 'Atenção',
                        html: '<ol><li>'+arrErrors.join('</li><li>')+'</li></ol>'
                    });
                }
            });
            return false;
        });
    </script>
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
                        <h4 class="card-title no-border">Empresas Cadastrados</h4>
                        <a href="{{ route('master.company.create') }}" class="mb-3 btn btn-primary col-md-3 btn-rounded btn-fw"><i class="fas fa-plus"></i> Novo Cadastro</a>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 form-group">
                            <label>Situação</label>
                            <select class="form-control" id="status">
                                <option value="1">Ativo</option>
                                <option value="0">Inativo</option>
                                <option value="all">Todos</option>
                            </select>
                        </div>
                    </div>
                    <table id="tableCompanies" class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>CPF/CNPJ</th>
                                <th>Email</th>
                                <th>Telefone</th>
                                <th>Situação</th>
                                <th>Expira em</th>
                                <th>Criado em</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>Nome</th>
                                <th>CPF/CNPJ</th>
                                <th>Email</th>
                                <th>Telefone</th>
                                <th>Situação</th>
                                <th>Expira em</th>
                                <th>Criado em</th>
                                <th>Ação</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addExpirationTime" tabindex="-1" role="dialog" aria-labelledby="addExpirationTimeLabel" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <form action="{{ route('ajax.master.company.add-expiration-time') }}" method="POST" id="formAddExpirationTime">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addExpirationTimeLabel">Adicionar tempo de expiração</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <h5>Informe o tempo de expiração adicional para a empresa: <span class="font-weight-bold company_name"></span></h5>
                                <p>Atualmente a data de expiração é para: <span class="font-weight-bold expiration_date"></span></p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-4">
                                <label>Tipo de tempo</label>
                                <select class="form-control label-animate" name="type">
                                    <option value="">Selecione</option>
                                    <option value="day">Dia</option>
                                    <option value="month">Mês</option>
                                    <option value="year">Ano</option>
                                </select>
                            </div>
                            <div class="form-group col-md-3">
                                <label>Quantidade de tempo</label>
                                <input type="number" class="form-control" name="time">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-around">
                        <button type="button" class="btn btn-secondary col-md-3" data-dismiss="modal"><i class="fa fa-times"></i> Cancelar</button>
                        <button type="submit" class="btn btn-success col-md-3"><i class="fa fa-save"></i> Atualizar</button>
                    </div>
                    <input type="hidden" name="company_id" value="">
                </form>
            </div>
        </div>
    </div>
@stop
