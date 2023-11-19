<div class="row">
    <div class="col-12 grid-margin">
        <div class="card card-statistics">
            <div class="row">
                {{-- Entregar hoje --}}
                <div class="col-md-6 grid-margin stretch-card content-graph">
                    <div class="card">
                        <div class="card-body graph">
                            <h4 class="card-title">Equipamentos Para Entregar Hoje</h4>
                            <div class="row d-flex justify-content-center">
                                <div class="col-md-7 aligner-wrapper">
                                    <canvas class="my-4 my-md-auto" id="cavasDeliveryToday"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="card-body table">
                            <div class="row mt-2">
                                <div class="col-md-12 table-responsive">
                                    <table id="tableRentalToDeliveryToday" class="table">
                                        <thead>
                                            <tr>
                                                <th>Locação</th>
                                                <th>Cliente/Endereço</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- Retirar hoje --}}
                <div class="col-md-6 grid-margin stretch-card content-graph">
                    <div class="card">
                        <div class="card-body graph">
                            <h4 class="card-title">Equipamentos Para Retirar Hoje</h4>
                            <div class="row d-flex justify-content-center">
                                <div class="col-md-7 aligner-wrapper">
                                    <canvas class="my-4 my-md-auto" id="cavasWithdrawToday"></canvas>
                                </div>

                            </div>
                        </div>
                        <div class="card-body table">
                            <div class="row mt-2">
                                <div class="col-md-12 table-responsive">
                                    <table id="tableRentalToWithdrawToday" class="table">
                                        <thead>
                                        <tr>
                                            <th>Locação</th>
                                            <th>Cliente/Endereço</th>
                                        </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                {{-- Contas a receber hoje --}}
                <div class="col-md-6 grid-margin stretch-card content-graph">
                    <div class="card">
                        <div class="card-body graph">
                            <h4 class="card-title">Contas a receber hoje</h4>
                            <div class="row d-flex justify-content-center">
                                <div class="col-md-7 aligner-wrapper">
                                    <canvas class="my-4 my-md-auto" id="cavasReceiveToday"></canvas>
                                </div>

                            </div>
                        </div>
                        <div class="card-body table">
                            <div class="row mt-2">
                                <div class="col-md-12 table-responsive">
                                    <table id="tableBillToReceiveToday" class="table">
                                        <thead>
                                            <tr>
                                                <th>Locação</th>
                                                <th>Cliente</th>
                                                <th>Valor</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- Contas A pagar Hoje --}}
                <div class="col-md-6 grid-margin stretch-card content-graph">
                    <div class="card">
                        <div class="card-body graph">
                            <h4 class="card-title">Contas A pagar Hoje</h4>
                            <div class="row d-flex justify-content-center">
                                <div class="col-md-7 aligner-wrapper">
                                    <canvas class="my-4 my-md-auto" id="cavasPayToday"></canvas>
                                </div>

                            </div>
                        </div>
                        <div class="card-body table">
                            <div class="row mt-2">
                                <div class="col-md-12 table-responsive">
                                    <table id="tableBillToPayToday" class="table">
                                        <thead>
                                            <tr>
                                                <th>Compra</th>
                                                <th>Fornecedor</th>
                                                <th>Valor</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<input type="hidden" id="route_bill_to_receive_today" value="{{ route('ajax.bills_to_receive.getBillsForDateAndClient') }}">
<input type="hidden" id="route_table_bill_to_receive_today" value="{{ route('ajax.bills_to_receive.fetchBillForDate') }}">
<input type="hidden" id="route_list_table_bill_to_receive_today" value="{{ route('bills_to_receive.index', array('filter_start_date' => dateNowInternational(null, DATE_INTERNATIONAL), 'filter_end_date' => dateNowInternational(null, DATE_INTERNATIONAL))) }}">

<input type="hidden" id="route_bill_to_pay_today" value="{{ route('ajax.bills_to_pay.getBillsForDateAndProvider') }}">
<input type="hidden" id="route_table_bill_to_pay_today" value="{{ route('ajax.bills_to_pay.fetchBillForDate') }}">
<input type="hidden" id="route_list_table_bill_to_pay_today" value="{{ route('bills_to_pay.index', array('filter_start_date' => dateNowInternational(null, DATE_INTERNATIONAL), 'filter_end_date' => dateNowInternational(null, DATE_INTERNATIONAL))) }}">

<input type="hidden" id="route_rental_to_delivery_today" value="{{ route('ajax.rental.getRentalsForDateAndClient', array('date' => dateNowInternational(null, DATE_INTERNATIONAL), 'type' => 'deliver')) }}">
<input type="hidden" id="route_table_rental_to_delivery_today" value="{{ route('ajax.rental.fetch') }}">
<input type="hidden" id="route_list_table_rental_to_delivery_today" value="{{ route('rental.index', array('filter_start_date' => dateNowInternational(null, DATE_INTERNATIONAL), 'filter_end_date' => dateNowInternational(null, DATE_INTERNATIONAL), 'date_filter_by' => 'expected_delivery')) }}">

<input type="hidden" id="route_rental_to_withdraw_today" value="{{ route('ajax.rental.getRentalsForDateAndClient', array('date' => dateNowInternational(null, DATE_INTERNATIONAL), 'type' => 'withdraw')) }}">
<input type="hidden" id="route_table_rental_to_withdraw_today" value="{{ route('ajax.rental.fetch') }}">
<input type="hidden" id="route_list_table_rental_to_withdraw_today" value="{{ route('rental.index', array('filter_start_date' => dateNowInternational(null, DATE_INTERNATIONAL), 'filter_end_date' => dateNowInternational(null, DATE_INTERNATIONAL), 'date_filter_by' => 'expected_withdraw')) }}">
