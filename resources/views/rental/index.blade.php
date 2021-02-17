@extends('adminlte::page')

@section('title', 'Listagem de Locações')

@section('content_header')
    <h1 class="m-0 text-dark">Listagem de Locações</h1>
@stop

@section('css')
@stop

@section('js')
    <script>
        var tableRental;
        $(function () {
            tableRental = getTable();
        });

        const getTable = () => {
            return $("#tableRentals").DataTable({
                "responsive": true,
                "processing": true,
                "autoWidth": false,
                "serverSide": true,
                "sortable": true,
                "searching": true,
                "serverMethod": "post",
                "order": [[ 0, 'desc' ]],
                "ajax": {
                    url: '{{ route('ajax.rental.fetch') }}',
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

        $(document).on('click', '.btnRemoveRental', function (){
            const rental_id = $(this).attr('rental-id');
            const rental_name = $(this).closest('tr').find('td:eq(1)').html();

            Swal.fire({
                title: 'Exclusão de Veículo',
                html: "Você está prestes a excluir definitivamente o veículo <br><strong>"+rental_name+"</strong><br>Deseja continuar?",
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
                        url: "{{ route('ajax.rental.delete') }}",
                        data: { rental_id },
                        dataType: 'json',
                        success: response => {
                            $('[data-toggle="tooltip"]').tooltip('dispose')
                            tableRental.destroy();
                            $("#tableRentals tbody").empty();
                            tableRental = getTable();
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
                                $(`button[rental-id="${rental_id}"]`).trigger('blur');
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
                <div class="alert-animate alert-success"><i class="fa fa-check-circle"></i> {{session('success')}}</div>
            @endif
            @if(session('warning'))
                <div class="alert-animate alert-danger mt-2">{{session('warning')}}</div>
            @endif
            <div class="card">
                <div class="card-body">
                    <div class="header-card-body justify-content-between flex-wrap">
                        <h4 class="card-title no-border">Locações Realizadas</h4>
                        @if(in_array('RentalCreatePost', $permissions))
                        <a href="{{ route('rental.create') }}" class="mb-3 btn btn-primary col-md-3 btn-rounded btn-fw"><i class="fas fa-plus"></i> Nova Locação</a>
                        @endif
                    </div>
                    <table id="tableRentals" class="table table-bordered">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Cliente/Endereço</th>
                            <th>Criado Em</th>
                            <th>Ação</th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                        <tfoot>
                        <tr>
                            <th>#</th>
                            <th>Cliente/Endereço</th>
                            <th>Criado Em</th>
                            <th>Ação</th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop
