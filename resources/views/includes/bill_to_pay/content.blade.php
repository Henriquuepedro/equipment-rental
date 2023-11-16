<div class="card" id="contentListBillToPay">
    <div class="card-body">
        <div class="header-card-body justify-content-between flex-wrap @if (!$card_title) d-none @endif">
            <h4 class="card-title no-border">Contas a Pagar</h4>
            @if(in_array('BillsToPayCreatePost', $permissions))
                <a href="{{ route('bills_to_pay.create') }}" class="mb-3 btn btn-primary col-md-3 btn-rounded btn-fw"><i class="fas fa-plus"></i> Nova Compra</a>
            @endif
        </div>
        <div class="row d-flex justify-content-center">
            <div class="col-md-9 form-group @if ($show_select_provider === false) d-none @endif">
                <label>Fornecedor</label>
                <select class="form-control" name="providers">
                    @if ($show_select_provider === true)
                        <option value="0">Todos</option>
                        @foreach($providers as $provider)
                            <option value="{{ $provider->id }}" {{ ($provider_id_selected ?? 0) == $provider->id ? 'selected' : '' }}>{{ $provider->name }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
            <div class="col-md-3 form-group">
                <label>Data do Vencimento</label>
                <input type="text" name="intervalDates" class="form-control" value="{{ ($billStartFilterDate ?? $settings['intervalBillDates']['start']) . ' - ' . ($billEndFilterDate ?? $settings['intervalBillDates']['finish']) }}" />
            </div>
        </div>
        <div class="nav-scroller mt-3">
            <ul class="nav nav-tabs tickets-tab-switch justify-content-center" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="without_pay-tab" data-toggle="tab" href="#without_pay" role="tab" aria-controls="without_pay" aria-selected="false">Não Pago<div class="badge">50 </div></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="paid-tab" data-toggle="tab" href="#paid" role="tab" aria-controls="paid" aria-selected="false">Pago<div class="badge">29 </div>
                    </a>
                </li>
            </ul>
        </div>
        <div class="tab-content tab-content-basic">
            <div class="tab-pane fade show active" id="without_pay" role="tabpanel" aria-labelledby="without_pay">

            </div>
            <div class="tab-pane fade" id="paid" role="tabpanel" aria-labelledby="paid">

            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-12">
                <table id="tableBillsToPay" class="table table-bordered mt-2">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Fornecedor</th>
                        <th>Valor</th>
                        <th>Vencimento</th>
                        <th>Ação</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                    <tr>
                        <th>#</th>
                        <th>Fornecedor</th>
                        <th>Valor</th>
                        <th>Vencimento</th>
                        <th>Ação</th>
                    </tr>
                    </tfoot>
                </table>
            </div>
            <div class="col-md-12 values d-flex justify-content-end flex-wrap text-right">
                <span class="col-md-12">Valores Selecionados <b class="price">R$ 0,00</b></span>
                <span class="col-md-12">Pagamentos Selecionados <b class="quantity">0</b></span>
            </div>
        </div>
    </div>
</div>
<input type="hidden" name="route_bills_to_pay_fetch" value="{{ route('ajax.bills_to_pay.fetch') }}">
<input type="hidden" name="route_bills_to_pay_get_qty_type_rentals" value="{{ route('ajax.bills_to_pay.get-qty-type-bills') }}">
