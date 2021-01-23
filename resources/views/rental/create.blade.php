@extends('adminlte::page')

@section('title', 'Cadastro de Locação')

@section('content_header')
    <h1 class="m-0 text-dark">Cadastro de Locação</h1>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/material_blue.css">
    <link href="{{ asset('vendor/icheck/skins/all.css') }}" rel="stylesheet">
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
        .flatpickr a.input-button,
        .flatpickr button.input-button{
            height: calc(1.5em + 0.75rem + 3px);
            width: 50%;
            text-align: center;
            padding-top: 13%;
            cursor: pointer;
            border: 1px solid transparent
        }
        .flatpickr a.input-button:last-child,
        .flatpickr button.input-button:last-child{
            border-bottom-right-radius: 5px;
            border-top-right-radius: 5px;
        }
        [name^="date_withdrawal"], [name^="date_delivery"], [name="stock_equipament"] {
            border-bottom-right-radius: 0 !important;
            border-top-right-radius: 0 !important;;
        }
        .input-group-append.btn-primary{
            background: #2196f3;
            cursor: pointer;
        }
        .input-group-append.btn-success{
            background: #19d895;
            cursor: pointer;
        }
        .input-group-append.btn-danger{
            background: #ff6258;
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
        .content-equipament::-webkit-scrollbar-track {
            -webkit-box-shadow: inset 0 0 6px #2196f3;
            border-radius: 10px;
            background-color: #F5F5F5;
        }
        .content-equipament::-webkit-scrollbar {
            width: 12px;
            background-color: #F5F5F5;
        }
        .content-equipament::-webkit-scrollbar-thumb {
            border-radius: 10px;
            -webkit-box-shadow: inset 0 0 6px #2196f3;
            background-color: #52a4e5;
        }
        .calendar_equipament a {
            height: calc(1.5em + 0.75rem + 4px) !important;
        }
        .calendar_equipament i {
            font-size: 14px !important;
        }
        a[disabled] {
            pointer-events: none;
            cursor: no-drop;
        }
        .list-equipaments-payment li.one-li-list-equipaments-payment:after{
            display: none;
        }
        .btn-view-price-period-equipament {
            display: flex;
            align-items: center;
            justify-content: center;
            padding-bottom: 5px;
            height: calc(1.5em + 0.75rem + 3px);
        }
        .btn-view-price-period-equipament i {
            font-size: 18px !important;
            color: #fff;
        }
        .payment-yes input:disabled {
            background-color: #eee;
        }
        .payment-yes .input-group-text {
            background-color: #eee;
        }
    </style>
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
<script src="{{ asset('vendor/icheck/icheck.min.js') }}" type="application/javascript"></script>

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
        $('#discount_value, #extra_value, #net_value').mask('#.##0,00', { reverse: true });
        loadDrivers(0, '#newVehicleModal [name="driver"]');
        $('[name="type_rental"]').iCheck({
            checkboxClass: 'icheckbox_square',
            radioClass: 'iradio_square-blue',
            increaseArea: '20%' // optional
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

                const date_delivery = $('input[name="date_delivery"]').val();
                const date_withdrawal = $('input[name="date_withdrawal"]').val();

                const colRef = response.cacamba ? 'col-md-4' : 'col-md-8';
                const colQty = response.cacamba ? 'col-md-3' : 'col-md-4';
                const displayResidue = response.cacamba ? '' : 'display-none';

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
                        <div id="collapseEquipament-${response.id}" class="collapse" role="tabpanel" aria-labelledby="headingEquipament-${response.id}" data-parent="#equipaments-selected" id-equipament="${response.id}">
                            <div class="card-body">
                                <div class="row">
                                    <div class="form-group ${colRef}">
                                        <label>Referência</label>
                                        <input type="text" class="form-control" value="${response.reference}" name="reference_equipament" disabled>
                                    </div>
                                    <div class="${colQty}">
                                        <div class="form-group flatpickr label-animate stock-group">
                                            <label>Quantidade</label>
                                            <input type="tel" name="stock_equipament" class="form-control col-md-9 pull-left flatpickr-input" value="1" max-stock="${response.stock}">
                                            <div class="input-button-calendar col-md-3 pull-right no-padding">
                                                <button class="input-button pull-right btn-primary w-100 btn-view-price-period-equipament" data-toggle="tootip" title="Visualizar valor por período" id-equipament="${response.id}">
                                                    <i class="fas fa-file-invoice-dollar"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <small class="text-danger font-weight-bold stock_available pull-left">Disponível: ${response.stock}</small>
                                    </div>
                                    <div class="form-group col-md-5 label-animate ${displayResidue}">
                                        <label>Resíduo</label>
                                        <div class="input-group label-animate">
                                            <select class="form-control" name="residue[]" disabled>
                                                <option>Carregando ...</option>
                                            </select>
                                            <div class="input-group-addon input-group-append">
                                                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#newResidueModal" title="Novo Resíduo"><i class="fas fa-plus-circle"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-6 label-animate">
                                        <label>Veículo</label>
                                        <div class="input-group label-animate">
                                            <select class="form-control" name="vehicle[]" disabled>
                                                <option>Carregando ...</option>
                                            </select>
                                            <div class="input-group-addon input-group-append">
                                                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#newVehicleModal" title="Novo Veículo"><i class="fas fa-plus-circle"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-6 label-animate">
                                        <label>Motorista</label>
                                        <div class="input-group label-animate">
                                            <select class="form-control" name="driver[]" disabled>
                                                <option>Carregando ...</option>
                                            </select>
                                            <div class="input-group-addon input-group-append">
                                                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#newDriverModal" title="Novo Motorista"><i class="fas fa-plus-circle"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="switch pt-3">
                                            <input type="checkbox" class="check-style check-xs use_date_diff_equip" name="use_date_diff_equip" id="use_date_diff_equip_${response.id}">
                                            <label for="use_date_diff_equip_${response.id}" class="check-style check-xs"></label> Usar datas de entrega e/ou retirada diferentes para esse equipamento.
                                        </div>
                                    </div>
                                </div>
                                <div class="row display-none use_date_diff_equip_show">
                                    <div class="col-md-6">
                                        <div class="form-group flatpickr">
                                            <label>Data Prevista de Entrega</label>
                                            <input type="text" name="date_delivery_equipament" class="form-control col-md-9 pull-left" value="${date_delivery}" data-inputmask="'alias': 'datetime'" data-inputmask-inputformat="dd/mm/yyyy HH:MM" im-insert="false" data-input disabled>
                                            <div class="input-button-calendar col-md-3 pull-right no-padding calendar_equipament">
                                                <a class="input-button pull-left btn-primary" title="toggle" data-toggle disabled>
                                                    <i class="fa fa-calendar text-white"></i>
                                                </a>
                                                <a class="input-button pull-right btn-primary" title="clear" data-clear disabled>
                                                    <i class="fa fa-times text-white"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group flatpickr">
                                            <label>Data Prevista de Retirada</label>
                                            <input type="text" name="date_withdrawal_equipament" class="form-control col-md-9 pull-left" value="${date_withdrawal}" data-inputmask="'alias': 'datetime'" data-inputmask-inputformat="dd/mm/yyyy HH:MM" im-insert="false" data-input disabled>
                                            <div class="input-button-calendar col-md-3 pull-right no-padding calendar_equipament">
                                                <a class="input-button pull-left btn-primary" title="toggle" data-toggle disabled>
                                                    <i class="fa fa-calendar text-white"></i>
                                                </a>
                                                <a class="input-button pull-right btn-primary" title="clear" data-clear disabled>
                                                    <i class="fa fa-times text-white"></i>
                                                </a>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="switch pt-1">
                                                <input type="checkbox" class="check-style check-xs not_use_date_withdrawal" name="use_date_diff_equip" id="not_use_date_withdrawal_${response.id}" disabled>
                                                <label for="not_use_date_withdrawal_${response.id}" class="check-style check-xs"></label> Não informar data de retirada
                                            </div>
                                        </div>
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
                $('#cleanSearchEquipament').trigger('click')
                setTimeout(() => {
                    $(`.load-equipament[id-equipament="${idEquipament}"]`).remove();

                    if (!$(`.list-equipament tbody tr`).length) {
                        equipamentMessageDefault('<i class="fas fa-surprise"></i> Nenhum equipamento encontrado');
                    }
                    checkLabelAnimate();
                    $(`#collapseEquipament-${idEquipament}`).collapse('show');
                    $(`#collapseEquipament-${idEquipament} input[name="stock_equipament"]`).mask('0#');
                    $(`#collapseEquipament-${idEquipament} input[name="date_withdrawal_equipament"]`).inputmask();
                    $(`#collapseEquipament-${idEquipament} input[name="date_delivery_equipament"]`).inputmask();
                    $(`#collapseEquipament-${idEquipament} .btn-view-price-period-equipament`).tooltip();

                    $(`#collapseEquipament-${idEquipament} .flatpickr:not(.stock-group)`).flatpickr({
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
                    if ($('#not_use_date_withdrawal').is(':checked')) {
                        $(`#collapseEquipament-${idEquipament} input[name="date_withdrawal_equipament"]`).val('');
                        $(`#collapseEquipament-${idEquipament} .not_use_date_withdrawal`).prop('checked', true);
                    }

                    loadVehicles(0,`#collapseEquipament-${idEquipament} select[name="vehicle[]"]`);
                    loadDrivers(0, `#collapseEquipament-${idEquipament} select[name="driver[]"]`);
                    loadResidues(0,`#collapseEquipament-${idEquipament} select[name="residue[]"]`);
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
        $(`#collapseEquipament-${idEquipament}`).collapse('hide');
    });

    $(document).on('click', '.use_date_diff_equip', function (){
        const elEquip = $(this).closest('.card-body');
        let date_delivery, date_withdrawal;

        elEquip.find('input[name="date_delivery_equipament"]').attr('disabled', !$(this).is(':checked'));
        elEquip.find('.not_use_date_withdrawal').attr('disabled', !$(this).is(':checked'));

        if (!elEquip.find('.not_use_date_withdrawal').is(':checked'))
            elEquip.find('input[name="date_withdrawal_equipament"]').attr('disabled', !$(this).is(':checked'));

        if (!elEquip.find('.not_use_date_withdrawal').is(':checked'))
            elEquip.find('.calendar_equipament:eq(1) a').attr('disabled', !$(this).is(':checked'));

        elEquip.find('.calendar_equipament:eq(0) a').attr('disabled', !$(this).is(':checked'));

        if (!$(this).is(':checked')) {
            date_delivery = $('input[name="date_delivery"]').val();
            date_withdrawal = $('input[name="date_withdrawal"]').val();

            elEquip.find('input[name="date_delivery_equipament"]').val(date_delivery);
            elEquip.find('input[name="date_withdrawal_equipament"]').val(date_withdrawal);

            if ($('#not_use_date_withdrawal').is(':checked'))
                elEquip.find('.not_use_date_withdrawal').prop('checked', true);
            else
                elEquip.find('.not_use_date_withdrawal').prop('checked', false);

            checkLabelAnimate();

            elEquip.find('.use_date_diff_equip_show').slideUp('slow');
        } else
            elEquip.find('.use_date_diff_equip_show').slideDown({
                start: function () {
                    $(this).css({
                        display: "flex"
                    })
                }
            });
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
            setTimeout(() => {
                $(`#collapseEquipament-${idEquipament}`).collapse('show');
                $(this).focus();
            }, 550);
        }
    });

    $('#not_use_date_withdrawal').on('click', function (){
        const elEquip = $(this).closest('.col-md-6');

        elEquip.find('input[name="date_withdrawal"]').attr('disabled', $(this).is(':checked'));
        elEquip.find('.flatpickr a').attr('disabled', $(this).is(':checked'));

        elEquip.find('input[name="date_withdrawal"]').val('');

        if (!$(this).is(':checked')) {
            elEquip.find('input[name="date_withdrawal"]').val(getTodayDateBr());
        }
        checkLabelAnimate();
    });

    $(document).on('click', '.not_use_date_withdrawal', function (){
        const elEquip = $(this).closest('.col-md-6');

        elEquip.find('input[name="date_withdrawal_equipament"]').attr('disabled', $(this).is(':checked'));
        elEquip.find('.flatpickr a').attr('disabled', $(this).is(':checked'));

        elEquip.find('input[name="date_withdrawal_equipament"]').val('');

        if (!$(this).is(':checked'))
            elEquip.find('input[name="date_withdrawal_equipament"]').val(getTodayDateBr());

        checkLabelAnimate();
    });

    $('#extra_value, #discount_value, #net_value').on('keyup', () => {
        reloadTotalRental();
    }).on('blur', function(){
        if ($(this).val() === '') $(this).val('0,00');
    });

    $(document).on('click', '.btn-view-price-period-equipament', function (){
        const btn = $(this);
        const idEquipament = $(this).attr('id-equipament');

        btn.attr('disable', true);

        let descPeriod = '';

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'POST',
            url: "{{ route('ajax.equipament.get-price-per-period') }}",
            data: { idEquipament },
            success: response => {
                if (response.length) {
                    descPeriod += '<ol class="no-padding">';
                    $.each(response, function (key, val) {
                        descPeriod += `<li><b>${val.day_start} dias</b> até <b>${val.day_end} dias</b> por <b>R$${numberToReal(val.value)}</b></li>`;
                    });
                    descPeriod += '</ol>';
                } else
                    descPeriod += 'Equipamento não contém valor por período definido.';

                btn.attr('disable', true);

                Swal.fire({
                    icon: 'info',
                    title: 'Valores Por Período',
                    html: descPeriod
                });
            }, error: () => {
                btn.attr('disable', true);
                Swal.fire({
                    icon: 'info',
                    title: 'Valores Por Período',
                    html: 'Não foi possível localizar os valores do equipamento.'
                });
            }
        });

        return false;
    });

    $('#is_parceled').change(function (){
        const check = $(this).is(':checked');

        if (check) {
            $('#add_parcel, #del_parcel, .automatic_parcel_distribution_parent').slideDown(500);
            $('#parcels').show().append(
                createParcel(0)
            ).find('.form-group').slideDown(500).find('[name="value_parcel[]"]').mask('#.##0,00', { reverse: true });

            recalculeParcels();
        }
        else {
            $('#add_parcel, #del_parcel, #parcels, .automatic_parcel_distribution_parent').slideUp(500);
            $('#automatic_parcel_distribution').prop('checked', true);
            setTimeout(() => { $('#parcels .form-group').remove() }, 550)
        }
    })

    $('#parcels').on('keyup change', '[name="due_day[]"]', function(){
        let days = parseInt($(this).val());
        const el = $(this).closest('.form-group');

        el.find('[name="due_date[]"]').val(sumDaysDateNow(days));
    });

    $('#parcels').on('blur', '[name="due_date[]"]', function(){
        const dataVctoInput = $(this).val();
        if (dataVctoInput === '') return false;

        const diasVcto = calculateDays(getTodayDateEn(false), dataVctoInput);
        const el = $(this).closest('.form-group');

        el.find('[name="due_day[]"]').val(diasVcto);
    });

    $('#add_parcel').click(function(){
        const parcels = $('#parcels .form-group').length;

        $('#parcels').show().append(
            createParcel(parcels)
        ).find('.form-group').slideDown(500).find('[name="value_parcel[]"]').mask('#.##0,00', { reverse: true });

        $('#del_parcel').attr('disabled', false);

        recalculeParcels();
    });

    $('#del_parcel').click(function(){
        const dues = $('#parcels .form-group').length - 1;

        $(`#parcels .form-group:eq(${dues})`).remove();

        if (dues === 1)
            $('#del_parcel').attr('disabled', true);

        recalculeParcels();

    });

    $('#automatic_parcel_distribution').change(function(){
        const check = $(this).is(':checked');

        if (check) {
            $('#parcels .form-group [name="value_parcel[]"]').attr('disabled', true);
            recalculeParcels();
        } else
            $('#parcels .form-group [name="value_parcel[]"]').attr('disabled', false);

    });

    $(document).on('change', '[name="vehicle[]"]', function (){
        const vehicle_id = $(this).val();
        if (vehicle_id == 'Selecione ...') return false;

        const el = $(this).closest('.card-body');

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'GET',
            data: { vehicle_id },
            url: "{{ route('ajax.vehicle.get-vehicle')  }}",
            async: true,
            success: response => {
                if (response.driver_id && el.find('[name="driver[]"]').val() === 'Selecione ...')
                    el.find('[name="driver[]"]').val(response.driver_id)
                else if(response.driver_id)
                    Swal.fire({
                        title: 'Alteração de Motorista',
                        html: `O veículo selecionado contém relacionado o motorista <b>${response.driver_name}</b>. <br>Deseja atualizar?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#19d895',
                        cancelButtonColor: '#bbb',
                        confirmButtonText: 'Sim, atualizar',
                        cancelButtonText: 'Não atualizar',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed)
                            el.find('[name="driver[]"]').val(response.driver_id)
                    })

            }
        });
    });

    $('#calculate_net_amount_automatic').on('change', function(){
        if ($(this).is(':checked')) {
            $('#net_value').attr('disabled', true);
            reloadTotalRental();
        } else $('#net_value').attr('disabled', false);
    });

    const recalculeParcels = () => {
        if ($('#automatic_parcel_distribution').is(':checked')) {
            const parcels = $('#parcels .form-group').length;
            const netValue = realToNumber($('#net_value').val());

            let valueSumParcel = parseFloat(0.00);
            let valueParcel = netValue / parcels;

            for (let count = 0; count < parcels; count++) {

                if((count + 1) === parcels) valueParcel = netValue - valueSumParcel;

                valueSumParcel += parseFloat((netValue / parcels).toFixed(2));
                $(`#parcels .form-group [name="value_parcel[]"]:eq(${count})`).val(numberToReal(valueParcel));
            }
        }
    }

    const createParcel = due => {
        const disabledValue = $('#automatic_parcel_distribution').is(':checked') ? 'disabled' : '';
        return `<div class="form-group mt-1 display-none">
            <div class="d-flex align-items-center justify-content-between">
                <div class="input-group col-md-12 no-padding">
                    <div class="input-group-prepend stock-equipament-payment col-md-3 no-padding">
                        <span class="input-group-text col-md-12 no-border-radius "><strong>${(due+1)}º Vencimento</strong></span>
                    </div>
                    <input type="text" class="form-control col-md-2 text-center" name="due_day[]" value="${(due*30)}">
                    <input type="date" class="form-control col-md-4 text-center" name="due_date[]" value="${sumDaysDateNow(due*30)}">
                    <div class="input-group-prepend col-md-1 no-padding">
                        <span class="input-group-text pl-3 pr-3 col-md-12"><strong>R$</strong></span>
                    </div>
                    <input type="text" class="form-control col-md-2 no-border-radius text-center" name="value_parcel[]" value="0,00" ${disabledValue}>
                </div>
            </div>
        </div>`
    }

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
@include('includes.driver.modal-script')
@include('includes.vehicle.modal-script')
@include('includes.residue.modal-script')
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
                                            <div class="">
                                                <input type="radio" name="type_rental" value="0" id="have-payment" @if(old('type_person') === '0') checked @endif>
                                                <label for="have-payment">Com Cobrança</label>
                                            </div>
                                            <div class="">
                                                <input type="radio" name="type_rental" id="no-have-payment" value="1" @if(old('type_person') === '1') checked @endif>
                                                <label for="no-have-payment">Sem Cobrança</label>
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
                                            <div class="form-group">
                                                <div class="switch pt-1">
                                                    <input type="checkbox" class="check-style check-xs" name="not_use_date_withdrawal" id="not_use_date_withdrawal">
                                                    <label for="not_use_date_withdrawal" class="check-style check-xs"></label> Não informar data de retirada
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
                                                <div class="input-group-addon input-group-append btn-danger" id="cleanSearchEquipament">
                                                    <i class="fa fa-times input-group-text text-white"></i>
                                                </div>
                                                <div class="input-group-addon input-group-append btn-success" id="newEquipament" data-toggle="modal" data-target="#newEquipamentModal">
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
                                        <div class="payment-yes col-md-12 mt-3">
                                            <div class="col-md-12 grid-margin stretch-card">
                                                <ul class="bullet-line-list pl-3 col-md-12 list-equipaments-payment"></ul>
                                                <div class="pl-3 col-md-12 list-equipaments-payment-load text-center">
                                                    <h4>Carregando equipamentos <i class="fa fa-spinner fa-spin"></i></h4>
                                                </div>
                                            </div>
                                            <hr class="separator-dashed">
                                            <div class="col-md-12">
                                                <div class="d-flex justify-content-end align-items-center mb-2">
                                                    <label class="mb-0 mr-md-2">Valor Bruto</label>
                                                    <div class="input-group col-md-4 no-padding">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text"><strong>R$</strong></span>
                                                        </div>
                                                        <span type="text" class="form-control d-flex align-items-center" id="gross_value"></span>
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-content-end align-items-center mb-2">
                                                    <label class="mb-0 mr-md-2">Acréscimo</label>
                                                    <div class="input-group col-md-4 no-padding">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text"><strong>R$</strong></span>
                                                        </div>
                                                        <input type="text" class="form-control" value="0,00" id="extra_value">
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-content-end align-items-center mb-2">
                                                    <label class="mb-0 mr-md-2">Desconto</label>
                                                    <div class="input-group col-md-4 no-padding">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text"><strong>R$</strong></span>
                                                        </div>
                                                        <input type="text" class="form-control" value="0,00" id="discount_value">
                                                    </div>
                                                </div>
                                            </div>
                                            <hr class="separator-dashed">
                                            <div class="col-md-12">
                                                <div class="d-flex justify-content-end align-items-center mb-2 flex-wrap">
                                                    <label class="mb-0 mr-md-2">Valor Líquido</label>
                                                    <div class="input-group col-md-4 no-padding">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text"><strong>R$</strong></span>
                                                        </div>
                                                        <input type="text" class="form-control d-flex align-items-center" id="net_value" disabled>
                                                    </div>
                                                    <div class="form-group col-md-12 no-padding text-right mt-2">
                                                        <div class="switch">
                                                            <input type="checkbox" class="check-style check-xs" name="calculate_net_amount_automatic" id="calculate_net_amount_automatic" checked>
                                                            <label for="calculate_net_amount_automatic" class="check-style check-xs"></label> Calcular Valor Líquido
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <hr class="separator-dashed">
                                            <div class="col-md-12 d-flex justify-content-between">
                                                <div class="form-group">
                                                    <div class="switch">
                                                        <input type="checkbox" class="check-style check-xs" name="is_parceled" id="is_parceled">
                                                        <label for="is_parceled" class="check-style check-xs"></label> Gerar Parcelamento
                                                    </div>
                                                </div>
                                                <div class="form-group display-none automatic_parcel_distribution_parent">
                                                    <div class="switch">
                                                        <input type="checkbox" class="check-style check-xs" name="automatic_parcel_distribution" id="automatic_parcel_distribution" checked>
                                                        <label for="automatic_parcel_distribution" class="check-style check-xs"></label> Distribuir Valores
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <button type="button" class="btn btn-success display-none" id="add_parcel"><i class="fa fa-plus"></i> Parcela</button>
                                                </div>
                                                <div class="form-group">
                                                    <button type="button" class="btn btn-danger display-none" id="del_parcel" disabled><i class="fa fa-trash"></i> Parcela</button>
                                                </div>
                                            </div>
                                            <div class="col-md-12 display-none" id="parcels"></div>
                                        </div>
                                        <div class="payment-no col-md-12 mt-5">
                                            <div class="form-group text-center">
                                                <h4><i class="fa fa-check"></i> Locação sem cobrança.</h4>
                                                <h5>Prossiga para a próxima etapa.</h5>
                                            </div>
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
    @include('includes.vehicle.modal-create')
    @include('includes.driver.modal-create')
    @include('includes.residue.modal-create')
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
    <input type="hidden" id="routeGetPriceEquipament" value="{{ route('ajax.equipament.get-price') }}">
    <input type="hidden" id="routeGetEquipament" value="{{ route('ajax.equipament.get-equipament') }}">
    <input type="hidden" id="routeGetPriceStockEquipaments" value="{{ route('ajax.equipament.get-price-stock-check') }}">

@stop
