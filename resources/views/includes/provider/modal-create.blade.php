<div class="modal fade" id="newProviderModal" tabindex="-1" role="dialog" aria-labelledby="newProviderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <form action="{{ route('ajax.provider.new-provider') }}" method="POST" id="formCreateProviderModal">
                <div class="modal-header">
                    <h5 class="modal-title" id="newProviderModalLabel">Cadastro de novo Fornecedor</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
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
                    <div class="card display-none">
                        <div class="card-body">
                            <div class="header-card-body">
                                <h4 class="card-title">Dados do Fornecedor</h4>
                                <p class="card-description"> Preencha o formulário abaixo com as informações do novo fornecedor </p>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-12">
                                    <label for="name">Nome do Fornecedor <sup>*</sup></label>
                                    <input type="text" class="form-control" id="name" name="name" autocomplete="nope" required>
                                </div>
                                <div class="form-group col-md-6 d-none">
                                    <label for="fantasy">Fantasia</label>
                                    <input type="text" class="form-control" id="fantasy" name="fantasy" autocomplete="nope">
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label for="cpf_cnpj">CPF</label>
                                    <input type="text" class="form-control" id="cpf_cnpj" name="cpf_cnpj" autocomplete="nope">
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="rg_ie">RG</label>
                                    <input type="text" class="form-control" id="rg_ie" name="rg_ie" autocomplete="nope">
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="contact">Contato</label>
                                    <input type="text" class="form-control" id="contact" name="contact" autocomplete="nope">
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
                    <div class="card display-none">
                        <div class="card-body">
                            <div class="row">
                                <div class="form-group col-md-3">
                                    <label>CEP</label>
                                    <input type="text" class="form-control" name="cep" autocomplete="nope">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Endereço</label>
                                    <input type="text" class="form-control" name="address" autocomplete="nope">
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Número</label>
                                    <input type="text" class="form-control" name="number" autocomplete="nope">
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label>Complemento</label>
                                    <input type="text" class="form-control" name="complement" autocomplete="nope">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Referência</label>
                                    <input type="text" class="form-control" name="reference" autocomplete="nope">
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label>Bairro</label>
                                    <input type="text" class="form-control" name="neigh" autocomplete="nope">
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Estado</label>
                                    <select class="form-control" name="state"></select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Cidade</label>
                                    <select class="form-control" name="city"></select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card display-none">
                        <div class="card-body d-flex justify-content-between">
                            <a href="{{ route('provider.index') }}" class="btn btn-secondary col-md-3"><i class="fa fa-arrow-left"></i> Cancelar</a>
                            <button type="submit" class="btn btn-success col-md-3"><i class="fa fa-save"></i> Cadastrar</button>
                        </div>
                    </div>
                    {{ csrf_field() }}
                </div>
            </form>
        </div>
    </div>
</div>
