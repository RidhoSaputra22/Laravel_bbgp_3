<!-- Header start -->
<header id="header" class="header-one">
   <div class="bg-white">
      <div class="container">
         <div style="padding:10px" class="logo-area">
            <div class="row align-items-center">
               <div class="logo col-lg-3 text-center text-lg-left mb-3 mb-md-5 mb-lg-0">
                  <a class="d-block" href="/">
                     <img style="width:auto; height:80px" src="{{ asset('landing/images/header/icon.png') }}"
                        alt="BBGTK SulSel">
                  </a>
               </div><!-- logo end -->

               <div class="col-lg-9 ">
                  <ul class="top-info-box">
                     <li>
                        <div class="info-box">
                           <div class="info-box-content">
                              <p class="info-box-title">Hubungi Kami</p>
                              <p class="info-box-subtitle">(0411) 440065, 421460 </p>
                           </div>
                        </div>
                     </li>
                     <li class="last">
                        <div class="info-box last">
                           <div class="info-box-content">
                              <p class="info-box-title">Fax</p>
                              <p class="info-box-subtitle">0411-421460</p>
                           </div>
                        </div>
                     </li>
                     <li>
                        <div class="info-box">
                           <div class="info-box-content">
                              <p class="info-box-title">Email Kami</p>
                              <p class="info-box-subtitle">bbgtksulsel@kemdikbud.go.id </p>
                           </div>
                        </div>
                     </li>

                     <li class="header-get-a-quote">
                        <a class="btn btn-primary" href="{{ route('user.kontak') }}">Kontak</a>
                     </li>
                  </ul><!-- Ul end -->
               </div><!-- header right end -->
            </div><!-- logo area end -->

         </div><!-- Row end -->
      </div><!-- Container end -->
   </div>

   <div class="site-navigation">
      <div class="container">
         <div class="row">
            <div class="col-lg-12">
               <nav class="navbar navbar-expand-lg navbar-dark p-0">
                  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target=".navbar-collapse"
                     aria-controls="navbar-collapse" aria-expanded="false" aria-label="Toggle navigation">
                     <span class="navbar-toggler-icon"></span>
                  </button>

                  <div id="navbar-collapse" class="collapse navbar-collapse">
                     <ul class="nav navbar-nav mr-auto">
                        <li class="nav-item {{ $menu == 'profil' ? 'active' : '' }}"><a class="nav-link"
                              href="/">Profil</a></li>

                        {{-- <li class="nav-item {{ $menu == 'data' ? 'active' : '' }} dropdown">
                                    <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">Data <i
                                            class="fa fa-angle-down"></i></a>
                                    <ul class="dropdown-menu" role="menu">
                                        <li><a href="{{ route('user.pegawai') }}">Data Internal</a></li>
                                        <li><a href="{{ route('user.guru') }}">Data Eksternal</a></li>
                                    </ul>
                                </li> --}}

                        <li class="nav-item {{ $menu == 'data' ? 'active' : '' }}"><a class="nav-link"
                              href="{{ route('user.guru') }} ">Data Eksternal</a></li>

                        <li class="nav-item {{ $menu == 'kontak' ? 'active' : '' }}"><a class="nav-link"
                              href="{{ route('user.kontak') }} ">Kontak</a></li>

                        <li class="nav-item {{ $menu == 'kegiatan' ? 'active' : '' }}"><a class="nav-link"
                              href="{{ route('user.kegiatan') }}">Kegiatan</a></li>

                        <li class="nav-item {{ $menu == 'sekolah' ? 'active' : '' }}"><a class="nav-link"
                              href="{{ route('user.data-sekolah') }}">Input Data Sekolah</a></li>

                        <li class="nav-item {{ $menu == 'assessment-portal' ? 'active' : '' }}"><a class="nav-link"
                              href="{{ route('assessment.portal.index') }}">Assessment</a></li>

                        {{-- <li class="nav-item {{ $menu == 'statistik' ? 'active' : '' }}"><a class="nav-link" href="{{ route('user.statistik') }}">Statistik</a></li> --}}

                        <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Login</a></li>


                        {{-- <li class="nav-item dropdown">
                                  <a href="#" class="nav-link dropdown-toggle"
                                      data-toggle="dropdown">Projects <i class="fa fa-angle-down"></i></a>
                                  <ul class="dropdown-menu" role="menu">
                                      <li><a href="projects.html">Projects All</a></li>
                                      <li><a href="projects-single.html">Projects Single</a></li>
                                  </ul>
                              </li>

                              <li class="nav-item dropdown">
                                  <a href="#" class="nav-link dropdown-toggle"
                                      data-toggle="dropdown">Services <i class="fa fa-angle-down"></i></a>
                                  <ul class="dropdown-menu" role="menu">
                                      <li><a href="services.html">Services All</a></li>
                                      <li><a href="service-single.html">Services Single</a></li>
                                  </ul>
                              </li>

                              <li class="nav-item dropdown">
                                  <a href="#" class="nav-link dropdown-toggle"
                                      data-toggle="dropdown">Features <i class="fa fa-angle-down"></i></a>
                                  <ul class="dropdown-menu" role="menu">
                                      <li><a href="typography.html">Typography</a></li>
                                      <li><a href="404.html">404</a></li>
                                      <li class="dropdown-submenu">
                                          <a href="#!" class="dropdown-toggle"
                                              data-toggle="dropdown">Parent Menu</a>
                                          <ul class="dropdown-menu">
                                              <li><a href="#!">Child Menu 1</a></li>
                                              <li><a href="#!">Child Menu 2</a></li>
                                              <li><a href="#!">Child Menu 3</a></li>
                                          </ul>
                                      </li>
                                  </ul>
                              </li>

                              <li class="nav-item dropdown">
                                  <a href="#" class="nav-link dropdown-toggle"
                                      data-toggle="dropdown">News <i class="fa fa-angle-down"></i></a>
                                  <ul class="dropdown-menu" role="menu">
                                      <li><a href="news-left-sidebar.html">News Left Sidebar</a></li>
                                      <li><a href="news-right-sidebar.html">News Right Sidebar</a></li>
                                      <li><a href="news-single.html">News Single</a></li>
                                  </ul>
                              </li> --}}

                     </ul>
                  </div>
               </nav>
            </div>
            <!--/ Col end -->
         </div>
         <!--/ Row end -->

         <div class="nav-search">
            <span id="search"><i class="fa fa-search"></i></span>
         </div><!-- Search end -->

         <div class="search-block" style="display: none;">
            <label for="search-field" class="w-100 mb-0">
               <input type="text" class="form-control" id="search-field" placeholder="Type what you want and enter">
            </label>
            <span class="search-close">&times;</span>
         </div><!-- Site search end -->
      </div>
      <!--/ Container end -->

   </div>
   <!--/ Navigation end -->
</header>
<!--/ Header end -->
