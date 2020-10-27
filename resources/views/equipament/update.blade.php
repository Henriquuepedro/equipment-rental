@extends('adminlte::page')

@section('title', 'Alterar de Equipamento')

@section('content_header')
    <h1 class="m-0 text-dark">Alterar de Equipamento</h1>
@stop

@section('css')
@stop

@section('js')
<script src="{{ asset('assets/js/views/equipament/form.js') }}" type="application/javascript"></script>
<script>
    // validate the form when it is submitted
    $("#formUpdateEquipament").validate({
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
            $('html, body').animate({scrollTop:0}, 400);
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
            }, 500);
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

            $('#formUpdateEquipament [type="submit"]').attr('disabled', true);
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
                    <div class="alert alert-warning">
                        <ol>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ol>
                    </div>
                    @endif
                    <form action="{{ route(('equipament.update')) }}" method="POST" enctype="multipart/form-data" id="formUpdateEquipament">
                        <div class="card">
                            <div class="card-body d-flex justify-content-around">
                                <div class="form-radio form-radio-flat">
                                    <label class="form-check-label">
                                        <input type="radio" class="form-check-input" name="type_equipament" value="cacamba" {{ old() ? (old('type_equipament') === 'cacamba' ? 'checked' : '') : ($equipament->name === null ? 'checked' : '') }}> Caçamba <i class="input-helper"></i>
                                    </label>
                                </div>
                                <div class="form-radio form-radio-flat">
                                    <label class="form-check-label">
                                        <input type="radio" class="form-check-input" name="type_equipament" value="others" {{ old() ? (old('type_equipament') === 'others' ? 'checked' : '') : ($equipament->name !== null ? 'checked' : '') }}> Outros <i class="input-helper"></i>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="card display-none">
                            <div class="card-body">
                                <div class="header-card-body">
                                    <h4 class="card-title">Dados do Equipamento</h4>
                                    <p class="card-description"> Altere o formulário abaixo com as informações do equipamento</p>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-4 label-animate">
                                        <label for="volume">Volume <sup>*</sup></label>
                                        <select class="form-control" id="volume" name="volume">
                                            <option {{ old() ? old('volume') == '' ? 'selected' : '' : ($equipament->volume == null ? 'selected' : '') }}>Selecione ...</option>
                                            <option value="3" {{ old() ? old('volume') == 3 ? 'selected' : '' : ($equipament->volume == 3 ? 'selected' : '') }}>3m³</option>
                                            <option value="4" {{ old() ? old('volume') == 4 ? 'selected' : '' : ($equipament->volume == 4 ? 'selected' : '')  }}>4m³</option>
                                            <option value="5" {{ old() ? old('volume') == 5 ? 'selected' : '' : ($equipament->volume == 5 ? 'selected' : '')  }}>5m³</option>
                                            <option value="6" {{ old() ? old('volume') == 6 ? 'selected' : '' : ($equipament->volume == 6 ? 'selected' : '')  }}>6m³</option>
                                            <option value="7" {{ old() ? old('volume') == 7 ? 'selected' : '' : ($equipament->volume == 7 ? 'selected' : '')  }}>7m³</option>
                                            <option value="8" {{ old() ? old('volume') == 8 ? 'selected' : '' : ($equipament->volume == 8 ? 'selected' : '')  }}>8m³</option>
                                            <option value="9" {{ old() ? old('volume') == 9 ? 'selected' : '' : ($equipament->volume == 9 ? 'selected' : '')  }}>9m³</option>
                                            <option value="10" {{ old() ? old('volume') == 10 ? 'selected' : '' : ($equipament->volume == 10 ? 'selected' : '')  }}>10m³</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="name">Nome do Equipamento <sup>*</sup></label>
                                        <input type="text" class="form-control" id="name" name="name" autocomplete="nope" value="{{ old('name') ?? $equipament->name }}">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="reference">Referência <sup>*</sup></label>
                                        <input type="text" class="form-control" id="reference" name="reference" autocomplete="nope" value="{{ old('reference') ?? $equipament->reference }}" required>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="manufacturer">Fabricante </label>
                                        <input type="text" class="form-control" id="manufacturer" name="manufacturer" autocomplete="nope" value="{{ old('manufacturer') ?? $equipament->manufacturer }}">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label for="value">Valor Por Dia</label>
                                        <input type="text" class="form-control" id="value" name="value" autocomplete="nope" value="{{ old('value') ?? $equipament->value }}">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="stock">Estoque</label>
                                        <label for="stock"></label><input type="text" class="form-control" id="stock" name="stock" autocomplete="nope" value="{{ old('stock') ?? $equipament->stock }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card display-none">
                            <div class="card-body">
                                <div class="header-card-body">
                                    <h4 class="card-title">Valores</h4>
                                    <p class="card-description"> Preencha o formulário abaixo com as informações de valores, defindo por intervalos</p>
                                </div>
                                @if (count($equipament_wallet) && !old('day_start'))
                                    @foreach($equipament_wallet as $countPeriod => $period)
                                        @php
                                            $numberNewPeriod = $countPeriod + 1;
                                        @endphp
                                        <div class="period">
                                            <div class="row">
                                                <div class="form-group col-md-2">
                                                    <label>{{ $numberNewPeriod }}º Período</label>
                                                </div>
                                                <div class="form-group col-md-3">
                                                    <label>Dia Inicial</label>
                                                    <input type="text" class="form-control" name="day_start[]" autocomplete="nope" value="{{ $period->day_start }}">
                                                </div>
                                                <div class="form-group col-md-3">
                                                    <label>Dia Final</label>
                                                    <input type="text" class="form-control" name="day_end[]" autocomplete="nope" value="{{ $period->day_end }}">
                                                </div>
                                                <div class="form-group col-md-3">
                                                    <label>Valor</label>
                                                    <input type="text" class="form-control" name="value_period[]" autocomplete="nope" value="{{ $period->value }}">
                                                </div>
                                                <div class="form-group col-md-1">
                                                    <label>&nbsp;</label>
                                                    <button type="button" class="btn btn-danger remove-period col-md-12"><i class="fa fa-trash"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                                @if (old('day_start') && count(old('day_start')))
                                    @for($period = 0; $period < count(old('day_start')); $period++)
                                        @php
                                            $numberNewPeriod = $period + 1;
                                        @endphp
                                        <div class="period">
                                            <div class="row">
                                                <div class="form-group col-md-2">
                                                    <label>{{ $numberNewPeriod }}º Período</label>
                                                </div>
                                                <div class="form-group col-md-3">
                                                    <label>Dia Inicial</label>
                                                    <input type="text" class="form-control" name="day_start[]" autocomplete="nope" value="{{ old('day_start')[$period] }}">
                                                </div>
                                                <div class="form-group col-md-3">
                                                    <label>Dia Final</label>
                                                    <input type="text" class="form-control" name="day_end[]" autocomplete="nope" value="{{ old('day_end')[$period] }}">
                                                </div>
                                                <div class="form-group col-md-3">
                                                    <label>Valor</label>
                                                    <input type="text" class="form-control" name="value_period[]" autocomplete="nope" value="{{ old('value_period')[$period] }}">
                                                </div>
                                                <div class="form-group col-md-1">
                                                    <label>&nbsp;</label>
                                                    <button type="button" class="btn btn-danger remove-period col-md-12"><i class="fa fa-trash"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                    @endfor
                                @endif
                                <div class="alert alert-warning mt-2 {{old() ? count(old('day_start'))?'display-none':'':(count($equipament_wallet)?'display-none':'')}}" id="no-have-period"><h4 class="text-center no-margin">Não existem períodos ainda.</h4></div>
                                <div id="new-periods" class="mt-2"></div>
                                <div class="col-md-12 text-center">
                                    <button type="button" class="btn btn-primary" id="add-new-period">Adicionar Novo Período</button>
                                </div>
                                <div class="col-md-12 text-center mt-2">
                                    <p class="text-danger">Caso opte por não adicionar períodos, no cadastro de uma nova locação será sugerido o valor por dia cadastrado no equipamento.</p>
                                </div>
                            </div>
                        </div>
                        <div class="card display-none">
                            <div class="card-body d-flex justify-content-between">
                                <a href="{{ route('equipament.index') }}" class="btn btn-secondary col-md-3"><i class="fa fa-arrow-left"></i> Cancelar</a>
                                <button type="submit" class="btn btn-success col-md-3"><i class="fa fa-sync"></i> Atualizar</button>
                            </div>
                        </div>
                        <input type="hidden" name="equipament_id" value="{{ $equipament->id }}">
                        {{ csrf_field() }}
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
