@extends('adminlte::page')

@section('title', 'Listagem de Planos')

@section('content_header')
    <h1 class="m-0 text-dark">Listagem de Planos</h1>
@stop

@section('css')
@stop

@section('js')
    <script>
        let tablePlan;
        $(function () {
            tablePlan = getTable(false);
        });

        const getTable = (stateSave = true) => {

            if (typeof tablePlan !== 'undefined') {
                tablePlan.destroy();
            }

            return $("#tablePlans").DataTable({
                "responsive": true,
                "processing": true,
                "autoWidth": false,
                "serverSide": true,
                "sortable": true,
                "searching": true,
                "stateSave": stateSave,
                "serverMethod": "post",
                "order": [[ 3, 'desc' ]],
                "ajax": {
                    url: '{{ route('ajax.master.plan.fetch') }}',
                    pages: 2,
                    type: 'POST',
                    data: { "_token": $('meta[name="csrf-token"]').attr('content') },
                    error: function(jqXHR, ajaxOptions, thrownError) {
                        console.log(jqXHR, ajaxOptions, thrownError);
                    }
                },
                "initComplete": function( settings, json ) {
                    $('[data-bs-toggle="tooltip"]').tooltip();
                },
                "language": {
                    "url": "https://cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json"
                }
            });
        }

        $('#active').on('change', function(){
            tablePlan.destroy();
            $("#tablePlans tbody").empty();
            tablePlan = getTable();
        });

        $(document).on('click', '.btnCreatePlanGateway', function (){
            const plan_id               = $(this).data('plan-id');
            const discount_subscription = $(this).data('discount-subscription');
            const plan_id_gateway       = $(this).data('plan-id-gateway');
            const plan_name             = $(this).closest('tr').find('td:eq(0)').text();
            const plan_price            = $(this).closest('tr').find('td:eq(1)').text();

            Swal.fire({
                title: `${plan_id_gateway ? 'Atualização' : 'Criação'} de plano no gateway de pagamento`,
                html: `Você está prestes a ${plan_id_gateway ? 'atualizar' : 'criar'} o plano no gateway de pagamento<br>
                        <strong>Atualmente o valor de desconto em percentual para a assinatura é de R$ ${discount_subscription}</strong><br>
                        <strong>Plano: ${plan_name}</strong><br>
                        <strong>Valor do Plano: R$ ${plan_price}</strong><br>
                        <strong>Código do Plano: ${plan_id}</strong><br><br>
                        <h3>Deseja continuar?</h3>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sim, desejo criar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        type: 'POST',
                        url: $('[name="routeCreatePlanGateway"]').val(),
                        data: { plan_id },
                        dataType: 'json',
                        success: response => {
                            tablePlan = getTable();
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
                        <h4 class="card-title no-border">Planos Cadastrados</h4>
                        <a href="{{ route('master.plan.create') }}" class="mb-3 btn btn-primary col-md-3 btn-rounded btn-fw"><i class="fas fa-plus"></i> Novo Plano</a>
                    </div>
                    <table id="tablePlans" class="table">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Valor</th>
                                <th>Quantidade de Equipamentos</th>
                                <th>Usuários Permitidos</th>
                                <th>Quantidade de Meses</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>Nome</th>
                                <th>Valor</th>
                                <th>Quantidade de Equipamentos</th>
                                <th>Usuários Permitidos</th>
                                <th>Quantidade de Meses</th>
                                <th>Ação</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" name="routeCreatePlanGateway" value="{{ route('ajax.master.plan.create_plan_gateway') }}">
@stop
