<div class="modal fade" id="newEquipamentModal" tabindex="-1" role="dialog" aria-labelledby="newEquipamentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <form action="{{ route('ajax.equipament.new-equipament') }}" method="POST" id="formEquipament">
                <div class="modal-header">
                    <h5 class="modal-title" id="newEquipamentModalLabel">Cadastro de novo equipamento</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="card">
                        <div class="card-body d-flex justify-content-around">
                            <div class="form-radio form-radio-flat">
                                <label class="form-check-label">
                                    <input type="radio" class="form-check-input" name="type_equipament" value="cacamba"> Caçamba <i class="input-helper"></i>
                                </label>
                            </div>
                            <div class="form-radio form-radio-flat">
                                <label class="form-check-label">
                                    <input type="radio" class="form-check-input" name="type_equipament" value="others"> Outros <i class="input-helper"></i>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="card display-none">
                        <div class="card-body">
                            <div class="header-card-body">
                                <h4 class="card-title">Dados do Equipamento</h4>
                                <p class="card-description"> Preencha o formulário abaixo com as informações do novo equipamento </p>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-4 label-animate">
                                    <label for="volume">Volume <sup>*</sup></label>
                                    <select class="form-control" id="volume" name="volume">
                                        <option>Selecione ...</option>
                                        <option value="3">3m³</option>
                                        <option value="4">4m³</option>
                                        <option value="5">5m³</option>
                                        <option value="6">6m³</option>
                                        <option value="7">7m³</option>
                                        <option value="8">8m³</option>
                                        <option value="9">9m³</option>
                                        <option value="10">10m³</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="name">Nome do Equipamento <sup>*</sup></label>
                                    <input type="text" class="form-control" id="name" name="name" autocomplete="nope">
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="reference">Referência <sup>*</sup></label>
                                    <input type="text" class="form-control" id="reference" name="reference" autocomplete="nope" required>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="manufacturer">Fabricante </label>
                                    <input type="text" class="form-control" id="manufacturer" name="manufacturer" autocomplete="nope">
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label for="value">Valor Por Dia</label>
                                    <input type="text" class="form-control" id="value" name="value" autocomplete="nope">
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="stock">Estoque</label>
                                    <label for="stock"></label><input type="text" class="form-control" id="stock" name="stock" autocomplete="nope">
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
                            <div id="new-periods" class="mt-2"></div>
                            <div class="col-md-12 text-center mt-2">
                                <button type="button" class="btn btn-primary" id="add-new-period">Adicionar Novo Período</button>
                            </div>
                            <div class="col-md-12 text-center mt-2">
                                <p class="text-danger">Caso opte por não adicionar períodos, no cadastro de uma nova locação será sugerido o valor por dia cadastrado no equipamento.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-around">
                    <button type="button" class="btn btn-secondary col-md-3" data-dismiss="modal"><i class="fa fa-times"></i> Cancelar</button>
                    <button type="submit" class="btn btn-success col-md-3 display-none"><i class="fa fa-save"></i> Cadastrar</button>
                </div>
            </form>
        </div>
    </div>
</div>
