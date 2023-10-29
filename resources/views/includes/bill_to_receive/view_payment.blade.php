<div class="modal fade" id="modalViewPayment" tabindex="-1" role="dialog" aria-labelledby="modalViewPayment" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="form-group col-md-3">
                        <label>Locação</label>
                        <input type="text" class="form-control" name="rental_code" value="" disabled>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Cliente</label>
                        <input type="text" class="form-control" name="client" value="" disabled>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Data da Locação</label>
                        <input type="text" class="form-control" name="date_rental" value="" disabled>
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
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary col-md-3" data-dismiss="modal"><i class="fa fa-times"></i> Cancelar</button>
            </div>
        </div>
    </div>
</div>
