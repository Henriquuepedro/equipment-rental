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
        .flatpickr a.input-button{
            height: calc(1.5em + 0.75rem + 4px);
            width: 50%;
            text-align: center;
            padding-top: 13%;
            cursor: pointer;
        }
        .flatpickr a.input-button:last-child{
            border-bottom-right-radius: 5px;
            border-top-right-radius: 5px;
        }
        [name="date_withdrawal"], [name="date_delivery"] {
            border-bottom-right-radius: 0 !important;
            border-top-right-radius: 0 !important;;
        }
    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/material_blue.css">
@stop

@section('js')
<script src="{{ asset('assets/vendors/jquery-steps/jquery.steps.min.js') }}"></script>
<script src="{{ asset('assets/js/views/rental/form.js') }}"></script>
<script src="{{ asset('assets/js/views/client/form.js') }}" type="application/javascript"></script>
<script src="{{ asset('assets/vendors/inputmask/jquery.inputmask.bundle.js') }}"></script>
<script src="{{ asset('assets/vendors/moment/moment.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr@4.6.6/dist/l10n/pt.js"></script>

<script>
    $(function() {
        $('.wizard .content').animate({ 'min-height': $('.wizard .content .body:visible').height()+40 }, 500);
        // $('[name="date_withdrawal"], [name="date_delivery"]').mask('00/00/0000 00:00');
        $('[name="date_withdrawal"], [name="date_delivery"]').inputmask();
        $('.flatpickr').flatpickr({
            enableTime: true,
            dateFormat: "d/m/Y H:i",
            time_24hr: true,
            wrap: true,
            clickOpens: false,
            allowInput: true,
            locale: "pt",
            onClose: function(selectedDates, dateStr, instance){
                checkLabelAnimate();
            }
        });
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
                                            <div class="alert alert-warning alert-mark-map text-center display-none">O endereço selecionado não foi confirmado no mapa no cadastro do cliente, isso pode acarretar uma má precisão da localização.</div>
                                        </div>
                                    </div>
                                </section>
                                <h3>Datas</h3>
                                <section>
                                    <h6>Datas</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group flatpickr">
                                                <label>Data Prevista de Entrega</label>
                                                <input type="text" name="date_delivery" class="form-control col-md-9 pull-left" value="{{ date('d/m/Y H:i') }}" data-inputmask="'alias': 'datetime'" data-inputmask-inputformat="dd/mm/yyyy HH:MM" im-insert="false" data-input>
                                                <div class="input-button-calendar col-md-3 pull-right no-padding">

                                                    <a class="input-button pull-left btn-info" title="toggle" data-toggle>
                                                        <i class="fa fa-calendar text-white"></i>
                                                    </a>

                                                    <a class="input-button pull-right btn-info" title="clear" data-clear>
                                                        <i class="fa fa-times text-white"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group flatpickr">
                                                <label>Data Prevista de Retirada</label>
                                                <input type="text" name="date_withdrawal" class="form-control col-md-9 pull-left" value="{{ date('d/m/Y H:i', strtotime('+1 minute', time())) }}" data-inputmask="'alias': 'datetime'" data-inputmask-inputformat="dd/mm/yyyy HH:MM" im-insert="false" data-input>
                                                <div class="input-button-calendar col-md-3 pull-right no-padding">

                                                    <a class="input-button pull-left btn-info" title="toggle" data-toggle>
                                                        <i class="fa fa-calendar text-white"></i>
                                                    </a>

                                                    <a class="input-button pull-right btn-info" title="clear" data-clear>
                                                        <i class="fa fa-times text-white"></i>
                                                    </a>
                                                </div>
                                            </div>
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
    <div class="modal fade" tabindex="-1" role="dialog" id="confirmAddressRental">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Endereço no Mapa</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 form-group text-center mb-2">
                            <button type="button" class="btn btn-primary" id="updateLocationMapRental"><i class="fas fa-search"></i> Buscar endereço do formulário</button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 form-group">
                            <div id="mapRental" style="height: 400px"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary col-md-3" data-dismiss="modal">Confirmar</button>
                </div>
            </div>
        </div>
    </div>

@stop
