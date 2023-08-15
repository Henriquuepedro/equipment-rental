@extends('adminlte::page')

@section('title', 'Listagem de Motoristas')

@section('content_header')
    <h1 class="m-0 text-dark">Listagem de Motoristas</h1>
@stop

@section('css')
@stop

@section('js')
    <script>
        var tableDriver;
        $(function () {
            tableDriver = getTable(false);
        });

        const getTable = (stateSave = true) => {
            return $("#tableDrivers").DataTable({
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
                    url: '{{ route('ajax.driver.fetch') }}',
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
                    "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Portuguese-Brasil.json"
                }
            });
        }

        $(document).on('click', '.btnRemoveDriver', function (){
            const driver_id = $(this).attr('driver-id');
            const driver_name = $(this).closest('tr').find('td:eq(1)').text();

            Swal.fire({
                title: 'Exclusão de Motorista',
                html: "Você está prestes a excluir definitivamente o motorista <br><strong>"+driver_name+"</strong><br>Deseja continuar?",
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
                        url: "{{ route('ajax.driver.delete') }}",
                        data: { driver_id },
                        dataType: 'json',
                        success: response => {
                            $('[data-toggle="tooltip"]').tooltip('dispose')
                            tableDriver.destroy();
                            $("#tableDrivers tbody").empty();
                            tableDriver = getTable();
                            Toast.fire({
                                icon: response.success ? 'success' : 'error',
                                title: response.message
                            })
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
                                $(`button[driver-id="${driver_id}"]`).trigger('blur');
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
        })
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
                        <h4 class="card-title no-border">Motoristas Cadastrados</h4>
                        @if(in_array('DriverCreatePost', $permissions))
                        <a href="{{ route('driver.create') }}" class="mb-3 btn btn-primary col-md-3 btn-rounded btn-fw"><i class="fas fa-plus"></i> Novo Cadastro</a>
                        @endif
                    </div>
                    <table id="tableDrivers" class="table table-bordered">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Nome</th>
                            <th>CPF</th>
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
                                <th>CPF</th>
                                <th>Telefone</th>
                                <th>Ação</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop
