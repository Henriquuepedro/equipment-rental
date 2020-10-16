@php
  $route = Route::current();
@endphp

<!-- partial -->
    <!-- partial:partials/_sidebar.html -->
<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav">
{{--        <li class="nav-item nav-profile">--}}
{{--            <a href="#" class="nav-link" onclick="return false;">--}}
{{--                <div class="profile-image">--}}
{{--                    <img class="img-xs rounded-circle" src="assets/images/faces/face8.jpg" alt="profile image">--}}
{{--                    <div class="dot-indicator bg-success"></div>--}}
{{--                </div>--}}
{{--                <div class="text-wrapper">--}}
{{--                    <p class="profile-name">{{ auth()->user()->name }}</p>--}}
{{--                </div>--}}
{{--            </a>--}}
{{--        </li>--}}

        @foreach(config('adminlte.menu') as $key => $menu)
            @if(isset($menu['submenu']))
                @php
                    $menuActive = false;
                      foreach($menu['submenu'] as $checkMenuActive){
                          if (in_array($route->getName(), $checkMenuActive['active']))
                              $menuActive = true;
                      }
                @endphp
                <li class="nav-item {{ $menuActive ? 'active' : '' }}">
                    <a class="nav-link" data-toggle="collapse" href="#item_{{ $key }}" aria-expanded="{{ $menuActive ? 'true' : 'false' }}" aria-controls="item_{{ $key }}">
                        <i class="menu-icon typcn typcn-coffee"></i>
                        <span class="menu-title">{{ $menu['text'] }}</span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="collapse {{ $menuActive ? 'show' : '' }}" id="item_{{ $key }}">
                        <ul class="nav flex-column sub-menu">
                            @foreach($menu['submenu'] as $subMenu)

                                <li class="nav-item {{ in_array($route->getName(), $subMenu['active']) ? 'active' : '' }}">
                                    <a class="nav-link {{ in_array($route->getName(), $subMenu['active']) ? 'active' : '' }}" href="{{ route($subMenu['route']) }}">{{ $subMenu['text'] }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </li>
            @else
                <li class="nav-item {{ $route->getName() == $menu['route'] ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route($menu['route']) }}">
                        <i class="menu-icon typcn {{ $menu['icon'] }}"></i>
                        <span class="menu-title">{{ $menu['text'] }}</span>
                    </a>
                </li>
            @endif
        @endforeach
    </ul>
</nav>
