@extends('adminlte::page')

@section('title', 'Listagem de Equipamentos')

@section('content_header')
    <h1 class="m-0 text-dark">Listagem de Equipamentos</h1>
@stop

@section('css')
@stop

@section('js')
    <script>
        var tableEquipaments;
        $(function () {
            tableEquipaments = getTable();
        });

        const getTable = () => {
            return $("#tableEquipaments").DataTable({
                "responsive": true,
                "processing": true,
                "autoWidth": false,
                "serverSide": true,
                "sortable": true,
                "searching": true,
                "serverMethod": "post",
                "order": [[ 0, 'desc' ]],
                "ajax": $.fn.dataTable.pipeline({
                    url: '{{ route('ajax.equipament.fetch') }}',
                    pages: 2,
                    type: 'POST',
                    data: { "_token": $('meta[name="csrf-token"]').attr('content') },
                }),
                "initComplete": function( settings, json ) {
                    $('[data-toggle="tooltip"]').tooltip();
                },
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Portuguese-Brasil.json"
                }
            });
        }

        $(document).on('click', '.btnRemoveEquipament', function (){
            const equipament_id = $(this).attr('equipament-id');
            const equipament_name = $(this).closest('tr').find('td:eq(1)').text();

            Swal.fire({
                title: 'Exclusão de Equipamento',
                html: "Você está prestes a excluir definitivamente o equipamento <br><strong>"+equipament_name+"</strong><br>Deseja continuar?",
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
                        url: "{{ route('ajax.equipament.delete') }}",
                        data: { equipament_id },
                        dataType: 'json',
                        success: response => {
                            tableEquipaments.destroy();
                            $("#tableEquipaments tbody").empty();
                            tableEquipaments = getTable();
                            Toast.fire({
                                icon: response.success ? 'success' : 'error',
                                title: response.message
                            })
                        }, error: e => {
                            console.log(e);
                        },
                        complete: function(xhr) {
                            if (xhr.status === 403) {
                                Toast.fire({
                                    icon: 'error',
                                    title: 'Você não tem permissão para fazer essa operação!'
                                });
                                $(`button[equipament-id="${equipament_id}"]`).trigger('blur');
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
                <div class="alert alert-success"><i class="fa fa-check-circle"></i> {{session('success')}}</div>
            @endif
            @if(session('warning'))
                <div class="alert alert-danger mt-2">{{session('warning')}}</div>
            @endif
            <div class="card">
                <div class="card-body">
                    <div class="header-card-body justify-content-between flex-wrap">
                        <h4 class="card-title no-border">Equipamentos Cadastrados</h4>
                        @if(in_array('EquipamentCreatePost', $permissions))
                        <a href="{{ route('equipament.create') }}" class="mb-3 btn btn-primary col-md-3 btn-rounded btn-fw"><i class="fas fa-plus"></i> Novo Cadastro</a>
                        @endif
                    </div>
                    <table id="tableEquipaments" class="table table-bordered">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Equipamento</th>
                            <th>Referência</th>
                            <th>Estoque</th>
                            <th>Ação</th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
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
@stop
