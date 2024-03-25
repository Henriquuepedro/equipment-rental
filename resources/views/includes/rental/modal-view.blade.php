<div class="modal fade" id="viewRental" tabindex="-1" role="dialog" aria-labelledby="newViewRentalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newViewRentalLabel">Visualizar Locação</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="d-flex justify-content-around col-md-12">
                                <div class="">
                                    <input type="radio" name="type_rental" id="have-payment" value="0">
                                    <label for="have-payment">Com Cobrança</label>
                                </div>
                                <div class="">
                                    <input type="radio" name="type_rental" id="no-have-payment" value="1">
                                    <label for="no-have-payment">Sem Cobrança</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-12">
                                <label>Cliente</label>
                                <input type="text" name="client" class="form-control">
                            </div>
                        </div>
                        @include('includes.address.form')
                        <div class="row mt-3">
                            <div class="col-md-12 form-group">
                                <div id="mapRental" style="height: 400px"></div>
                            </div>
                        </div>
                        <div class="row mt-2 content-residues">
                            <div class="form-group col-md-12 label-animate container-residues">
                                <label>Resíduos</label>
                                <div class="input-group label-animate">
                                    <select class="select2 form-control" multiple="multiple" name="residues[]" disabled></select>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-2 content-equipments">
                            <div class="form-group col-md-12">
                                <h5>Equipamentos</h5>
                            </div>
                        </div>
                        <div class="accordion accordion-solid-header equipments" role="tablist"></div>
                        <div class="text-center loading-equipments"><h5><i class="fa fa-spin fa-spinner"></i> Carregando</h5></div>
                        <div class="row mt-2 mb-1 content-payments">
                            <div class="form-group col-md-12">
                                <hr class="bg-white m-0">
                            </div>
                        </div>
                        <div class="row mt-2 content-payments">
                            <div class="form-group col-md-12">
                                <h5>Pagamentos</h5>
                            </div>
                        </div>
                        <div class="row mt-2 content-payments">
                            <div class="col-md-12">
                                <div class="form-group mt-1">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="input-group col-md-12 no-padding">
                                            <span class="col-md-1 font-weight-bold text-center">Situação</span>
                                            <span class="col-md-3 font-weight-bold text-center">Data do vencimento</span>
                                            <span class="col-md-2 font-weight-bold text-center">Valor do pagamento</span>
                                            <span class="col-md-3 font-weight-bold text-center">Data do pagamento</span>
                                            <span class="col-md-3 font-weight-bold text-center">Forma do pagamento</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="accordion accordion-solid-header payments" role="tablist"></div>
                        <div class="row mt-2 mb-1 content-observation">
                            <div class="form-group col-md-12">
                                <hr class="bg-white m-0">
                            </div>
                        </div>
                        <div class="row mt-2 content-observation">
                            <div class="form-group col-md-12">
                                <h5>Observação</h5>
                            </div>
                        </div>
                        <div class="row content-observation">
                            <div class="form-group col-md-12">
                                <div class="observation border p-2"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-around">
                <button type="button" class="btn btn-secondary col-md-3" data-bs-dismiss="modal"><i class="fa fa-times"></i> Fechar</button>
            </div>
        </div>
    </div>
</div>
