<div class="modal fade" id="newVehicleModal" tabindex="-1" role="dialog" aria-labelledby="newVehicleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <form action="{{ route('ajax.vehicle.new-vehicle') }}" method="POST" id="formCreateVehicleModal">
                <div class="modal-header">
                    <h5 class="modal-title" id="newVehicleModalLabel">Cadastro de novo veículo</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="form-group col-md-5">
                                    <label for="name">Nome do Veículo <sup>*</sup></label>
                                    <input type="text" class="form-control" id="name" name="name" autocomplete="nope" required>
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="reference">Referência</label>
                                    <input type="text" class="form-control" id="reference" name="reference" autocomplete="nope">
                                </div>
                                <div class="form-group col-md-4 label-animate">
                                    <label>Motorista Responsável</label>
                                    <div class="input-group driver-load">
                                        <select class="form-control label-animate" name="driver"></select>
                                        <div class="input-group-addon input-group-append">
                                            <button type="button" class="btn btn-success" id="btnModalNewDriverModal" title="Novo Motorista" {{ in_array('DriverCreatePost', $permissions) ? '' : 'disabled' }}><i class="fa fa-user-plus"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label for="brand">Marca</label>
                                    <input type="text" class="form-control" id="brand" name="brand" autocomplete="nope">
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="model">Modelo</label>
                                    <input type="text" class="form-control" id="model" name="model" autocomplete="nope">
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="board">Placa</label>
                                    <input type="text" class="form-control" id="board" name="board" autocomplete="nope">
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-12">
                                    <label for="observation">Observação</label>
                                    <textarea class="form-control" id="observation" name="observation" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-around">
                    <button type="button" class="btn btn-secondary col-md-3" data-bs-dismiss="modal"><i class="fa fa-times"></i> Cancelar</button>
                    <button type="submit" class="btn btn-success col-md-3"><i class="fa fa-save"></i> Cadastrar</button>
                </div>
            </form>
        </div>
    </div>
    <input type="hidden" name="element_to_load">
</div>
