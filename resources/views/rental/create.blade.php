@extends('adminlte::page')

@section('title', 'Cadastro de Locação')

@section('content_header')
    <h1 class="m-0 text-dark">Cadastro de Locação</h1>
@stop

@section('css')
    <style>
        .wizard > .actions > ul {
            display: flex;
            justify-content: space-between;
        }
        .wizard > .content {
            background: #FFF;
            border: 1px solid #eee;
        }
        .wizard > .content > .body {
            height: unset;
        }
        .show-address {
            display: none;
        }
    </style>
@stop

@section('js')
<script src="{{ asset('assets/vendors/jquery-steps/jquery.steps.min.js') }}"></script>
<script src="{{ asset('assets/js/views/rental/form.js') }}"></script>
<script src="{{ asset('assets/js/views/client/form.js') }}" type="application/javascript"></script>

<script src="{{ asset('assets/vendors/inputmask/jquery.inputmask.bundle.js') }}"></script>
<script>
    $(function() {
        $('.wizard .content').animate({ 'min-height': $('.wizard .content .body:visible').height()+40 }, 500);
        // $('[name="date_withdrawal"], [name="date_delivery"]').mask('00/00/0000 00:00');
        $('[name="date_withdrawal"], [name="date_delivery"]').inputmask();
    });

    // Validar dados
    $("#formCreateRental").validate({
        rules: {

        },
        messages: {

        },
        invalidHandler: function(event, validator) {
            $('html, body').animate({scrollTop:0}, 100);
            let arrErrors = [];
            $.each(validator.errorMap, function (key, val) {
                arrErrors.push(val);
            });
            setTimeout(() => {
                Swal.fire({
                    icon: 'warning',
                    title: 'Atenção',
                    html: '<ol><li>'+arrErrors.join('</li><li>')+'</li></ol>'
                });
            }, 150);
        },
        submitHandler: function(form) {
            $('#formCreateRental [type="submit"]').attr('disabled', true);
            form.submit();
        }
    });
</script>
@include('includes.client.modal-script')
@include('includes.address.modal-script')
@stop

@section('content')
    <div class="row">
        <div class="col-md-12 d-flex align-items-stretch grid-margin">
            <div class="row flex-grow">
                <div class="col-md-12">
                @if ($errors->any())
                    <div class="alert-animate alert-warning">
                        <ol>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ol>
                    </div>
                @endif
                </div>
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body">
                            <form action="{{ route(('rental.insert')) }}" method="POST" enctype="multipart/form-data" id="formCreateRental">
                                <h3>Cliente</h3>
                                <section>
                                    <h6>Cliente e Endereço</h6>
                                    <div class="row">
                                        <div class="form-group col-md-12 label-animate">
                                            @include('includes.client.form')
                                        </div>
                                    </div>
                                    @include('includes.address.form')
                                    <div class="row">
                                        <div class="form-group col-md-12 mt-2">
                                            <div class="alert alert-warning alert-mark-map text-center display-none">O endereço selecionado não foi confirmado no mapa, isso pode acarretar uma má precisão da localização.</div>
                                        </div>
                                    </div>
                                </section>
                                <h3>Datas</h3>
                                <section>
                                    <h6>Datas</h6>
                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            <label>Data Prevista de Entrega</label>
                                            <input type="text" name="date_delivery" class="form-control" value="{{ date('d/m/Y H:i') }}" data-inputmask="'alias': 'datetime'" data-inputmask-inputformat="dd/mm/yyyy HH:MM" im-insert="false">
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>Data Prevista de Retirada</label>
                                            <input type="text" name="date_withdrawal" class="form-control" value="{{ date('d/m/Y H:i', strtotime('+1 minute', time())) }}" data-inputmask="'alias': 'datetime'" data-inputmask-inputformat="dd/mm/yyyy HH:MM" im-insert="false">
                                        </div>
                                    </div>
                                </section>
                                <h3>Equipamento</h3>
                                <section>
                                    <h6>Equipamento</h6>
                                </section>
                                <h3>Finish</h3>
                                <section>
                                    <h6>Finish</h6>
                                </section>
                                {{ csrf_field() }}
                            </form>
                        </div>
                    </div>
{{--                    <div class="card">--}}
{{--                        <div class="card-body d-flex justify-content-between">--}}
{{--                            <a href="{{ route('driver.index') }}" class="btn btn-secondary col-md-3"><i class="fa fa-arrow-left"></i> Cancelar</a>--}}
{{--                            <button type="submit" class="btn btn-success col-md-3"><i class="fa fa-save"></i> Cadastrar</button>--}}
{{--                        </div>--}}
{{--                    </div>--}}
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <p>Em andamento ...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('includes.client.modal-create')
@stop
