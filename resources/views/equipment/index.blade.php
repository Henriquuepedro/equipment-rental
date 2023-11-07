@extends('adminlte::page')

@section('title', 'Listagem de Equipamentos')

@section('content_header')
    <h1 class="m-0 text-dark">Listagem de Equipamentos</h1>
@stop

@section('css')
@stop

@section('js')
    <script>
        var tableEquipments;
        $(function () {
            tableEquipments = getTable(false);
        });

        const getTable = (stateSave = true) => {
            return $("#tableEquipments").DataTable({
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
                    url: '{{ route('ajax.equipment.fetch') }}',
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

        $(document).on('click', '.btnRemoveEquipment', function (){
            const equipment_id = $(this).attr('equipment-id');
            const equipment_name = $(this).closest('tr').find('td:eq(1)').text();

            Swal.fire({
                title: 'Exclusão de Equipamento',
                html: "Você está prestes a excluir definitivamente o equipamento <br><strong>"+equipment_name+"</strong><br>Deseja continuar?",
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
                        url: "{{ route('ajax.equipment.delete') }}",
                        data: { equipment_id },
                        dataType: 'json',
                        success: response => {
                            $('[data-toggle="tooltip"]').tooltip('dispose')
                            tableEquipments.destroy();
                            $("#tableEquipments tbody").empty();
                            tableEquipments = getTable();
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
                                $(`button[equipment-id="${equipment_id}"]`).trigger('blur');
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
                        <h4 class="card-title no-border">Equipamentos Cadastrados</h4>
                        @if(in_array('EquipmentCreatePost', $permissions))
                        <a href="{{ route('equipment.create') }}" class="mb-3 btn btn-primary col-md-3 btn-rounded btn-fw"><i class="fas fa-plus"></i> Novo Cadastro</a>
                        @endif
                    </div>
                    <table id="tableEquipments" class="table table-bordered">
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
