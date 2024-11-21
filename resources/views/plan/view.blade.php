@extends('adminlte::page')

@section('title', 'Informações do Pagamento')

@section('content_header')
    <h1 class="m-0 text-dark">Informações do Pagamento</h1>
@stop

@section('css')
    <style>

    </style>
@stop

@section('js')
    <script>
        $(function(){
            checkLabelAnimate();
        });

        $(document).on('click', '.copy-input', function() {
            // Seleciona o conteúdo do input
            $(this).closest('.input-group').find('input').select();
            // Copia o conteudo selecionado
            const copy = document.execCommand('copy');

            Toast.fire({
                icon: 'success',
                title: copy ? 'Copiado com sucesso.' : 'Não foi possível copiar.'
            });
        });

        $('#btn_cancel_subscription').on('click', function () {
            const plan_id = $(this).data('plan-id');

            Swal.fire({
                title: 'Cancelamento de assinatura',
                html: "Você está prestes a cancelar a assinatura<br><br>Não será possível reverter a ação.<br><br>Deseja continuar a excluir?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#bbb',
                confirmButtonText: 'Sim, cancelar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        type: 'POST',
                        url: "{{ route('ajax.plan.cancel_subscription') }}",
                        data: { plan_id },
                        dataType: 'json',
                        success: response => {
                            Toast.fire({
                                icon: response.success ? 'success' : 'error',
                                title: response.message
                            })

                            if (response.success) {
                                setTimeout(() => {
                                    location.reload();
                                }, 500);
                            }
                        }, error: e => {
                            console.log(e);
                        },
                        complete: function(xhr) {
                            if (xhr.status === 403) {
                                Toast.fire({
                                    icon: 'error',
                                    title: 'Você não tem permissão para fazer essa operação!'
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
            <div class="row flex-grow">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="header-card-body">
                                <h4 class="card-title">Informações do Pagamento</h4>
                                <p class="card-description"> Visualize todos os dados sobre o pagamento do plano.</p>
                            </div>
                            <div class="col-md-12 no-padding">
                                <div class="row">
                                    <div class="form-group col-md-3">
                                        <label>Forma de Pagamento</label>
                                        <input type="text" class="form-control" name="type_payment" value="{{ getNamePaymentTypeMP($payment) }}" disabled />
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Plano</label>
                                        <input type="text" class="form-control" name="plan" value="{{ $payment->name }}" disabled />
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Data da Solicitação</label>
                                        <input type="text" class="form-control" name="date_requested" value="{{ dateInternationalToDateBrazil($payment->created_at) }}" disabled />
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Valor</label>
                                        <input type="text" class="form-control" name="amount" value="{{ formatMoney($payment->gross_amount, 2, 'R$ ') }}" disabled />
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-5">
                                        <label>Usuário Solicitante</label>
                                        <input type="text" class="form-control" name="user" value="{{ $user->email }}" disabled />
                                    </div>
                                    <div class="form-group col-md-7">
                                        <label>Empresa</label>
                                        <input type="text" class="form-control" name="company" value="{{ $company->name }}" disabled />
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label>Situação Atual</label>
                                        <input type="text" class="form-control" name="status" value="{{ __("mp.$payment->status") }}" disabled />
                                    </div>
                                    <div class="form-group col-md-8">
                                        <label>Situação Atual Detalhada</label>
                                        <input type="text" class="form-control" name="status_detail" value="{{ $payment->status_detail ? __("mp.$payment->status_detail") : __("mp.$payment->status") }}" disabled />
                                    </div>
                                </div>
                                @if ($payment->payment_method_id === 'pix' && $payment->payment_type_id === 'bank_transfer')
                                <div class="row justify-content-center flex-wrap">
                                    <div class="d-flex justify-content-center col-md-12">
                                        <div class="form-group col-md-4">
                                            <label>Pague até</label>
                                            <input type="text" class="form-control" name="pix_date_of_expiration" value="{{ dateInternationalToDateBrazil($payment->date_of_expiration, 'd/m H:i') }}" disabled/>
                                        </div>
                                    </div>
                                    <div class='col-md-12 d-flex justify-content-center'>
                                        <img width="250px" class="mt-2" src="data:image/jpeg;base64,{{ $payment->base64_key_pix }}" alt="QR Code"/>
                                    </div>



                                    <div class="form-group col-md-8 flatpickr mt-2">
                                        <div class="input-group">
                                            <input type="text" class="form-control col-md-10 flatpickr-input bbr-r-0 btr-r-0 pull-left" name="pix_copy_paste" value="{{ $payment->key_pix }}" readonly>
                                            <div class="input-button-calendar col-md-2 no-padding pull-right">
                                                <button type='button' class='input-button btn btn-primary btn-flat copy-input col-md-12 bbr-r-5 btr-r-5'>
                                                    <i class='fas fa-copy'></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @elseif (in_array($payment->payment_type_id, array('credit_card', 'debit_card', 'prepaid_card')))
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label>Parcelas</label>
                                        <input type="text" class="form-control" name="card_installments" value="{{ $payment->installments }}" disabled />
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Bandeira</label>
                                        <input type="text" class="form-control" name="card_payment_method_id" value="{{ $payment->payment_method_id }}" disabled />
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Valor Total Pago</label>
                                        <input type="text" class="form-control" name="card_client_amount" value="{{ formatMoney($payment->client_amount, 2, 'R$ ') }}" disabled />
                                    </div>
                                </div>
                                @elseif ($payment->payment_method_id === 'bolbradesco' && $payment->payment_type_id === 'ticket')
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <a href="{{ $payment->link_billet }}" class="billet_link_billet btn btn-primary col-md-12 mt-4" target="_blank" >Visualizar PDF</a>
                                    </div>
                                    <div class="form-group col-md-4 flatpickr label-animate">
                                        <label>Linha Digitável</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control col-md-9 flatpickr-input bbr-r-0 btr-r-0 pull-left" name="billet_barcode" value="{{ $payment->barcode_billet }}" readonly>
                                            <div class="input-button-calendar col-md-3 no-padding pull-right">
                                                <button type='button' class='input-button btn btn-primary btn-flat copy-input col-md-12 bbr-r-5 btr-r-5'>
                                                    <i class='fas fa-copy'></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Pague até</label>
                                        <input type="text" class="form-control" name="billet_date_of_expiration" value="{{ dateInternationalToDateBrazil($payment->date_of_expiration, 'd/m') }}" disabled />
                                    </div>
                                </div>
                                @endif
                                @if ($payment->is_subscription)
                                    <div class="row d-flex justify-content-center mt-3">
                                        <div class="form-group col-md-4">
                                            <button type="button" class="btn btn-danger col-md-12" id="btn_cancel_subscription" data-plan-id="{{ $payment->id }}">Cancelar assinatura</button>
                                        </div>
                                    </div>
                                @endif
                                @if (count($plan_histories))
                                    <div class="row histories-division">
                                        <div class="col-md-12"><hr/></div>
                                    </div>
                                    <div class="row histories-title">
                                        <div class="col-md-12 text-center">
                                            <h4>Histórico da Transação</h4>
                                        </div>
                                    </div>
                                    <div class="timeline mt-3">
                                        @foreach($plan_histories as $key => $plan_history)
                                            <div class="timeline-wrapper timeline-wrapper-{{ getColorStatusMP($plan_history->status) }} {{ $key % 2 == 0 ? 'timeline-inverted' : '' }}">
                                                <div class="timeline-badge"></div>
                                                <div class="timeline-panel">
                                                    <div class="timeline-heading">
                                                        <h6 class="timeline-title">{{ __('mp.' . $plan_history->status) }}</h6>
                                                    </div>
                                                    <div class="timeline-body">
                                                        @if ($payment->is_subscription)
                                                            @php($exp_observation = explode(':', $plan_history->observation))
                                                            <p>{{ __('mp.' . $exp_observation[0]) . ':' . $exp_observation[1] }}</p>
                                                        @elseif ($plan_history->status_detail)
                                                            <p>{{ __('mp.' . $plan_history->status_detail) }}</p>
                                                        @endif
                                                    </div>
                                                    <div class="timeline-footer d-flex align-items-center">
                                                        <span class="ml-auto font-weight-bold">{{ dateInternationalToDateBrazil($plan_history->created_at, DATETIME_BRAZIL_NO_SECONDS) }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="card mt-2">
                        <div class="card-body d-flex justify-content-between">
                            <a href="{{ route('plan.request') }}" class="btn btn-secondary col-md-3"><i class="fa fa-arrow-left"></i> Voltar</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
