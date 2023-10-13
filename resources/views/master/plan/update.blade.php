@extends('adminlte::page')

@section('title', 'Alterar Plano')

@section('content_header')
    <h1 class="m-0 text-dark">Alterar Plano</h1>
@stop

@section('css')
@stop

@section('js')
<script>
    $(function(){
        $('[name="value"]').maskMoney({thousands: '.', decimal: ',', allowZero: true});

        if ($('#content_description').length) {

            var quill = new Quill('#content_description', {
                modules: {
                    toolbar: [
                        ['bold', 'italic', 'underline'],
                        [{ 'align': [] }],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        [{ 'color': [] }, { 'background': [] }],
                        ['link']
                    ]
                },
                theme: 'snow' // or 'bubble'
            });
        }
    });

    $(document).on('click', '#formUpdatePlan input[type="checkbox"]', function(){
        const permission_id = parseInt($(this).data('permission-id'));
        const auto_check    = $(this).data('auto-check');
        const parentEl      = '#formUpdatePlan';
        let input_auto_check;

        $(`${parentEl} input[type="checkbox"]:checked`).each(function(){
            input_auto_check = $(this).data('auto-check');
            if (input_auto_check.includes(permission_id)) {
                $(`${parentEl} input[type="checkbox"][data-permission-id="${permission_id}"]`).prop('checked', true);
                return false;
            }
        });

        if (auto_check.length) {
            auto_check.forEach(id => {
                $(`${parentEl} input[type="checkbox"][data-permission-id="${id}"]`).prop('checked', true);
            })
        }
    });

    // Validar dados
    $("#formUpdatePlan").validate({
        rules: {
            name: {
                required: true
            },
            phone: {
                rangelength: [13, 14]
            },
            email: {
                required: true,
                email: true
            },
            password: {
                minlength: 6
            },
            password_confirmation: {
                equalTo : "#password"
            }
        },
        messages: {
            name: {
                required: 'Digite o nome/razão social da empresa.'
            },
            phone: {
                rangelength: "O campo telefone primário deve ser um telefone válido."
            },
            email: {
                required: "Informe um e-mail comercial válido.",
                email: "Informe um e-mail comercial válido."
            },
            password: {
                minlength: "Senha deve conter no mínimo 6 caracteres."
            },
            password_confirmation: {
                equalTo : "Senhas devem ser iguais."
            }
        },
        invalidHandler: function(event, validator) {
            let arrErrors = [];
            $.each(validator.errorMap, function (key, val) {
                arrErrors.push(val);
            });
            Swal.fire({
                icon: 'warning',
                title: 'Atenção',
                html: '<ol><li>'+arrErrors.join('</li><li>')+'</li></ol>'
            });
        },
        submitHandler: function(form) {
            $("#description").val($("#content_description .ql-editor").html());
            form.submit();
        }
    });
</script>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12 d-flex align-items-stretch grid-margin">
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
                    <form action="{{ route(empty($plan) ? 'master.plan.insert' : 'master.plan.update', empty($plan) ? [] : array('id' => $plan->id)) }}" method="POST" enctype="multipart/form-data" id="formUpdatePlan">
                        <div class="card">
                            <div class="card-body d-flex flex-wrap">
                                <div class="header-card-body">
                                    <h4 class="card-title">Dados do Plano</h4>
                                    <p class="card-description"> Preencha o formulário abaixo com as novas informações do plano </p>
                                </div>
                                <div class="col-md-12 no-padding">
                                    <div class="row">
                                        <div class="form-group col-md-4">
                                            <label>Nome</label>
                                            <input type="text" class="form-control" name="name" value="{{ old('name', $plan->name ?? '') }}">
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label>Valor</label>
                                            <input type="text" class="form-control" name="value" value="{{ old('value', formatMoney($plan->value ?? 0)) }}">
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label>Quantidade de Equipamentos</label>
                                            <input type="text" class="form-control" name="quantity_equipment" value="{{ old('quantity_equipment', $plan->quantity_equipment ?? '') }}">
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label>Quantidade de Meses</label>
                                            <select name="month_time" id="month_time" class="form-control select2">
                                                <option value="1" {{ old('month_time', $plan->month_time) == 1 ? 'selected' : '' }}>1 Mês</option>
                                                <option value="3" {{ old('month_time', $plan->month_time) == 3 ? 'selected' : '' }}>3 Meses</option>
                                                <option value="6" {{ old('month_time', $plan->month_time) == 6 ? 'selected' : '' }}>6 Meses</option>
                                                <option value="12" {{ old('month_time', $plan->month_time) == 12 ? 'selected' : '' }}>1 Ano</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="form-group col-md-12 mt-3">
                                            <h5>Descrição do plano</h5>
                                            <div id="content_description" class="quill-container">{!! $plan->description !!}</div>
                                            <textarea type="hidden" class="d-none" name="description" id="description"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body d-flex justify-content-between">
                                <a href="{{ route('master.plan.index') }}" class="btn btn-secondary col-md-3"><i class="fa fa-arrow-left"></i> Cancelar</a>
                                <button type="submit" class="btn btn-success col-md-3"><i class="fa fa-sync"></i> Atualizar</button>
                            </div>
                        </div>
                        {{ csrf_field() }}
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
