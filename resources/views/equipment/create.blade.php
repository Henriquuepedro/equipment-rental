@extends('adminlte::page')

@section('title', 'Cadastro de Equipamento')

@section('content_header')
    <h1 class="m-0 text-dark">Cadastro de Equipamento</h1>
@stop

@section('css')
@stop

@section('js')
<script src="{{ asset('assets/js/views/equipment/form.js') }}" type="application/javascript"></script>
<script>
    // validate the form when it is submitted
    $("#formEquipment").validate({
        rules: {
            name: {
                name_valid: true
            },
            volume: {
                volume: true
            },
            reference: {
                required: true
            }
        },
        messages: {
            reference: {
                required: "Informe uma referência/código/numeração para seu equipamento"
            }
        },
        invalidHandler: function(event, validator) {
            $('html, body').animate({scrollTop:0}, 200);
            let arrErrors = [];
            $.each(validator.errorMap, function (key, val) {
                arrErrors.push(val);
            });
            setTimeout(() => {
                Swal.fire({
                    icon: 'warning',
                    title: 'Atenção',
                    html: '<ol><li>'+arrErrors.join('</li><li>')+'</li></ol>'
                })
            }, 250);
        },
        submitHandler: function(form) {
            let verifyPeriod = verifyPeriodComplet();
            if (!verifyPeriod[0]) {
                if (verifyPeriod[2] !== undefined) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Atenção',
                        html: '<ol><li>'+verifyPeriod[2].join('</li><li>')+'</li></ol>'
                    })
                } else {
                    Toast.fire({
                        icon: 'warning',
                        title: `Finalize o cadastro do ${verifyPeriod[1]}º período, para adicionar um novo.`
                    });
                }
                return false;
            }
            $('#formEquipment [type="submit"]').attr('disabled', true);
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
                    <div class="alert-animate alert-warning">
                        <ol>
                            @foreach($errors->all() as $error)
                                <li><label class="error">{{ $error }}</label></li>
                            @endforeach
                        </ol>
                    </div>
                    @endif
                    <form action="{{ route(('equipment.insert')) }}" method="POST" enctype="multipart/form-data" id="formEquipment">

                        @include('includes.equipment.form-create')

                        <div class="card display-none">
                            <div class="card-body d-flex justify-content-between">
                                <a href="{{ route('equipment.index') }}" class="btn btn-secondary col-md-3"><i class="fa fa-arrow-left"></i> Cancelar</a>
                                <button type="submit" class="btn btn-success col-md-3"><i class="fa fa-save"></i> Cadastrar</button>
                            </div>
                        </div>
                        {{ csrf_field() }}
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
