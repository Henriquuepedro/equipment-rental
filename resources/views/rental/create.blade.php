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
        .input-group-append.btn-primary{
            background: #2196f3;
            cursor: pointer;
        }
        .list-equipament .equipament {
            cursor: pointer;
        }
        .list-equipament .equipament:hover {
            background: #f5f5f5;
        }
        .wizard > .content > .body {
            position: unset;
        }
        .accordion .card .card-header a{
            background: #2196f3 !important;
        }
        .remove-equipament i {
            cursor: pointer;
        }
        .accordion.accordion-multiple-filled .card .card-header a:last-child {
            padding-left: 1rem;
            padding-right: 1rem;
            overflow: unset;
        }
        i.fa.input-group-text.text-white {
            font-size: 15px;
        }
        .stepRental.body {
            width: 100% !important;
        }
        .content-equipament {
            max-height: 300px;
        }

        .content-equipament::-webkit-scrollbar-track
        {
            -webkit-box-shadow: inset 0 0 6px #2196f3;
            border-radius: 10px;
            background-color: #F5F5F5;
        }

        .content-equipament::-webkit-scrollbar
        {
            width: 12px;
            background-color: #F5F5F5;
        }

        .content-equipament::-webkit-scrollbar-thumb
        {
            border-radius: 10px;
            -webkit-box-shadow: inset 0 0 6px #2196f3;
            background-color: #52a4e5;
        }
    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/material_blue.css">
@stop

@section('js')
<script src="{{ asset('assets/vendors/jquery-steps/jquery.steps.min.js') }}" type="application/javascript"></script>
<script src="{{ asset('assets/js/views/rental/form.js') }}" type="application/javascript"></script>
<script src="{{ asset('assets/js/views/client/form.js') }}" type="application/javascript"></script>
<script src="{{ asset('assets/js/views/equipament/form.js') }}" type="application/javascript"></script>
<script src="{{ asset('assets/vendors/inputmask/jquery.inputmask.bundle.js') }}" type="application/javascript"></script>
<script src="{{ asset('assets/vendors/moment/moment.min.js') }}" type="application/javascript"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr" type="application/javascript"></script>
<script src="https://npmcdn.com/flatpickr@4.6.6/dist/l10n/pt.js" type="application/javascript"></script>

<script>
    var searchEquipamentOld = '';

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

    $('#searchEquipament').on('blur keyup', function (e){

        if(e.keyCode !== 13 && e.type === 'keyup') return false;

        const searchEquipament = $(this).val();
        let equipamentInUse = [];

        if (searchEquipament === searchEquipamentOld) return false;

        $('#equipaments-selected .card-header').each(function(){
            equipamentInUse.push(parseInt($(this).attr('id-equipament')));
        });

        $('table.list-equipament tbody').empty();

        searchEquipamentOld = searchEquipament;

        $('table.list-equipament tbody').empty();

        if (searchEquipament === '') {
            equipamentMessageDefault('<i class="fas fa-search"></i> Pesquise por um equipamento');
            return false;
        }

        equipamentMessageDefault('<i class="fas fa-spinner fa-spin"></i> Carregando equipamentos ...');

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'POST',
            url: "{{ route('ajax.equipament.get-equipaments') }}",
            data: { searchEquipament, equipamentInUse },
            success: response => {

                $('table.list-equipament tbody').empty();

                if (!response.length) {
                    equipamentMessageDefault('<i class="fas fa-surprise"></i> Nenhum equipamento encontrado');
                    return false;
                }

                let badgeStock = '';
                $.each(response, function (key, val) {
                    badgeStock = val.stock <= 0 ? 'danger' : 'primary';
                    $('table.list-equipament tbody').append(`
                        <tr class="equipament" id-equipament="${val.id}">
                            <td class="text-left"><h6 class="mb-1 text-left">${val.name}</h6></td>
                            <td><div class="badge badge-pill badge-lg badge-info">${val.reference}</div></td>
                            <td><div class="badge badge-pill badge-lg badge-${badgeStock}">${val.stock} un</div></td>
                            <td class="text-right">
                                <button type="button" class="badge badge-lg badge-pill badge-success">
                                    <i class="fa fa-plus"></i>
                                </button>
                            </td>
                        </tr>
                    `);
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
                }
            }
        });
    });

    $('#cleanSearchEquipament').on('click', function (){
        $('#searchEquipament').val('').trigger('blur');
    });

    $('table.list-equipament').on('click', '.equipament', function(){
        const idEquipament = $(this).attr('id-equipament');

        $(`.equipament[id-equipament="${idEquipament}"]`).empty().toggleClass('equipament load-equipament').append('<td colspan="4" class="text-center"><i class="fa fa-spinner fa-spin"></i> Carregando ...</td>')

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'POST',
            url: "{{ route('ajax.equipament.get-equipament') }}",
            data: { idEquipament, validStock: true },
            success: response => {

                if (!response.success) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Atenção',
                        html: `<ol><li>${response.data}</li></ol>`
                    });
                    searchEquipamentOld = '';
                    $('#searchEquipament').trigger('blur');
                    return false;
                }

                response = response.data;

                $('#equipaments-selected').append(`
                    <div class="card">
                        <div class="card-header" role="tab" id="headingEquipament-${response.id}" id-equipament="${response.id}">
                            <h5 class="mb-0 d-flex align-items-center">
                                <a class="collapsed pull-left w-100" data-toggle="collapse" href="#collapseEquipament-${response.id}" aria-expanded="false" aria-controls="collapseEquipament-${response.id}">
                                    ${response.name}
                                </a>
                                <a class="remove-equipament pull-right"><i class="fa fa-trash"></i></a>
                            </h5>
                        </div>
                        <div id="collapseEquipament-${response.id}" class="collapse" role="tabpanel" aria-labelledby="headingEquipament-${response.id}" data-parent="#equipaments-selected">
                            <div class="card-body">
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label>Referência</label>
                                        <input type="text" class="form-control" value="${response.reference}" name="reference_equipament" disabled>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Quantidade</label>
                                        <input type="tel" class="form-control" value="0" name="stock_equipament" max-stock="${response.stock}">
                                        <small class="text-danger font-weight-bold stock_available">Disponível: ${response.stock}</small>
                                    </div>
                                    <div class="form-group col-md-5">
                                        <label>Resíduo</label>
                                        <select class="form-control">
                                            <option>Selecione ...</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label>Veículo</label>
                                        <select class="form-control">
                                            <option>Selecione ...</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Motorista</label>
                                        <select class="form-control">
                                            <option>Selecione ...</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-12 mt-2">
                                        <button type="button" class="btn btn-primary pull-right hideEquipament" id-equipament="${response.id}"><i class="fa fa-angle-up"></i> Ocultar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `);
                $(`.load-equipament[id-equipament="${idEquipament}"]`).hide(300);
                showSeparatorEquipamentSelected();
                setTimeout(() => {
                    $(`.load-equipament[id-equipament="${idEquipament}"]`).remove();

                    if (!$(`.list-equipament tbody tr`).length) {
                        equipamentMessageDefault('<i class="fas fa-surprise"></i> Nenhum equipamento encontrado');
                    }
                    checkLabelAnimate();
                    $(`#collapseEquipament-${idEquipament}`).collapse('show').find('[name="stock_equipament"]').mask('0#');
                }, 350);
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
    });

    $(document).on('click', '.remove-equipament i', function (){
        $(this).closest('.card').slideUp(500);
        setTimeout(() => {
            $(this).closest('.card').remove();
            searchEquipamentOld = '';
            $('#searchEquipament').trigger('blur');
            showSeparatorEquipamentSelected();
        }, 550);
    });

    $(document).on('click', '.hideEquipament', function (){
        const idEquipament = parseInt($(this).attr('id-equipament'));
        console.log(idEquipament);
        $(`#collapseEquipament-${idEquipament}`).collapse('hide');
    });

    $(document).on('blur change', '[name="stock_equipament"]', function (){
        const maxStock      = parseInt($(this).attr('max-stock'));
        const stock         = parseInt($(this).val());
        const idEquipament  = parseInt($(this).closest('.card').find('.card-header').attr('id-equipament'));

        if (stock > maxStock) {
            Toast.fire({
                icon: 'error',
                title: `A quantidade não pode ser superior a ${maxStock} un.`
            });
            $(this).val(maxStock);
            console.log(idEquipament);
            setTimeout(() => {
                $(`#collapseEquipament-${idEquipament}`).collapse('show');
                $(this).focus();
            }, 550);
        }
    });

    const equipamentMessageDefault = message => {
        $('table.list-equipament tbody').append(`
            <tr>
                <td class="text-left"><h6 class="text-center">${message}</h6></td>
            </tr>
        `);
    }

    const showSeparatorEquipamentSelected = () => {
        if ($('#equipaments-selected div').length)
            $('.equipaments-selected hr.separator-dashed').slideDown(300);
        else
            $('.equipaments-selected hr.separator-dashed').slideUp(300);
    }

</script>
@include('includes.client.modal-script')
@include('includes.address.modal-script')
@include('includes.equipament.modal-script')
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
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <form action="{{ route(('rental.insert')) }}" method="POST" enctype="multipart/form-data" id="formCreateRental" class="pb-2">
                                <h3>Tipo de Locação</h3>
                                <div class="stepRental">
                                    <h6>Tipo de Locação <i class="fa fa-info-circle" data-toggle="tooltip" title="Defina se haverá ou não cobrança para essa locação."></i></h6>
                                    <div class="row">
                                        <div class="d-flex justify-content-around col-md-12">
                                            <div class="form-radio form-radio-flat">
                                                <label class="form-check-label">
                                                    <input type="radio" class="form-check-input" name="type_rental" value="0" @if(old('type_person') === '0') checked @endif> Com Cobrança <i class="input-helper"></i>
                                                </label>
                                            </div>
                                            <div class="form-radio form-radio-flat">
                                                <label class="form-check-label">
                                                    <input type="radio" class="form-check-input" name="type_rental" value="1" @if(old('type_person') === '1') checked @endif> Sem Cobrança <i class="input-helper"></i>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <h3>Cliente</h3>
                                <div class="stepRental">
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
                                </div>
                                <h3>Datas</h3>
                                <div class="stepRental">
                                    <h6>Datas</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group flatpickr">
                                                <label>Data Prevista de Entrega</label>
                                                <input type="text" name="date_delivery" class="form-control col-md-9 pull-left" value="{{ date('d/m/Y H:i') }}" data-inputmask="'alias': 'datetime'" data-inputmask-inputformat="dd/mm/yyyy HH:MM" im-insert="false" data-input>
                                                <div class="input-button-calendar col-md-3 pull-right no-padding">
                                                    <a class="input-button pull-left btn-primary" title="toggle" data-toggle>
                                                        <i class="fa fa-calendar text-white"></i>
                                                    </a>
                                                    <a class="input-button pull-right btn-primary" title="clear" data-clear>
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
                                                    <a class="input-button pull-left btn-primary" title="toggle" data-toggle>
                                                        <i class="fa fa-calendar text-white"></i>
                                                    </a>
                                                    <a class="input-button pull-right btn-primary" title="clear" data-clear>
                                                        <i class="fa fa-times text-white"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <h3>Equipamentos</h3>
                                <div class="stepRental">
                                    <h6>Equipamentos</h6>
                                    <div class="row">
                                        <div class="form-group col-md-12 mt-2 equipaments-selected">
                                            <div class="accordion accordion-multiple-filled" id="equipaments-selected" role="tablist">
                                            </div>
                                            <hr class="separator-dashed mt-4 display-none">
                                        </div>
                                        <div class="form-group col-md-12 mt-2">
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="searchEquipament" placeholder="Pesquise por nome, referência ou código">
                                                <div class="input-group-addon input-group-append btn-primary">
                                                    <i class="fa fa-search input-group-text text-white"></i>
                                                </div>
                                                <div class="input-group-addon input-group-append btn-primary" id="cleanSearchEquipament">
                                                    <i class="fa fa-times input-group-text text-white"></i>
                                                </div>
                                                <div class="input-group-addon input-group-append btn-primary" id="newEquipament" data-toggle="modal" data-target="#newEquipamentModal">
                                                    <i class="fa fa-plus input-group-text text-white"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-12 mt-2 table-responsive content-equipament">
                                            <table class="table list-equipament d-table">
                                                <tbody>
                                                    <tr class="equipament">
                                                        <td class="text-left"><h6 class="text-center"><i class="fas fa-search"></i> Pesquise por um equipamento</h6></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <h3>Pagamento</h3>
                                <div class="stepRental">
                                    <h6>Pagamento</h6>
                                    <div class="row">
                                        <div class="form-group col-md-12 text-center mt-5">
                                            <h4><i class="fa fa-warning"></i> Em andamento, em breve estará disponível</h4>
                                        </div>
                                    </div>
                                </div>
                                <h3>Finalizar</h3>
                                <div class="stepRental">
                                    <h6>Finalizar</h6>
                                    <div class="row">
                                        <div class="form-group col-md-12 text-center mt-5">
                                            <h4><i class="fa fa-warning"></i> Em andamento, em breve estará disponível</h4>
                                        </div>
                                    </div>
                                </div>
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
{{--                <div class="col-md-4">--}}
{{--                    <div class="card">--}}
{{--                        <div class="card-body">--}}
{{--                            <p>Em andamento ...</p>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
            </div>
        </div>
    </div>
    @include('includes.client.modal-create')
    @include('includes.equipament.modal-create')
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
    <input type="hidden" id="routeGetStockEquipament" value="{{ route('ajax.equipament.get-stock') }}">

@stop
