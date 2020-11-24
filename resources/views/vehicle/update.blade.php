@extends('adminlte::page')

@section('title', 'Alterar de Veículo')

@section('content_header')
    <h1 class="m-0 text-dark">Alterar de Veículo</h1>
@stop

@section('css')
@stop

@section('js')
{{--<script src="{{ asset('assets/js/views/vehicle/form.js') }}" type="application/javascript"></script>--}}
<script>
    // Validar dados
    $("#formUpdateVehicle").validate({
        rules: {
            name: {
                required: true
            }
        },
        messages: {
            name: {
                required: 'Informe um nome para o veículo'
            }
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
            $('#formCreateVehicle [type="submit"]').attr('disabled', true);
            form.submit();
        }
    });
</script>
@include('includes.driver.modal-script')
@stop

@php
    $disabled = in_array('VehicleUpdatePost', $permissions) ? '' : 'disabled';
    $driverSelected = old('driver') ?? $vehicle->driver_id;
@endphp

@section('content')
    <div class="row">
        <div class="col-md-12 d-flex align-items-stretch grid-margin">
            <div class="row flex-grow">
                <div class="col-12">
                    @if ($errors->any())
                    <div class="alert alert-warning">
                        <ol>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ol>
                    </div>
                    @endif
                    <form action="{{ route(('vehicle.update')) }}" method="POST" enctype="multipart/form-data" id="formUpdateVehicle">
                        <div class="card">
                            <div class="card-body">
                                <div class="header-card-body">
                                    <h4 class="card-title">Dados do Veículo</h4>
                                    <p class="card-description"> Altere o formulário abaixo com as informações do veículo </p>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-5">
                                        <label for="name">Nome do Veículo <sup>*</sup></label>
                                        <input {{ $disabled }} type="text" class="form-control" id="name" name="name" autocomplete="nope" value="{{ old('name') ?? $vehicle->name }}" required>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="reference">Referência</label>
                                        <input {{ $disabled }} type="text" class="form-control" id="reference" name="reference" autocomplete="nope" value="{{ old('reference') ?? $vehicle->reference }}">
                                    </div>
                                    <div class="form-group col-md-4 label-animate">
                                        @include('includes.driver.form')
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label for="brand">Marca</label>
                                        <input {{ $disabled }} type="text" class="form-control" id="brand" name="brand" autocomplete="nope" value="{{ old('brand') ?? $vehicle->brand }}">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="model">Modelo</label>
                                        <input {{ $disabled }} type="text" class="form-control" id="model" name="model" autocomplete="nope" value="{{ old('model') ?? $vehicle->model }}">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="board">Placa</label>
                                        <input {{ $disabled }} type="text" class="form-control" id="board" name="board" autocomplete="nope" value="{{ old('board') ?? $vehicle->board }}">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <label for="observation">Observação</label>
                                        <textarea {{ $disabled }} class="form-control" id="observation" name="observation" rows="3">{{ old('observation') ?? $vehicle->observation }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body d-flex justify-content-between">
                                <a href="{{ route('vehicle.index') }}" class="btn btn-secondary col-md-3"><i class="fa fa-arrow-left"></i> Cancelar</a>
                                @if(empty($disabled))<button type="submit" class="btn btn-success col-md-3"><i class="fa fa-sync"></i> Atualizar</button>@endif
                            </div>
                        </div>
                        @if(empty($disabled))
                        <input type="hidden" name="vehicle_id" value="{{ $vehicle->id }}">
                        {{ csrf_field() }}
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
    @include('includes.driver.modal-create')
@stop
