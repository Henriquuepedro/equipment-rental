<div class="card">
    <div class="card-body d-flex justify-content-around">
        <div class="form-radio form-radio-flat">
            <label class="form-check-label">
                <input {{ $disabled ?? '' }} type="radio" class="form-check-input" name="type_equipment" value="cacamba" {{ old() ? (old('type_equipment') === 'cacamba' ? 'checked' : '') : (isset($equipment) && $equipment->name === null ? 'checked' : '') }}> Caçamba <i class="input-helper"></i>
            </label>
        </div>
        <div class="form-radio form-radio-flat">
            <label class="form-check-label">
                <input {{ $disabled ?? '' }} type="radio" class="form-check-input" name="type_equipment" value="others" {{ old() ? (old('type_equipment') === 'others' ? 'checked' : '') : (isset($equipment) && $equipment->name !== null ? 'checked' : '') }}> Outros <i class="input-helper"></i>
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
                <select {{ $disabled ?? '' }} class="form-control" id="volume" name="volume">
                    <option {{ old() ? old('volume') == '' ? 'selected' : '' : (isset($equipment) && $equipment->volume == null ? 'selected' : '') }}>Selecione ...</option>
                    <option value="3" {{ old() ? old('volume') == 3 ? 'selected' : '' : (isset($equipment) && $equipment->volume == 3 ? 'selected' : '') }}>3m³</option>
                    <option value="4" {{ old() ? old('volume') == 4 ? 'selected' : '' : (isset($equipment) && $equipment->volume == 4 ? 'selected' : '')  }}>4m³</option>
                    <option value="5" {{ old() ? old('volume') == 5 ? 'selected' : '' : (isset($equipment) && $equipment->volume == 5 ? 'selected' : '')  }}>5m³</option>
                    <option value="6" {{ old() ? old('volume') == 6 ? 'selected' : '' : (isset($equipment) && $equipment->volume == 6 ? 'selected' : '')  }}>6m³</option>
                    <option value="7" {{ old() ? old('volume') == 7 ? 'selected' : '' : (isset($equipment) && $equipment->volume == 7 ? 'selected' : '')  }}>7m³</option>
                    <option value="8" {{ old() ? old('volume') == 8 ? 'selected' : '' : (isset($equipment) && $equipment->volume == 8 ? 'selected' : '')  }}>8m³</option>
                    <option value="9" {{ old() ? old('volume') == 9 ? 'selected' : '' : (isset($equipment) && $equipment->volume == 9 ? 'selected' : '')  }}>9m³</option>
                    <option value="10" {{ old() ? old('volume') == 10 ? 'selected' : '' : (isset($equipment) && $equipment->volume == 10 ? 'selected' : '')  }}>10m³</option>
                </select>
            </div>
            <div class="form-group col-md-4">
                <label for="name">Nome do Equipamento <sup>*</sup></label>
                <input {{ $disabled ?? '' }} type="text" class="form-control" id="name" name="name" autocomplete="nope" value="{{ old('name') ?? (isset($equipment) ? $equipment->name : '') }}">
            </div>
            <div class="form-group col-md-4">
                <label for="reference">Referência <sup>*</sup></label>
                <input {{ $disabled ?? '' }} type="text" class="form-control" id="reference" name="reference" autocomplete="nope" value="{{ old('reference') ?? (isset($equipment) ? $equipment->reference : '') }}" required>
            </div>
            <div class="form-group col-md-4">
                <label for="manufacturer">Fabricante </label>
                <input {{ $disabled ?? '' }} type="text" class="form-control" id="manufacturer" name="manufacturer" autocomplete="nope" value="{{ old('manufacturer') ?? (isset($equipment) ? $equipment->manufacturer : '') }}">
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-4">
                <label for="value">Valor Por Dia</label>
                <input {{ $disabled ?? '' }} type="text" class="form-control" id="value" name="value" autocomplete="nope" value="{{ old('value') ?? (isset($equipment) ? $equipment->value : '') }}">
            </div>
            <div class="form-group col-md-4">
                <label for="stock">Estoque</label>
                <input {{ $disabled ?? '' }} type="text" class="form-control" id="stock" name="stock" autocomplete="nope" value="{{ old('stock') ?? (isset($equipment) ? $equipment->stock : '') }}">
                <small>Estoque disponível: <span class="font-weight-bold available_stock"></span></small>
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
        @if (!empty($dataEquipmentWallet) && !old('day_start'))
            @foreach($dataEquipmentWallet as $countPeriod => $period)
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
                            <input {{ $disabled ?? '' }} type="text" class="form-control" name="day_start[]" autocomplete="nope" value="{{ $period['day_start'] }}">
                        </div>
                        <div class="form-group col-md-3">
                            <label>Dia Final</label>
                            <input {{ $disabled ?? '' }} type="text" class="form-control" name="day_end[]" autocomplete="nope" value="{{ $period['day_end'] }}">
                        </div>
                        <div class="form-group col-md-3">
                            <label>Valor</label>
                            <input {{ $disabled ?? '' }} type="text" class="form-control" name="value_period[]" autocomplete="nope" value="{{ $period['value'] }}">
                        </div>
                        <div class="col-md-1">
                            <label>&nbsp;</label>
                            @if ($btns ?? true)<button type="button" class="btn btn-danger remove-period col-md-12"><i class="fa fa-trash"></i></button>@endif
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
                            <input {{ $disabled ?? '' }} type="text" class="form-control" name="day_start[]" autocomplete="nope" value="{{ old('day_start')[$period] }}">
                        </div>
                        <div class="form-group col-md-3">
                            <label>Dia Final</label>
                            <input {{ $disabled ?? '' }} type="text" class="form-control" name="day_end[]" autocomplete="nope" value="{{ old('day_end')[$period] }}">
                        </div>
                        <div class="form-group col-md-3">
                            <label>Valor</label>
                            <input {{ $disabled ?? '' }} type="text" class="form-control" name="value_period[]" autocomplete="nope" value="{{ old('value_period')[$period] }}">
                        </div>
                        <div class="col-md-1">
                            <label>&nbsp;</label>
                            @if ($btns ?? true)<button type="button" class="btn btn-danger remove-period col-md-12"><i class="fa fa-trash"></i></button>@endif
                        </div>
                    </div>
                </div>
            @endfor
        @endif
        @if ($btns ?? true)
            <div id="new-periods" class="mt-2"></div>
            <div class="col-md-12 text-center mt-2">
                <button type="button" class="btn btn-primary" id="add-new-period">Adicionar Novo Período</button>
            </div>
        @endif
        <div class="col-md-12 text-center mt-2">
            <p class="text-danger">Caso opte por não adicionar períodos, no cadastro de uma nova locação será sugerido o valor por dia cadastrado no equipamento.</p>
        </div>
    </div>
</div>
