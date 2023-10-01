@php
    $route_name = Route::current()->getName();
    $route_exp = explode('.', $route_name);

    $routes = array(
        [
            'type'          => 'level',
            'route'         => null,
            'permissions'   => [],
            'text'          => 'Administração',
            'can'           => 'admin-master',
            'class'         => 'bg-primary',
            'list'          => [
                [
                    'route' => 'master.company.index',
                    'text' => 'Empresas',
                    'permissions'   => [],
                ],
                [
                    'route' => 'master.user.index',
                    'text' => 'Usuários',
                    'permissions'   => [],
                ],
                [
                    'route' => 'master.plan.index',
                    'text' => 'Planos',
                    'permissions'   => [],
                ]
            ]
        ],
        [
            'type'          => 'single',
            'route'         => 'dashboard',
            'text'          => 'Dashboard',
            'permissions'   => [],
        ],
        [
            'type'          => 'level',
            'route'         => null,
            'permissions'   => [],
            'text'          => 'Cadastro',
            'can'           => null,
            'list'          => [
                [
                    'permissions' => [
                        'ClientView'
                    ],
                    'route' => 'client.index',
                    'text' => 'Cliente'
                ],
                [
                    'permissions' => [
                        'EquipmentView'
                    ],
                    'route' => 'equipment.index',
                    'text' => 'Equipamento'
                ],
                [
                    'permissions' => [
                        'DriverView'
                    ],
                    'route' => 'driver.index',
                    'text' => 'Motorista'
                ],
                [
                    'permissions' => [
                        'VehicleView'
                    ],
                    'route' => 'vehicle.index',
                    'text' => 'Veículo'
                ],
                [
                    'permissions' => [
                        'ResidueView'
                    ],
                    'route' => 'residue.index',
                    'text' => 'Resíduo'
                ],
                [
                    'permissions' => [
                        'ProviderView'
                    ],
                    'route' => 'provider.index',
                    'text' => 'Fornecedor'
                ]
            ]
        ],
        [
            'type'          => 'level',
            'route'         => null,
            'permissions'   => [],
            'text'          => 'Controle',
            'can'           => null,
            'list'          => [
                [
                    'permissions' => [
                        'RentalView'
                    ],
                    'route' => 'rental.index',
                    'text' => 'Locação',
                ],
                [
                    'permissions' => [
                        'BudgetView'
                    ],
                    'route' => 'budget.index',
                    'text' => 'Orçamento',
                ],
                [
                    'permissions' => [
                        'BillsToReceiveView'
                    ],
                    'route' => 'bills_to_receive.index',
                    'text' => 'Contas a Receber',
                ],
                [
                    'permissions' => [
                        'BillsToPayView'
                    ],
                    'route' => 'bills_to_pay.index',
                    'text' => 'Contas a Pagar',
                ],
            ]
        ],
        [
            'type'          => 'level',
            'route'         => null,
            'permissions'   => ['ReportView'],
            'text'          => 'Relatório',
            'can'           => null,
            'list'          => [
                [
                    'route' => 'report.rental',
                    'text' => 'Locação'
                ],
                [
                    'route' => 'report.bill',
                    'text' => 'Financeiro'
                ],
                [
                    'route' => 'report.register',
                    'text' => 'Cadastro'
                ]
            ]
        ],
        [
            'type'          => 'level',
            'route'         => null,
            'permissions'   => ['PlanView'],
            'text'          => 'Plano',
            'can'           => null,
            'list'          => [
                [
                    'route' => 'plan.index',
                    'text' => 'Planos'
                ],
                [
                    'route' => 'plan.request',
                    'text' => 'Solicitações'
                ]
            ]
        ]
    );
@endphp

<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav">
    @foreach($routes as $key => $route)
        @php
            $route_permissions = $route['permissions'] ?? array_map(function($r) { return $r['permissions']; }, $route['list'] ?? array());
            $active = $route['type'] === 'single' ? $route_exp[0] == $route['route'] : in_array($route_exp[0], array_map(function ($r) { return explode('.', $r['route'])[0]; }, $route['list']))
        @endphp
        @if(empty($route_permissions) || count(array_diff($route_permissions, $permissions)) == 0)
            @if (empty($route['can']) || Auth::user()->can($route['can']))
                <li class="nav-item {{$route['class'] ?? ''}} {{ $active ? 'active' : '' }}">
                    <a class="nav-link" @if($route['type'] === 'single') href="{{ route($route['route']) }}" @else data-toggle="collapse" href="#level{{$key}}-dropdown" aria-expanded="false" aria-controls="level{{$key}}-dropdown" @endif>
                        <i class="menu-icon typcn typcn-device-desktop"></i>
                        <span class="menu-title">{{ $route['text'] }}</span>
                        @if($route['type'] === 'level') <i class="menu-arrow"></i> @endif
                    </a>
                    @if($route['type'] === 'level')
                        <div class="collapse" id="level{{$key}}-dropdown">
                            <ul class="nav flex-column sub-menu">
                                @php
                                    $route_exist = count(array_filter($route['list'], function($list) use ($route_name) { return $route_name == $list['route']; })) !== 0;
                                @endphp
                                @foreach($route['list'] as $list)
                                    @if(empty($list['permissions']) || count(array_diff($list['permissions'], $permissions)) == 0)
                                        @php

                                            $active_level = ($route_exist && $route_name == $list['route']) || (!$route_exist && $route_exp[0] == explode('.', $list['route'])[0]);
                                        @endphp
                                        <li class="nav-item">
                                            <a class="nav-link {{$active_level ? 'active' : ''}}" href="{{ route($list['route']) }}">{{ $list['text'] }}</a>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </li>
            @endif
        @endif
    @endforeach
    </ul>
</nav>
