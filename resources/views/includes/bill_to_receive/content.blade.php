<div class="card" id="contentListBillToReceive">
    <div class="card-body">
        <div class="header-card-body justify-content-between flex-wrap @if (!$card_title) d-none @endif">
            <h4 class="card-title no-border">{{ $card_title }}</h4>
        </div>
        <div class="row d-flex justify-content-center">
            <div class="col-md-9 form-group @if ($show_select_client === false) d-none @endif">
                <label>Cliente</label>
                <select class="form-control" name="clients">
                    @if ($show_select_client === true)
                        <option value="0">Todos</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ ($client_id_selected ?? 0) == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
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
                    <a class="nav-link active" id="without_pay-tab" data-bs-toggle="tab" href="#without_pay" role="tab" aria-controls="without_pay" aria-selected="false">Não Pago<div class="badge">50 </div></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="paid-tab" data-bs-toggle="tab" href="#paid" role="tab" aria-controls="paid" aria-selected="false">Pago<div class="badge">29 </div>
                    </a>
                </li>
            </ul>
        </div>
        <div class="tab-content tab-content-basic d-none">
            <div class="tab-pane fade show active" id="without_pay" role="tabpanel" aria-labelledby="without_pay">

            </div>
            <div class="tab-pane fade" id="paid" role="tabpanel" aria-labelledby="paid">

            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-12">
                <table id="tableBillsToReceive" class="table mt-2">
                    <thead>
                    <tr>
                        <th>Locação</th>
                        <th>Cliente/Endereço</th>
                        <th>Valor</th>
                        <th>Vencimento</th>
                        <th>Ação</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                    <tr>
                        <th>Locação</th>
                        <th>Cliente/Endereço</th>
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
<input type="hidden" name="route_bills_to_receive_fetch" value="{{ route('ajax.bills_to_receive.fetch') }}">
<input type="hidden" name="route_bills_to_receive_get_qty_type_rentals" value="{{ route('ajax.bills_to_receive.get-qty-type-rentals') }}">
