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
                        <label>Código do Lançamento</label>
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
                <div class="row">
                    <div class="form-group col-md-12">
                        <p class="mb-0 pt-3 pl-1 bbl-r-0">Descrição</p>

                        <div id="observationDiv" class="quill-container border btr-r-5 btl-r-5 bbr-r-5 bbl-r-5" contenteditable="true"></div>
                        <textarea type="hidden" class="d-none" name="observation" id="observation"></textarea>



{{--                        <div class="observation border p-2 btr-r-5 btl-r-5 bbr-r-5 bbl-r-5"></div>--}}
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary col-md-3" data-dismiss="modal"><i class="fa fa-times"></i> Cancelar</button>
            </div>
        </div>
    </div>
</div>
