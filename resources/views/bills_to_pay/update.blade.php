@extends('adminlte::page')

@section('title', 'Cadastro de Compra')

@section('content_header')
    <h1 class="m-0 text-dark">Cadastro de Compra</h1>
@stop

@section('css')
    <style>
        #descriptionDiv {
            border-top-right-radius: 0;
            border-top-left-radius: 0;
        }
    </style>
@stop

@section('js')
<script src="{{ asset('assets/js/views/bill_to_pay/form.js') }}" type="application/javascript"></script>
<script>

    $(function(){
        getPayments($('#bill_id').val(), function(response){
            let due_day, due_date, due_value;

            $(response).each(async function (k, payment) {
                due_day      = calculateDays(getTodayDateEn(false), payment.due_date);
                due_date     = payment.due_date;
                due_value    = numberToReal(payment.due_value);


                $('#parcels').show().append(
                    createParcel(due_day, due_date, due_value)
                ).find('.form-group').slideDown(500).find('[name="value_parcel[]"]').maskMoney({thousands: '.', decimal: ',', allowZero: true}).closest('.parcel').find('.remove-payment').tooltip();
            });
        });
    });

    $(document).on('click', '#btnRemoveBill', function (){
        Swal.fire({
            title: 'Exclusão da Compra',
            html: "Você está prestes a excluir definitivamente essa compra.<br>Deseja continuar?",
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
                    type: 'DELETE',
                    url: "{{ route('ajax.bills_to_pay.delete', ['id' => $bill_to_pay->id]) }}",
                    dataType: 'json',
                    success: response => {
                        console.log(response);

                        Swal.fire({
                            title: 'Concluído',
                            html: `<h4>${response.message}</h4>`,
                            icon: 'success',
                            showCancelButton: false,
                            confirmButtonColor: '#2196f3',
                            cancelButtonColor: '#15b67d',
                            confirmButtonText: 'Voltar para a listagem',
                            reverseButtons: true,
                            allowOutsideClick: false
                        }).then(() => {
                            window.location.href = $('#back_page').attr('href');
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
                            $(`button[data-client-id="${client_id}"]`).trigger('blur');
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

@include('includes.provider.modal-script')

    @if(in_array('BillsToPayCreatePost', $permissions))    <script src="{{ asset('assets/js/views/provider/form.js') }}" type="application/javascript"></script>    @endif

@stop

@section('content')
    <div class="row">
        <div class="col-md-12 grid-margin">
            <div class="row flex-grow">
                <div class="col-12">
                    @if ($errors->any())
                    <div class="alert alert-animate alert-warning">
                        <ol>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ol>
                    </div>
                    @endif
                    <form action="{{ route('ajax.bills_to_pay.update-bill-to-pay', ['id' => $bill_to_pay->id]) }}" method="POST" enctype="multipart/form-data" id="formCreateBillsToPay">
                        <div class="card">
                            <div class="card-body">
                                <div class="header-card-body">
                                    <h4 class="card-title">Dados da Compra</h4>
                                    <p class="card-description"> Preencha o formulário abaixo com as informações da nova compra </p>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-12 label-animate">
                                        @include('includes.provider.form')
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-12 mt-3">
                                        <h5>Descrição</h5>
                                        <div id="descriptionDiv" class="quill-container">{!! $bill_to_pay->observation !!}</div>
                                        <textarea type="hidden" class="d-none" name="description" id="description"></textarea>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label for="value">Valor</label>
                                        <input type="text" class="form-control" id="value" name="value" autocomplete="nope" value="{{ old('value', formatMoney($bill_to_pay->gross_value)) }}">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Forma de Pagamento</label>
                                        <select class="form-control" name="form_payment" required></select>
                                    </div>
                                    <div class="form-group automatic_parcel_distribution_parent col-md-4">
                                        <div class="switch d-flex mt-4">
                                            <input type="checkbox" class="check-style check-xs" name="automatic_parcel_distribution" id="automatic_parcel_distribution" {{ $bill_to_pay->automatic_parcel_distribution ? 'checked' : '' }}>
                                            <label for="automatic_parcel_distribution" class="check-style check-xs"></label>&nbsp;Distribuir Valores
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12 d-flex justify-content-end mt-4">
                                        <div class="form-group">
                                            <button type="button" class="btn btn-primary" id="add_parcel"><i class="fa fa-plus"></i> Nova Parcela</button>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group mt-1">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div class="input-group col-md-12 no-padding">
                                                    <span class="col-md-3 text-center">Dia do vencimento</span>
                                                    <span class="col-md-4 text-center">Data do vencimento</span>
                                                    <span class="col-md-1 no-padding">&nbsp;</span>
                                                    <span class="col-md-3 no-border-radius text-center">Valor</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12" id="parcels"></div>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body d-flex justify-content-between">
                                <a href="{{ route('bills_to_pay.index') }}" class="btn btn-secondary col-md-3" id="back_page"><i class="fa fa-arrow-left"></i> Cancelar</a>
                                <button type="button" class="btn btn-danger col-md-3" id="btnRemoveBill"><i class="fa fa-trash"></i> Excluir Compra</button>
                                <button type="submit" class="btn btn-success col-md-3"><i class="fa fa-save"></i> Alterar</button>
                            </div>
                        </div>
                        <input type="hidden" name="confirm_update_payment" value="0">
                        {{ csrf_field() }}
                    </form>
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" id="provider_id" value="{{ $bill_to_pay->provider_id }}">
    <input type="hidden" id="form_payment" value="{{ $bill_to_pay->form_payment }}">
    <input type="hidden" id="bill_id" value="{{ $bill_to_pay->id }}">
    @if(in_array('BillsToPayCreatePost', $permissions)) @include('includes.provider.modal-create') @endif
@stop
