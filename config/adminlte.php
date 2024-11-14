<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Title
    |--------------------------------------------------------------------------
    |
    | Here you can change the default title of your admin panel.
    |
    | For more detailed instructions you can look here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/#61-title
    |
    */

    'title' => env('APP_NAME', 'Laravel'),
    'title_prefix' => '',
    'title_postfix' => '',

    /*
    |--------------------------------------------------------------------------
    | Favicon
    |--------------------------------------------------------------------------
    |
    | Here you can activate the favicon.
    |
    | For more detailed instructions you can look here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/#62-favicon
    |
    */

    'use_ico_only' => false,
    'use_full_favicon' => false,

    /*
    |--------------------------------------------------------------------------
    | Logo
    |--------------------------------------------------------------------------
    |
    | Here you can change the logo of your admin panel.
    |
    | For more detailed instructions you can look here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/#63-logo
    |
    */

    'logo' => '<b>Admin</b>LTE',
    'logo_img' => 'vendor/adminlte/dist/img/AdminLTELogo.png',
    'logo_img_class' => 'brand-image img-circle',
    'logo_img_xl' => null,
    'logo_img_xl_class' => 'brand-image-xs',
    'logo_img_alt' => 'AdminLTE',

    /*
    |--------------------------------------------------------------------------
    | User Menu
    |--------------------------------------------------------------------------
    |
    | Here you can activate and change the user menu.
    |
    | For more detailed instructions you can look here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/#64-user-menu
    |
    */

    'usermenu_enabled' => true,
    'usermenu_header' => false,
    'usermenu_header_class' => 'bg-primary',
    'usermenu_image' => false,
    'usermenu_desc' => false,
    'usermenu_profile_url' => false,

    /*
    |--------------------------------------------------------------------------
    | Layout
    |--------------------------------------------------------------------------
    |
    | Here we change the layout of your admin panel.
    |
    | For more detailed instructions you can look here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/#71-layout
    |
    */

    'layout_topnav' => null,
    'layout_boxed' => null,
    'layout_fixed_sidebar' => null,
    'layout_fixed_navbar' => null,
    'layout_fixed_footer' => null,

    /*
    |--------------------------------------------------------------------------
    | Authentication Views Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the authentication views.
    |
    | For more detailed instructions you can look here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/#721-authentication-views-classes
    |
    */

    'classes_auth_card' => 'card-outline card-primary',
    'classes_auth_header' => '',
    'classes_auth_body' => '',
    'classes_auth_footer' => '',
    'classes_auth_icon' => '',
    'classes_auth_btn' => 'btn-flat btn-primary',

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the admin panel.
    |
    | For more detailed instructions you can look here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/#722-admin-panel-classes
    |
    */

    'classes_body' => 'm-page--fluid m--skin- m-content--skin-light2 m-header--fixed m-header--fixed-mobile m-aside-left--enabled m-aside-left--skin-dark m-aside-left--offcanvas m-footer--push m-aside--offcanvas-default',
    'classes_brand' => '',
    'classes_brand_text' => '',
    'classes_content_header' => 'container-fluid',
    'classes_content' => 'container-fluid',
    'classes_sidebar' => 'sidebar-dark-primary elevation-4',
    'classes_sidebar_nav' => 'nav-flat nav-legacy',
    'classes_topnav' => 'navbar-white navbar-light',
    'classes_topnav_nav' => 'navbar-expand-md',
    'classes_topnav_container' => 'container',

    /*
    |--------------------------------------------------------------------------
    | Sidebar
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar of the admin panel.
    |
    | For more detailed instructions you can look here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/#73-sidebar
    |
    */

    'sidebar_mini' => true,
    'sidebar_collapse' => false,
    'sidebar_collapse_auto_size' => false,
    'sidebar_collapse_remember' => false,
    'sidebar_collapse_remember_no_transition' => true,
    'sidebar_scrollbar_theme' => 'os-theme-light',
    'sidebar_scrollbar_auto_hide' => 'l',
    'sidebar_nav_accordion' => true,
    'sidebar_nav_animation_speed' => 300,

    /*
    |--------------------------------------------------------------------------
    | Control Sidebar (Right Sidebar)
    |--------------------------------------------------------------------------
    |
    | Here we can modify the right sidebar aka control sidebar of the admin panel.
    |
    | For more detailed instructions you can look here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/#74-control-sidebar-right-sidebar
    |
    */

    'right_sidebar' => false,
    'right_sidebar_icon' => 'fas fa-cogs',
    'right_sidebar_theme' => 'dark',
    'right_sidebar_slide' => true,
    'right_sidebar_push' => true,
    'right_sidebar_scrollbar_theme' => 'os-theme-light',
    'right_sidebar_scrollbar_auto_hide' => 'l',

    /*
    |--------------------------------------------------------------------------
    | URLs
    |--------------------------------------------------------------------------
    |
    | Here we can modify the url settings of the admin panel.
    |
    | For more detailed instructions you can look here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/#65-urls
    |
    */

    'use_route_url' => false,

    'dashboard_url' => 'dashboard',

    'logout_url' => 'logout',

    'login_url' => 'login',

    'register_url' => 'register',

    'password_reset_url' => 'password/reset',

    'password_email_url' => 'password/email',

    'profile_url' => false,

    /*
    |--------------------------------------------------------------------------
    | Laravel Mix
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Laravel Mix option for the admin panel.
    |
    | For more detailed instructions you can look here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/#92-laravel-mix
    |
    */

    'enabled_laravel_mix' => false,
    'laravel_mix_css_path' => 'css/app.css',
    'laravel_mix_js_path' => 'js/app.js',

    /*
    |--------------------------------------------------------------------------
    | Menu Items
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar/top navigation of the admin panel.
    |
    */

    'menu' => [
        [
            'type'          => 'level',
            'route'         => null,
            'permissions'   => [],
            'text'          => 'Administração',
            'can'           => 'admin-master',
            'icon'          => 'menu-icon mdi mdi-security',
            'class'         => '',
            'list'          => [
                [
                    'route' => 'master.company.index',
                    'text' => 'Empresas',
                    'permissions'   => [],
                    'route_active'  => ['master.company.create', 'master.company.edit']
                ],
                [
                    'route' => 'master.user.index',
                    'text' => 'Usuários',
                    'permissions'   => [],
                    'route_active'  => ['master.user.create', 'master.user.edit']
                ],
                [
                    'route' => 'master.plan.index',
                    'text' => 'Planos',
                    'permissions'   => [],
                    'route_active'  => ['master.plan.create', 'master.plan.edit']
                ],
                [
                    'route' => 'master.guide.index',
                    'text' => 'Manuais',
                    'permissions'   => [],
                    'route_active'  => ['master.guide.create', 'master.guide.edit']
                ],
                [
                    'route' => 'master.notification.index',
                    'text' => 'Notificação',
                    'permissions'   => [],
                    'route_active'  => ['master.notification.create', 'master.notification.edit']
                ],
                [
                    'route' => 'master.audit_log.index',
                    'text' => 'Logs de Auditoria',
                    'permissions'   => [],
                    'route_active'  => ['master.audit_log.view']
                ],
                [
                    'route' => 'master.log_file',
                    'text' => 'Logs File',
                    'permissions'   => [],
                    'route_active'  => []
                ]
            ]
        ],
        [
            'type'          => 'single',
            'route'         => 'dashboard',
            'text'          => 'Dashboard',
            'permissions'   => [],
            'icon'          => 'menu-icon mdi mdi-grid-large'
        ],
        [
            'type'          => 'level',
            'route'         => null,
            'permissions'   => [],
            'text'          => 'Cadastro',
            'can'           => null,
            'icon'          => 'menu-icon mdi mdi-plus-box-multiple',
            'list'          => [
                [
                    'permissions' => [
                        'ClientView'
                    ],
                    'route' => 'client.index',
                    'text' => 'Cliente',
                    'route_active'  => ['client.create', 'client.edit']
                ],
                [
                    'permissions' => [
                        'EquipmentView'
                    ],
                    'route' => 'equipment.index',
                    'text' => 'Equipamento',
                    'route_active'  => ['equipment.create', 'equipment.edit']
                ],
                [
                    'permissions' => [
                        'DriverView'
                    ],
                    'route' => 'driver.index',
                    'text' => 'Motorista',
                    'route_active'  => ['driver.create', 'driver.edit']
                ],
                [
                    'permissions' => [
                        'VehicleView'
                    ],
                    'route' => 'vehicle.index',
                    'text' => 'Veículo',
                    'route_active'  => ['vehicle.create', 'vehicle.edit']
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
                    'text' => 'Fornecedor',
                    'route_active'  => ['provider.create', 'provider.edit']
                ],
                [
                    'permissions' => [
                        'DisposalPlaceView'
                    ],
                    'route' => 'disposal_place.index',
                    'text' => 'Local de descarte',
                    'route_active'  => ['disposal_place.create', 'disposal_place.edit']
                ]
            ]
        ],
        [
            'type'          => 'level',
            'route'         => null,
            'permissions'   => [],
            'text'          => 'Controle',
            'can'           => null,
            'icon'          => 'menu-icon mdi mdi-cog-outline',
            'list'          => [
                [
                    'permissions' => [
                        'RentalView'
                    ],
                    'route' => 'rental.index',
                    'text' => 'Locação',
                    'route_active'  => ['rental.create', 'rental.edit', 'rental.exchange']
                ],
                [
                    'permissions' => [
                        'BudgetView'
                    ],
                    'route' => 'budget.index',
                    'text' => 'Orçamento',
                    'route_active'  => ['budget.create', 'budget.edit']
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
                    'route_active'  => ['bills_to_pay.create', 'bills_to_pay.edit']
                ],
                [
                    'permissions' => [
                        'BillsToPayView',
                        'BillsToReceiveView'
                    ],
                    'route' => 'cash_flow.index',
                    'text' => 'Fluxo de Caixa',
                    'route_active'  => []
                ],
            ]
        ],
        [
            'type'          => 'level',
            'route'         => null,
            'permissions'   => ['ReportView'],
            'text'          => 'Relatório',
            'can'           => null,
            'icon'          => 'menu-icon mdi mdi-file-chart',
            'list'          => [
                [
                    'route' => 'report.rental',
                    'text'  => 'Locação'
                ],
                [
                    'route' => 'report.bill',
                    'text'  => 'Financeiro'
                ],
                [
                    'route' => 'report.register',
                    'text'  => 'Cadastro'
                ]
            ]
        ],
        [
            'type'          => 'level',
            'route'         => null,
            'permissions'   => ['PlanView'],
            'text'          => 'Plano',
            'can'           => null,
            'icon'          => 'menu-icon mdi mdi-calendar-clock',
            'list'          => [
                [
                    'route'         => 'plan.index',
                    'text'          => 'Planos',
                    'route_active'  => ['plan.confirm']
                ],
                [
                    'route'         => 'plan.request',
                    'text'          => 'Solicitações',
                    'route_active'  => ['plan.view']
                ]
            ]
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Menu Filters
    |--------------------------------------------------------------------------
    |
    | Here we can modify the menu filters of the admin panel.
    |
    | For more detailed instructions you can look here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/#83-custom-menu-filters
    |
    */

    'filters' => [
        JeroenNoten\LaravelAdminLte\Menu\Filters\GateFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\HrefFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\SearchFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ActiveFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ClassesFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\LangFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\DataFilter::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Plugins Initialization
    |--------------------------------------------------------------------------
    |
    | Here we can modify the plugins used inside the admin panel.
    |
    | For more detailed instructions you can look here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/#91-plugins
    |
    */

    'plugins' => [
        'Datatables' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css',
                ],
            ],
        ],
        'Select2' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.css',
                ],
            ],
        ],
        'Chartjs' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.0/Chart.bundle.min.js',
                ],
            ],
        ],
        'Sweetalert2' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.jsdelivr.net/npm/sweetalert2@8',
                ],
            ],
        ],
        'Pace' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/themes/blue/pace-theme-center-radar.min.css',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/pace.min.js',
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Livewire
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Livewire support.
    |
    | For more detailed instructions you can look here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/#93-livewire
    */

    'livewire' => false,
];
