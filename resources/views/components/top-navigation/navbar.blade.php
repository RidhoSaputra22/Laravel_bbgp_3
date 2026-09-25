<div class="navbar-bg"></div>
<nav class="navbar navbar-expand-lg main-navbar">
    <a href="/" class="navbar-brand sidebar-gone-hide">BBGTK Sulsel</a>
    <a href="#" class="nav-link sidebar-gone-show" data-toggle="sidebar"><i class="fas fa-bars"></i></a>
    <div class="nav-collapse">
        <ul class="navbar-nav">
        </ul>
    </div>
    <ul class="navbar-nav navbar-right">
    </ul>
</nav>

<nav class="navbar navbar-secondary navbar-expand-lg">
    <div class="container">
        <ul class="navbar-nav">
            <li class="nav-item {{ $menu == 'profil' ? 'active' : '' }}">
                <a href="{{ route('user.index') }}" class="nav-link"><i class="fas fa-home"></i><span>Profil</span></a>
            </li>

            <li class="nav-item dropdown {{ $menu == 'guru' || $menu == 'pegawai' ? 'active' : '' }}">
                <a href="#" data-toggle="dropdown" class="nav-link has-dropdown"><i class="fas fa-layer-group"></i><span>Data</span></a>
                <ul class="dropdown-menu">
                    <li class="nav-item {{ $menu == 'pegawai' ? 'active' : '' }}"><a href="{{  route('user.pegawai') }}" class="nav-link">Data Internal BBGTK</a></li>
                    <li class="nav-item {{ $menu == 'guru' ? 'active' : '' }}"><a href="{{  route('user.guru') }}" class="nav-link">Data Eksternal BBGTK</a></li>
                </ul>
            </li>

            <li class="nav-item {{ $menu == 'kontak' ? 'active' : '' }}">
                <a href="{{ route('user.kontak') }}" class="nav-link"><i class="fas fa-id-card-alt"></i><span>Kontak</span></a>
            </li>
            <li class="nav-item {{ $menu == 'kegiatan' ? 'active' : '' }}">
                <a href="{{ route('user.kegiatan') }}" class="nav-link"><i class="fas fa-calendar-week"></i><span>Kegiatan</span></a>
            </li>

            <li class="nav-item">
                <a href="{{ route('login') }}" class="nav-link"><i class="fas fa-user"></i><span>Login</span></a>
            </li>
            
        </ul>
    </div>
</nav>
