@php
    $route = Route::current();

    $registerActive = '';
    $dashboardActive = '';

    if ($route->getName() == 'dashboard') $dashboardActive = 'active';
    elseif (
        strstr($route->getName(),'client') !== false ||
        strstr($route->getName(),'equipament') !== false ||
        strstr($route->getName(),'driver') !== false ||
        strstr($route->getName(),'vehicle') !== false
    ) $registerActive = 'active';
@endphp

<!-- partial -->
    <!-- partial:partials/_sidebar.html -->
<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav">
        <li class="nav-item {{ $dashboardActive }}">
            <a class="nav-link" href="{{ route('dashboard') }}">
                <i class="menu-icon typcn typcn-device-desktop"></i>
                <span class="menu-title">Dashboard</span>
            </a>
        </li>
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
                        <a class="nav-link" href=" {{ route('client.index') }}">Cliente</a>
                    </li>
                    @endif
                    @if(in_array('EquipamentView', $permissions))
                        <li class="nav-item">
                            <a class="nav-link" href=" {{ route('equipament.index') }}">Equipamento</a>
                        </li>
                    @endif
                    @if(in_array('DriverView', $permissions))
                        <li class="nav-item">
                            <a class="nav-link" href=" {{ route('driver.index') }}">Motorista</a>
                        </li>
                    @endif
                    @if(in_array('VehicleView', $permissions))
                        <li class="nav-item">
                            <a class="nav-link" href=" {{ route('vehicle.index') }}">Veículo</a>
                        </li>
                    @endif
                </ul>
            </div>
        </li>
    </ul>
</nav>
