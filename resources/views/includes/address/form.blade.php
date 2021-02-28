<div class="row show-address">
    <div class="form-group col-md-12 label-animate">
        <label>Endereços</label>
        <select {{ $disabled ?? '' }} class="form-control label-animate" name="name_address" required>
            <option>Não selecionado</option>
        </select>
    </div>
</div>
<div class="row show-address">
    <div class="form-group col-md-4">
        <label>CEP</label>
        <input type="tel" name="cep" class="form-control">
    </div>
    <div class="form-group col-md-8">
        <label>Endereço <sup>*</sup></label>
        <input type="text" name="address" class="form-control">
    </div>
</div>
<div class="row show-address">
    <div class="form-group col-md-4">
        <label>Número <sup>*</sup></label>
        <input type="text" name="number" class="form-control">
    </div>
    <div class="form-group col-md-8">
        <label>Complemento</label>
        <input type="text" name="complement" class="form-control">
    </div>
</div>
<div class="row show-address">
    <div class="form-group col-md-7">
        <label>Referência</label>
        <input type="text" name="reference" class="form-control">
    </div>
    <div class="form-group col-md-5">
        <label>Bairro <sup>*</sup></label>
        <input type="text" name="neigh" class="form-control">
    </div>
</div>
<div class="row show-address">
    <div class="form-group col-md-6">
        <label>Cidade <sup>*</sup></label>
        <input type="text" name="city" class="form-control">
    </div>
    <div class="form-group col-md-6">
        <label>Estado <sup>*</sup></label>
        <input type="text" name="state" class="form-control">
    </div>
</div>
<div class="row show-address mt-2">
    <div class="form-group col-md-12 text-center">
        <button type="button" class="btn btn-primary col-md-9" id="confirmAddressMap">Confirmar Endereço no Mapa</button>
    </div>
</div>
<input type="hidden" name="lat">
<input type="hidden" name="lng">
