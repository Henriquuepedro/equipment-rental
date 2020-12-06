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
        <label>Endereço</label>
        <input type="text" name="address" class="form-control">
    </div>
</div>
<div class="row show-address">
    <div class="form-group col-md-4">
        <label>Número</label>
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
        <label>Bairro</label>
        <input type="text" name="neigh" class="form-control">
    </div>
</div>
<div class="row show-address">
    <div class="form-group col-md-6">
        <label>Cidade</label>
        <input type="text" name="city" class="form-control">
    </div>
    <div class="form-group col-md-6">
        <label>Estado</label>
        <input type="text" name="state" class="form-control">
    </div>
</div>
