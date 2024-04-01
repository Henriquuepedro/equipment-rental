<div class="modal fade" id="newClientModal" tabindex="-1" role="dialog" aria-labelledby="newClientModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <form action="{{ route('ajax.client.new-client') }}" method="POST" id="formCreateClientModal">
                <div class="modal-header">
                    <h5 class="modal-title" id="newClientModalLabel">Cadastro de novo cliente</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="card">
                        <div class="card-body d-flex justify-content-around">
                            <div class="form-radio form-radio-flat">
                                <label class="form-check-label">
                                    <input type="radio" class="form-check-input" name="type_person" value="pf"> Pessoa Física <i class="input-helper"></i>
                                </label>
                            </div>
                            <div class="form-radio form-radio-flat">
                                <label class="form-check-label">
                                    <input type="radio" class="form-check-input" name="type_person" value="pj"> Pessoa Jurídica <i class="input-helper"></i>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="card mt-2 display-none">
                        <div class="card-body">
                            <div class="header-card-body">
                                <h4 class="card-title">Dados do Cliente</h4>
                                <p class="card-description"> Preencha o formulário abaixo com as informações do novo cliente </p>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-10">
                                    <label for="name_client">Nome do Cliente <sup>*</sup></label>
                                    <input type="text" class="form-control" id="name_client" name="name_client" autocomplete="nope" required>
                                </div>
                                <div class="form-group col-md-5 d-none">
                                    <label for="fantasy_client">Fantasia</label>
                                    <input type="text" class="form-control" id="fantasy_client" name="fantasy_client" autocomplete="nope">
                                </div>
                                <div class="form-group col-md-2">
                                    <div class="switch d-flex mt-4">
                                        <input type="checkbox" class="check-style check-xs" name="active" id="active" checked>
                                        <label for="active" class="check-style check-xs"></label>&nbsp;Ativo
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label for="cpf_cnpj">CPF</label>
                                    <input type="text" class="form-control" id="cpf_cnpj" name="cpf_cnpj" autocomplete="nope" >
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="rg_ie">RG</label>
                                    <input type="text" class="form-control" id="rg_ie" name="rg_ie" autocomplete="nope">
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="contact">Contato</label>
                                    <input type="text" class="form-control" id="contact" name="contact" autocomplete="nope" >
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label for="phone_1">Telefone Principal</label>
                                    <input type="text" class="form-control" id="phone_1" name="phone_1" autocomplete="nope">
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="phone_2">Telefone Secundário</label>
                                    <input type="text" class="form-control" id="phone_2" name="phone_2" autocomplete="nope">
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="email">Endereço de E-mail</label>
                                    <input type="email" class="form-control" id="email" name="email" autocomplete="nope">
                                </div>
                            </div>
                            <div class="row personal_data">
                                <div class="form-group col-md-4">
                                    <label for="sex" style="top: 15px; left: 0;">Sexo</label><br>
                                    <input type="radio" id="sex_1" name="sex" value="1" style="position: relative; top: 15px;"> <label for="sex_1" style="top: 17px; left: 0; pointer-events: none;">Masculino</label>
                                    <input type="radio" id="sex_2" name="sex" value="2" style="position: relative; top: 15px;"> <label for="sex_2" style="top: 17px; left: 0; pointer-events: none;">Feminino</label>
                                    <input type="radio" id="sex_3" name="sex" value="3" style="position: relative; top: 15px;"> <label for="sex_3" style="top: 17px; left: 0; pointer-events: none;">Outro</label>
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="birth_date">Data de Nascimento</label>
                                    <input type="date" class="form-control" id="birth_date" name="birth_date" autocomplete="nope">
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="nationality">Nacionalidade</label>
                                    <select class="form-control" id="nationality" name="nationality"></select>
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="marital_status">Estado Civíl</label>
                                    <select class="form-control" id="marital_status" name="marital_status"></select>
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
                    <div class="card mt-2 display-none">
                        <div class="card-body">
                            <div class="header-card-body">
                                <h4 class="card-title">Dados do Endereço</h4>
                                <p class="card-description"> Preencha o formulário abaixo com as informações de endereço </p>
                            </div>
                            <table class="table col-md-12 display-none" id="tableAddressClient">
                                <thead>
                                <tr>
                                    <th>Identificação</th>
                                    <th>CEP</th>
                                    <th>Endereço</th>
                                    <th>Cidade/Estado</th>
                                    <th>Ação</th>
                                </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                            <div id="new-addressses"></div>
                            <div class="col-md-12 text-center pt-4">
                                <button type="button" class="btn btn-primary" id="add-new-address">Adicionar Novo Endereço</button>
                            </div>
                        </div>
                    </div>
                    <div class="card mt-2 display-none">
                        <div class="card-body d-flex justify-content-between">
                            <button type="button" class="btn btn-secondary col-md-3" data-bs-dismiss="modal"><i class="fa fa-times"></i> Cancelar</button>
                            <button type="submit" class="btn btn-success col-md-3"><i class="fa fa-save"></i> Cadastrar</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" tabindex="-1" role="dialog" id="confirmAddress">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Endereço no Mapa</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12 form-group text-center mb-2">
                        <button type="button" class="btn btn-primary" id="updateLocationMap"><i class="fas fa-sync-alt"></i> Atualizar Localização</button>
                    </div>
                </div>
                <div class="row">
                    <div id="map" style="height: 400px"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary col-md-3" data-bs-dismiss="modal">Confirmar</button>
            </div>
        </div>
    </div>
</div>
