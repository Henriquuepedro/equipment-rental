<div class="modal fade" id="modalReopenPayment" tabindex="-1" role="dialog" aria-labelledby="modalReopenPayment" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <form action="{{ route('ajax.bills_to_pay.reopen_payment') }}" method="POST" id="formReopenPayment">
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="form-group col-md-3">
                                    <label>Locação</label>
                                    <input type="text" class="form-control" name="bill_code" value="" disabled>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Fornecedor</label>
                                    <input type="text" class="form-control" name="provider" value="" disabled>
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Data da Locação</label>
                                    <input type="text" class="form-control" name="date_bill" value="" disabled>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-3">
                                    <label>Data de Vencimento</label>
                                    <input type="text" class="form-control" name="due_date" value="" disabled>
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Valor</label>
                                    <input type="text" class="form-control" name="due_value" value="" disabled>
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Forma de Pagamento</label>
                                    <select class="form-control" name="form_payment" disabled></select>
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Data de Pagamento</label>
                                    <input type="text" class="form-control" name="date_payment" value="" disabled>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-around">
                    <button type="button" class="btn btn-secondary col-md-3" data-bs-dismiss="modal"><i class="fa fa-times"></i> Cancelar</button>
                    <button type="submit" class="btn btn-success col-md-3"><i class="fa-solid fa-rotate-left"></i> Reabrir Pagamento</button>
                </div>
                <input type="hidden" class="form-control" name="payment_id">
            </form>
        </div>
    </div>
</div>
