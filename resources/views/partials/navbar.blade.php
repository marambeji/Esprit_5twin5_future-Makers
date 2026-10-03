<header class="header-area">
            <div class="container-fluid">
               <div class="row d_flex">
                  <div class=" col-md-2 col-sm-3">
                     <div class="logo">
                        <a class="{{ request()->routeIs('home') || request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('home') }}">Nutri<span>trace</span></a>
                     </div>
                  </div>
                  <div class="col-md-8 col-sm-9">
                     <div class="navbar-area">
                        <nav class="site-navbar">
                           <ul>
                              <li><a class="{{ request()->routeIs('home') || request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('home') }}">Home</a></li>
                              <li><a class="{{ request()->routeIs('about') ? 'active' : '' }}" href="{{ route('about') }}">About</a></li>
                              <li><a class="{{ request()->routeIs('service') ? 'active' : '' }}" href="{{ route('service') }}">Service</a></li>
                               <li><a class="{{ request()->routeIs('catalog.*') ? 'active' : '' }}" href="{{ route('catalog.index') }}">Catalogue</a></li>
                                <li><a class="{{ request()->routeIs('testimonials') ? 'active' : '' }}" href="{{ route('testimonials') }}">Testimonail</a></li>
                              <li><a class="{{ request()->routeIs('blog') ? 'active' : '' }}" href="{{ route('blog') }}">Blog</a></li>
                              <li><a class="{{ request()->routeIs('contact') ? 'active' : '' }}" href="{{ route('contact') }}">Contact</a></li>
                           </ul>
                           <button class="nav-toggler" type="button" aria-label="Ouvrir le menu" aria-expanded="false">
                           <span></span>
                           </button>
                        </nav>
                     </div>
                  </div>
                  <div class="col-md-2 padd_0 d_none">
                     <ul class="email text_align_right">
                        <li><a href="Javascript:void(0)">Login</a>
                        </li>
                        <li><a href="Javascript:void(0)"><i class="fa fa-search" aria-hidden="true"></i>
                           </a>
                        </li>
                     </ul>
                  </div>
               </div>
            </div>
         </header>
