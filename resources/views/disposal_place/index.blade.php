@extends('adminlte::page')

@section('title', 'Listagem de Locais de descarte')

@section('content_header')
    <h1 class="m-0 text-dark">Listagem de Locais de descarte</h1>
@stop

@section('css')
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
@stop

@section('js')
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js" type="application/javascript"></script>
    <script src="//cdn.datatables.net/plug-ins/1.13.7/api/processing().js" type="application/javascript"></script>
    <script>
        var tableDisposalPlace;
        $(function () {
            tableDisposalPlace = getTableDisposalPlace(false);
        });

        const getTableDisposalPlace = (stateSave = true) => {

            const active = $('#active').val();

            return getTableList(
                '{{ route('ajax.disposal_place.fetch') }}',
                { active },
                'tableDisposalPlaces',
                stateSave,
                [ 0, 'desc' ],
                'POST',
                () => {},
                () => {
                    $('[data-bs-toggle="tooltip"]').tooltip();
                }
            );
        }

        $(document).on('click', '.btnRemoveDisposalPlace', function (){
            const disposal_place_id = $(this).data('disposal-place-id');
            const disposal_place_name = $(this).closest('tr').find('td:eq(1)').text();

            Swal.fire({
                title: 'Exclusão do local de descarte',
                html: "Você está prestes a excluir definitivamente o local de descarte <br><strong>"+disposal_place_name+"</strong><br>Deseja continuar?",
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
                        url: "{{ route('ajax.disposal_place.delete') }}",
                        data: { disposal_place_id },
                        dataType: 'json',
                        success: response => {
                            $('[data-bs-toggle="tooltip"]').tooltip('dispose')
                            tableDisposalPlace.destroy();
                            $("#tableDisposalPlaces tbody").empty();
                            tableDisposalPlace = getTableDisposalPlace();
                            Toast.fire({
                                icon: response.success ? 'success' : 'error',
                                title: response.message
                            });
                        }, error: e => {
                            if (e.status !== 403 && e.status !== 422) {
                                console.log(e);
                            }
                        },
                        complete: function(xhr) {
                            if (xhr.status === 403) {
                                Toast.fire({
                                    icon: 'error',
                                    title: 'Você não tem permissão para fazer essa operação!'
                                });
                                $(`button[data-disposal-place-id="${disposal_place_id}"]`).trigger('blur');
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
            tableDisposalPlace.destroy();
            $("#tableDisposalPlaces tbody").empty();
            tableDisposalPlace = getTableDisposalPlace();
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
                        <h4 class="card-title no-border">Locais de descarte cadastrados</h4>
                        @if(in_array('DisposalPlaceCreatePost', $permissions))
                        <a href="{{ route('disposal_place.create') }}" class="mb-3 btn btn-primary col-md-3 btn-rounded btn-fw"><i class="fas fa-plus"></i> Novo Cadastro</a>
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
                    <table id="tableDisposalPlaces" class="table">
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
@stop
