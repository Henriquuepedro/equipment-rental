<nav class="navbar default-layout col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
    <div class="text-center navbar-brand-wrapper d-flex align-items-top justify-content-center">
        <a class="navbar-brand brand-logo" href="index.html">
            <img src="{{ $settings['img_company'] }}" alt="logo" />
        </a>
        <a class="navbar-brand brand-logo-mini" href="index.html">
            <img src="{{ asset('assets/images/logo-mini.svg') }}" alt="logo" />
        </a>
    </div>
    <div class="navbar-menu-wrapper d-flex align-items-center">
        <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
            <span class="mdi mdi-menu"></span>
        </button>
        <div class="notices d-flex justify-content-center w-100">
            {!! $settings['notices'] !!}
        </div>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item dropdown" data-intro="Visualize e realize atendientos ao time de suporte" data-step="1" data-position="left">
                <a class="nav-link count-indicator" id="messageDropdown" href="{{ route('support.index') }}" data-toggle="tooltip" title="Atendimentos">
                    <i class="mdi mdi-headset"></i>
                </a>
            </li>
            <li class="nav-item dropdown d-none d-xl-inline-block user-dropdown" data-intro="Visualize seu perfil, configurações e encerre a sessão." data-step="2" data-position="left">
                <a class="nav-link dropdown-toggle" id="UserDropdown" href="#" data-toggle="dropdown" aria-expanded="false">
                    <img class="img-xs rounded-circle" src="{{ $settings['img_profile'] }}" alt="Profile image">
                </a>
                <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="UserDropdown">
                    <div class="dropdown-header text-center">
                        <img class="img-md rounded-circle" src="{{ $settings['img_profile'] }}" alt="Profile image">
                        <p class="mb-1 mt-2 font-weight-semibold">Código Cliente: {{ $settings['company_id'] }}</p>
                        <p class="mb-1 mt-2 font-weight-semibold">{{ auth()->user()->__get('name') }}</p>
                        <p class="font-weight-light text-muted mb-0">{{ auth()->user()->__get('email') }}</p>
                    </div>
                    <a class="dropdown-item d-flex justify-content-between" href="{{ route('profile.index') }}"><span><i class="dropdown-item-icon mdi mdi-account-outline text-primary"></i> Meu Perfil</span></a>
{{--                    <a class="dropdown-item"><i class="dropdown-item-icon mdi mdi-message-text-outline text-primary"></i> Messages</a>--}}
{{--                    <a class="dropdown-item"><i class="dropdown-item-icon mdi mdi-calendar-check-outline text-primary"></i> Activity</a>--}}
                    @if ($settings['type_user'] == 2 || $settings['type_user'] == 1)<a class="dropdown-item" href="{{ route('config.index') }}"><i class="dropdown-item-icon mdi mdi-cogs text-primary"></i> Configuração</a>@endif
                    <a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i class="dropdown-item-icon mdi mdi-power text-primary"></i>Encerrar Sessão</a>
                </div>
            </li>
        </ul>
        <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="offcanvas">
            <span class="mdi mdi-menu"></span>
        </button>
    </div>
</nav>
