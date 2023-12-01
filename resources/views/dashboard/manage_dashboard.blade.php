<div class="row">
    <div class="col-12 grid-margin">
        <div class="row">
            <div class="col-xl-3 col-lg-3 col-md-3 col-sm-6 grid-margin stretch-card">
                <div class="card card-statistics">
                    <div class="card-body">
                        <div class="clearfix">
                            <div class="float-left">
                                <i class="mdi mdi-account text-danger icon-lg"></i>
                            </div>
                            <div class="d-flex justify-content-center pt-2">
                                <p class="mb-0 text-right"></p>
                                <div class="fluid-container">
                                    <h3 class="font-weight-medium text-right mb-0" style="font-size: 35px">{{ $indicator['clients'] }}</h3>
                                </div>
                            </div>
                        </div>
                        <p class="text-muted mt-3 mb-0">
                            <i class="mdi mdi-account mr-1" aria-hidden="true"></i>Clientes ativos
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-3 col-md-3 col-sm-6 grid-margin stretch-card">
                <div class="card card-statistics">
                    <div class="card-body">
                        <div class="clearfix">
                            <div class="float-left">
                                <i class="mdi mdi-truck text-warning icon-lg"></i>
                            </div>
                            <div class="d-flex justify-content-center pt-2">
                                <p class="mb-0 text-right"></p>
                                <div class="fluid-container">
                                    <h3 class="font-weight-medium text-right mb-0" style="font-size: 35px">{{ $indicator['providers'] }}</h3>
                                </div>
                            </div>
                        </div>
                        <p class="text-muted mt-3 mb-0">
                            <i class="mdi mdi-truck mr-1" aria-hidden="true"></i>Fornecedores
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-3 col-md-3 col-sm-6 grid-margin stretch-card">
                <div class="card card-statistics">
                    <div class="card-body">
                        <div class="clearfix">
                            <div class="float-left">
                                <i class="mdi mdi-cube text-success icon-lg"></i>
                            </div>
                            <div class="d-flex justify-content-center pt-2">
                                <p class="mb-0 text-right"></p>
                                <div class="fluid-container">
                                    <h3 class="font-weight-medium text-right mb-0" style="font-size: 35px">{{ $indicator['equipments'] }}</h3>
                                </div>
                            </div>
                        </div>
                        <p class="text-muted mt-3 mb-0">
                            <i class="mdi mdi-cube mr-1" aria-hidden="true"></i>Equipamentos
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-3 col-md-3 col-sm-6 grid-margin stretch-card">
                <div class="card card-statistics">
                    <div class="card-body">
                        <div class="clearfix">
                            <div class="float-left">
                                <i class="mdi mdi-car text-info icon-lg"></i>
                            </div>
                            <div class="d-flex justify-content-center pt-2">
                                <p class="mb-0 text-right"></p>
                                <div class="fluid-container">
                                    <h3 class="font-weight-medium text-right mb-0" style="font-size: 35px">{{ $indicator['vehicles'] }}</h3>
                                </div>
                            </div>
                        </div>
                        <p class="text-muted mt-3 mb-0">
                            <i class="mdi mdi-car mr-1" aria-hidden="true"></i>Veículos
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-6 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center pb-4">
                    <h4 class="card-title mb-0">Financeiro vencidos</h4>
                </div>
                <canvas id="billingOpenLateChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6 grid-margin stretch-card">
        <div class="card">

            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center pb-4">
                    <h4 class="card-title mb-0">Controle de locações</h4>
                </div>
                <canvas id="rentalsLateChart"></canvas>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-6 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center pb-4">
                    <h4 class="card-title mb-0">Clientes novos</h4>
                </div>
                <canvas id="newClientsChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center pb-4">
                    <h4 class="card-title mb-0">Locações realizadas</h4>
                </div>
                <canvas id="rentalsDoneChart"></canvas>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-6 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center pb-4">
                    <h4 class="card-title mb-0">Faturamento</h4>
                </div>
                <canvas id="billingChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-6 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <p>Clientes que mais locaram</p>
                <ul class="bullet-line-list pb-3" id="top_clients_rental"></ul>
            </div>
        </div>
    </div>
</div>
<input type="hidden" id="route_new_clients_for_month" value="{{ route('ajax.client.get-new-client-for-month', array('months' => 9)) }}">
<input type="hidden" id="route_rentals_for_month" value="{{ route('ajax.rental.get-rentals-for-month', array('months' => 9)) }}">
<input type="hidden" id="route_bills_for_month" value="{{ route('ajax.bills_to_receive.get-bills-for-month', array('months' => 9)) }}">
<input type="hidden" id="route_clients_top_rentals" value="{{ route('ajax.client.get-clients-top-rentals', array('count' => 8)) }}">
<input type="hidden" id="route_update_client" value="{{ route('client.edit') }}">
<input type="hidden" id="route_rentals_late_by_type" value="{{ route('ajax.rental.get-rentals-late-by-type') }}">
<input type="hidden" id="route_dashboard_get_billing_open_late" value="{{ route('ajax.dashboard.get-billing-open-late') }}">
