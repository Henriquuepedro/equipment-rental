@extends('adminlte::page')

@section('title', 'Listagem de Locações')

@section('content_header')
    <h1 class="m-0 text-dark">Listagem de Locações</h1>
@stop

@section('css')
    <style>
        #tableRentals .badge.badge-lg {
            padding: 0.2rem 0.3rem;
        }
        .tickets-tab-switch .nav-item .nav-link.active .badge {
            background: #fff;
            color: #2196f3;
        }
    </style>
@stop

@section('js')
    <script>
        var tableRental;
        $(function () {
            setTabRental();

            moment.locale('pt-br');
            $('input[name="intervalDates"]').daterangepicker({
                locale: {
                    format: 'DD/MM/YYYY'
                }
            });
        });

        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            getTable(e.target.id.replace('-tab',''), false);
        });

        $('[name="intervalDates"], [name="clients"]').change(function(){
            getTable($('[data-toggle="tab"].active').attr('id').replace('-tab',''), false);
        });

        const setTabRental = () => {
            const url = window.location.href;
            const splitUrl = url.split('#');
            let tab = 'deliver';

            if (splitUrl.length === 2)
                tab = splitUrl[1];

            $(`#${tab}-tab`).tab('show');
            getTable(tab, false);
        }

        const loadCountsTabRental = () => {
            $('.nav-tabs.tickets-tab-switch').each(function(){
                $(this).find('li a .badge').html('<i class="fa fa-spin fa-spinner" style="margin-right: 0px"></i>');
            })
        }

        const disabledLoadData = () => {
            $('a[data-toggle="tab"], input[name="intervalDates"], select[name="clients"]').prop('disabled', true);
        }
        const enabledLoadData = () => {
            $('a[data-toggle="tab"], input[name="intervalDates"], select[name="clients"]').prop('disabled', false);
        }

        const getCountsTabRentals = () => {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'POST',
                url: "{{ route('ajax.rental.get-qty-type-rentals') }}",
                dataType: 'json',
                success: response => {

                    $.each(response, function( index, value ) {
                        $(`#${index}-tab .badge`).text(value)
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
                        $(`button[rental-id="${rental_id}"]`).trigger('blur');
                    }
                }
            });
        }

        const getTable = (typeRentals, stateSave = true) => {

            loadCountsTabRental();
            disabledLoadData();

            $('[data-toggle="tooltip"]').tooltip('dispose');

            if (typeof tableRental !== 'undefined') {
                tableRental.destroy();

                $("#tableRentals tbody").empty();
            }

            getCountsTabRentals();

            tableRental = $("#tableRentals").DataTable({
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
                    url: '{{ route('ajax.rental.fetch') }}',
                    pages: 2,
                    type: 'POST',
                    data: {
                        "_token": $('meta[name="csrf-token"]').attr('content'),
                        type: typeRentals,
                        intervalDate: $('[name="intervalDates"]').val(),
                        client: $('[name="clients"]').val()
                    },
                    error: function(jqXHR, ajaxOptions, thrownError) {
                        console.log(jqXHR, ajaxOptions, thrownError);
                    }, complete: () => {
                        enabledLoadData();
                    }
                },
                "initComplete": function( settings, json ) {
                    enabledLoadData();
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
                            getTable();
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
    <div class="row profile-page">
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
                    <div class="row">
                        <div class="col-md-9 form-group">
                            <label>Cliente</label>
                            <select class="form-control" name="clients">
                                <option value="0">Todos</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}">{{ $client->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Data de Criação</label>
                            <input type="text" name="intervalDates" class="form-control" value="{{ $settings['intervalDates']['start'] . ' - ' . $settings['intervalDates']['finish'] }}" />
                        </div>
                    </div>
                    <div class="nav-scroller mt-3">
                        <ul class="nav nav-tabs tickets-tab-switch" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="deliver-tab" data-toggle="tab" href="#deliver" role="tab" aria-controls="deliver" aria-selected="true">Para Entregar<div class="badge">13</div></a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="withdraw-tab" data-toggle="tab" href="#withdraw" role="tab" aria-controls="withdraw" aria-selected="false">Para Retirar<div class="badge">50 </div></a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="finished-tab" data-toggle="tab" href="#finished" role="tab" aria-controls="finished" aria-selected="false">Finalizados<div class="badge">29 </div>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="tab-content tab-content-basic">
                        <div class="tab-pane fade show active" id="deliver" role="tabpanel" aria-labelledby="deliver">

                        </div>
                        <div class="tab-pane fade" id="withdraw" role="tabpanel" aria-labelledby="withdraw">

                        </div>
                        <div class="tab-pane fade" id="finished" role="tabpanel" aria-labelledby="finished">

                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-12">
                            <table id="tableRentals" class="table table-bordered mt-2">
                                <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Cliente/Endereço</th>
                                    <th>Criado Em</th>
                                    <th>Ação</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                                <tfoot>
                                <tr>
                                    <th>Código</th>
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
        </div>
    </div>
@stop
