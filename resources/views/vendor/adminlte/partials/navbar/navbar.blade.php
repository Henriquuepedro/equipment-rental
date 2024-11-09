<nav class="navbar default-layout col-lg-12 col-12 p-0 fixed-top d-flex align-items-top flex-row">
    <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-start">
        <div class="me-3">
            <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-bs-toggle="minimize">
                <span class="icon-menu"></span>
            </button>
        </div>
        <div>
            <a class="navbar-brand brand-logo" href="{{ route('dashboard') }}">
                <img src="{{ $settings['img_company'] }}" alt="logo" />
            </a>
            <a class="navbar-brand brand-logo-mini" href="{{ route('dashboard') }}">
                <img src="{{ $settings['img_company'] }}" alt="logo" />
            </a>
        </div>
    </div>
    <div class="navbar-menu-wrapper d-flex align-items-top">
        @if(!empty($settings['notices']))
        <ul class="navbar-nav">
            <li class="nav-item fw-semibold d-none d-lg-block ms-0">
                <h3 class="welcome-sub-text">{!! $settings['notices'] !!}</h3>
            </li>
        </ul>
        @endif
        <ul class="navbar-nav ms-auto">
            <li class="nav-item dropdown">
                <a class="nav-link count-indicator" id="notificationDropdown" href="#" data-bs-toggle="dropdown">
                    <i class="icon-bell"></i>
                    @if ($settings['notifications_count'] != 0)
                        <span class="count">{{ $settings['notifications_count'] > 9 ? '9+' : $settings['notifications_count'] }}</span>
                    @endif
                </a>
                <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list pb-0"
                     aria-labelledby="notificationDropdown">
                    <a class="dropdown-item py-3 border-bottom" href="{{ route('notification.index') }}">
                        <p class="mb-0 fw-medium float-start">
                            {{
                                $settings['notifications_count'] == 0 ? 'Você não tem notificações não lidas' :
                                (
                                    $settings['notifications_count'] == 1 ? 'Você tem 1 notificação não lida' :
                                    "Você tem {$settings['notifications_count']} notificações não lidas"
                                )
                            }}

                        </p>
                        <span class="badge badge-pill badge-primary float-end">Ver todas</span>
                    </a>
                    @foreach($settings['notifications'] as $notification)
                        <a class="dropdown-item preview-item py-3" href="{{ route('notification.view', ['id' => $notification->id]) }}">
                            <div class="preview-thumbnail">
                                <i class="{{ $notification->title_icon }} text-primary"></i>
                            </div>
                            <div class="preview-item-content">
                                <h6 class="preview-subject fw-normal text-light mb-1">{{ $notification->title }}</h6>
                                <p class="fw-light small-text mb-0"> {{ dateInternationalToDateBrazil($notification->created_at, DATETIME_BRAZIL_NO_SECONDS) }} </p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link count-indicator" href="{{ route('support.index') }}">
                    <i class="icon-earphones-alt"></i>
                </a>
            </li>
            <li class="nav-item dropdown d-none d-lg-block user-dropdown">
                <a class="nav-link" id="UserDropdown" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                    <img class="img-xs rounded-circle" src="{{ $settings['img_profile'] }}" alt="Profile image">
                </a>
                <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="UserDropdown">
                    <div class="dropdown-header text-center">
                        <img class="img-md rounded-circle profile-user" src="{{ $settings['img_profile'] }}" alt="Profile image">
                        <p class="mb-1 mt-3 fw-semibold">Código Cliente: {{ $settings['company_id'] }}</p>
                        <p class="mb-1 mt-3 fw-semibold">{{ auth()->user()->__get('name') }}</p>
                        <p class="fw-light text-muted mb-0">{{ auth()->user()->__get('email') }}</p>
                    </div>

                    <a class="dropdown-item d-flex justify-content-between" href="{{ route('profile.index') }}"><span><i class="dropdown-item-icon mdi mdi-account-outline text-primary me-2"></i> Meu Perfil</span></a>
                    @if ($settings['type_user'] == 2 || $settings['type_user'] == 1)<a class="dropdown-item" href="{{ route('config.index') }}"><i class="dropdown-item-icon mdi mdi-cogs text-primary me-2"></i> Configuração</a>@endif
                    <a class="dropdown-item d-flex justify-content-between" href="{{ route('guide.index') }}"><span><i class="dropdown-item-icon mdi mdi-book-open-variant text-primary me-2"></i> Manuais</span></a>
                    <a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i class="dropdown-item-icon mdi mdi-power text-primary me-2"></i>Encerrar Sessão</a>
                </div>
            </li>
        </ul>
        <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button"
                data-bs-toggle="offcanvas">
            <span class="mdi mdi-menu"></span>
        </button>
    </div>
</nav>
