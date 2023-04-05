<div class="modal fade" id="newDriverModal" tabindex="1" role="dialog" aria-labelledby="newDriverModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <form action="{{ route('ajax.driver.new-driver') }}" method="POST" id="formCreateDriverModal">
                <div class="modal-header">
                    <h5 class="modal-title" id="newDriverModalLabel">Cadastro de novo motorista</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="form-group col-md-5">
                            <label for="name">Nome do Motorista <sup>*</sup></label>
                            <input type="text" class="form-control" name="name" autocomplete="nope" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" name="email" autocomplete="nope">
                        </div>
                        <div class="form-group col-md-3">
                            <label for="phone">Telefone</label>
                            <input type="text" class="form-control" name="phone" autocomplete="nope">
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-3">
                            <label for="cpf">CPF</label>
                            <input type="text" class="form-control" name="cpf" autocomplete="nope">
                        </div>
                        <div class="form-group col-md-3">
                            <label for="rg">RG</label>
                            <input type="text" class="form-control" name="rg" autocomplete="nope">
                        </div>
                        <div class="form-group col-md-3">
                            <label for="cnh">CNH</label>
                            <input type="text" class="form-control" name="cnh" autocomplete="nope">
                        </div>
                        <div class="form-group col-md-3">
                            <label for="cnh_exp">Expiração CNH</label>
                            <input type="date" class="form-control" name="cnh_exp" autocomplete="nope">
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-12">
                            <label for="observation">Observação</label>
                            <textarea class="form-control" name="observation" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-around">
                    <button type="button" class="btn btn-secondary col-md-3" data-dismiss="modal"><i class="fa fa-times"></i> Cancelar</button>
                    <button type="submit" class="btn btn-success col-md-3"><i class="fa fa-save"></i> Cadastrar</button>
                </div>
            </form>
        </div>
    </div>
</div>
