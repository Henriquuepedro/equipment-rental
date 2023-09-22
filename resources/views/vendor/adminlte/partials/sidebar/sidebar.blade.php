@php
    $route = Route::current();

    $registerActive = '';
    $controlActive = '';
    $reportActive = '';
    $dashboardActive = '';
    $adminMaster = '';

    if ($route->getName() == 'dashboard') {
        $dashboardActive = 'active';
    } elseif (
        likeText('client.%', $route->getName())      !== false ||
        likeText('equipment.%', $route->getName())   !== false ||
        likeText('driver.%', $route->getName())      !== false ||
        likeText('vehicle.%', $route->getName())     !== false ||
        likeText('residue.%', $route->getName())     !== false ||
        likeText('provider.%', $route->getName())    !== false
    ) {
        $registerActive = 'active';
    } elseif (
        likeText('rental.%', $route->getName()) !== false ||
        likeText('budget.%', $route->getName()) !== false ||
        likeText('bills_to_receive.%', $route->getName()) !== false ||
        likeText('bills_to_pay.%', $route->getName()) !== false
    ) {
        $controlActive = 'active';
    } elseif (
        likeText('report.%', $route->getName())
    ) {
        $reportActive = 'active';
    } elseif (
        likeText('master.%', $route->getName())
    ) {
        $adminMaster = 'active';
    }
//    dd($route->getName(),strstr($route->getName(),'rental\.'), $reportActive);
@endphp

<!-- partial -->
    <!-- partial:partials/_sidebar.html -->
<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav">
        @can('admin-master')
            <li class="nav-item {{ $adminMaster }} bg-primary">
                <a class="nav-link" data-toggle="collapse" href="#report-dropdown" aria-expanded="false" aria-controls="report-dropdown">
                    <i class="menu-icon typcn typcn-cog-outline"></i>
                    <span class="menu-title">Administração</span>
                    <i class="menu-arrow"></i>
                </a>
                <div class="collapse" id="report-dropdown">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('master.company.index') }}">Empresas</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('master.user.index') }}">Usuários</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('master.plan.index') }}">Planos</a>
                        </li>
                    </ul>
                </div>
            </li>
        @endif
        <li class="nav-item {{ $dashboardActive }}">
            <a class="nav-link" href="{{ route('dashboard') }}">
                <i class="menu-icon typcn typcn-device-desktop"></i>
                <span class="menu-title">Dashboard</span>
            </a>
        </li>
        @if(
            in_array('ClientView', $permissions) ||
            in_array('EquipmentView', $permissions) ||
            in_array('DriverView', $permissions) ||
            in_array('VehicleView', $permissions) ||
            in_array('ResidueView', $permissions)
        )
        <li class="nav-item {{ $registerActive }}">
            <a class="nav-link" data-toggle="collapse" href="#register-dropdown" aria-expanded="false" aria-controls="register-dropdown">
                <i class="menu-icon typcn typcn-plus-outline"></i>
                <span class="menu-title">Cadastro</span>
                <i class="menu-arrow"></i>
            </a>
            <div class="collapse" id="register-dropdown">
                <ul class="nav flex-column sub-menu">
                    @if(in_array('ClientView', $permissions))
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('client.index') }}">Cliente</a>
                    </li>
                    @endif
                    @if(in_array('EquipmentView', $permissions))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('equipment.index') }}">Equipamento</a>
                        </li>
                    @endif
                    @if(in_array('DriverView', $permissions))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('driver.index') }}">Motorista</a>
                        </li>
                    @endif
                    @if(in_array('VehicleView', $permissions))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('vehicle.index') }}">Veículo</a>
                        </li>
                    @endif
                    @if(in_array('ResidueView', $permissions))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('residue.index') }}">Resíduo</a>
                        </li>
                    @endif
                    @if(in_array('ProviderView', $permissions))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('provider.index') }}">Fornecedor</a>
                        </li>
                    @endif
                </ul>
            </div>
        </li>
        @endif
        @if(
            in_array('RentalView', $permissions) ||
            in_array('BudgetView', $permissions)
        )
            <li class="nav-item {{ $controlActive }}">
                <a class="nav-link" data-toggle="collapse" href="#control-dropdown" aria-expanded="false" aria-controls="control-dropdown">
                    <i class="menu-icon typcn typcn-cog-outline"></i>
                    <span class="menu-title">Controle</span>
                    <i class="menu-arrow"></i>
                </a>
                <div class="collapse" id="control-dropdown">
                    <ul class="nav flex-column sub-menu">
                        @if(in_array('RentalView', $permissions))
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('rental.index') }}">Locação</a>
                            </li>
                        @endif
                        @if(in_array('BudgetView', $permissions))
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('budget.index') }}">Orçamento</a>
                            </li>
                        @endif
                        @if(in_array('BillsToReceiveView', $permissions))
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('bills_to_receive.index') }}">Contas a Receber</a>
                            </li>
                        @endif
                        @if(in_array('BillsToPayView', $permissions))
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('bills_to_pay.index') }}">Contas a Pagar</a>
                            </li>
                        @endif
                    </ul>
                </div>
            </li>
        @endif
        @if(
            in_array('ReportView', $permissions)
        )
            <li class="nav-item {{ $reportActive }}">
                <a class="nav-link" data-toggle="collapse" href="#report-dropdown" aria-expanded="false" aria-controls="report-dropdown">
                    <i class="menu-icon typcn typcn-cog-outline"></i>
                    <span class="menu-title">Relatório</span>
                    <i class="menu-arrow"></i>
                </a>
                <div class="collapse" id="report-dropdown">
                    <ul class="nav flex-column sub-menu">
                        @if(in_array('ReportView', $permissions))
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('report.rental') }}">Locação</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('report.bill') }}">Financeiro</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('report.register') }}">Cadastro</a>
                            </li>
                        @endif
                    </ul>
                </div>
            </li>
        @endif
    </ul>
</nav>
