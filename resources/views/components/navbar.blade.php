<nav class="bg-gradient-to-r from-slate-900 via-purple-900 to-slate-900 shadow-2xl backdrop-blur-lg border-b border-white/10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-20">
            {{-- Logo Section --}}
            <div class="flex-shrink-0 flex items-center">
                <a href="{{ route('home') }}" class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-r from-purple-400 to-pink-400 rounded-lg blur opacity-30 group-hover:opacity-50 transition-opacity duration-300"></div>
                    <div class="relative bg-white/10 backdrop-blur-sm rounded-lg px-4 py-2 border border-white/20 group-hover:border-white/30 transition-all duration-300">
                        <h1 class="text-2xl font-bold bg-gradient-to-r from-purple-400 via-pink-400 to-cyan-400 bg-clip-text text-transparent">
                            MeksikoStore
                        </h1>
                    </div>
                </a>
            </div>

            {{-- Desktop Navigation --}}
            <div class="hidden md:block">
                <div class="ml-10 flex items-baseline space-x-8">
                    <a href="{{ route('home') }}" 
                       class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
                        <i class="fas fa-home mr-2"></i>Beranda
                    </a>
                    
                    <div class="relative group">
                        <button class="nav-link flex items-center">
                            <i class="fas fa-store mr-2"></i>Toko Kami
                            <i class="fas fa-chevron-down ml-1 text-xs transform group-hover:rotate-180 transition-transform duration-200"></i>
                        </button>
                        <div class="absolute top-full left-0 mt-2 w-56 bg-slate-800/95 backdrop-blur-lg rounded-xl shadow-2xl border border-white/10 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 transform translate-y-2 group-hover:translate-y-0 z-50">
                            <div class="py-3">
                                <a href="" 
                                   class="dropdown-link">
                                    <i class="fas fa-box-open mr-3 text-purple-400"></i>Semua Produk
                                </a>
                                <a href="" 
                                   class="dropdown-link">
                                    <i class="fas fa-tags mr-3 text-blue-400"></i>Kategori
                                </a>
                                <a href="" 
                                   class="dropdown-link">
                                    <i class="fas fa-star mr-3 text-yellow-400"></i>Produk Unggulan
                                </a>
                                <a href="" 
                                   class="dropdown-link">
                                    <i class="fas fa-sparkles mr-3 text-green-400"></i>Produk Terbaru
                                </a>
                            </div>
                        </div>
                    </div>

                    <a href="https://heyzine.com/flip-book/20c342c823.html" 
                       target="_blank" 
                       class="nav-link">
                        <i class="fas fa-book-open mr-2"></i>Katalog Digital
                    </a>

                    <div class="relative group">
                        <button class="nav-link flex items-center">
                            <i class="fas fa-link mr-2"></i>Layanan
                            <i class="fas fa-chevron-down ml-1 text-xs transform group-hover:rotate-180 transition-transform duration-200"></i>
                        </button>
                        <div class="absolute top-full left-0 mt-2 w-56 bg-slate-800/95 backdrop-blur-lg rounded-xl shadow-2xl border border-white/10 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 transform translate-y-2 group-hover:translate-y-0 z-50">
                            <div class="py-3">
                                <a href="https://linktr.ee/meksikostore" 
                                   target="_blank" 
                                   class="dropdown-link">
                                    <i class="fas fa-external-link-alt mr-3 text-cyan-400"></i>Semua Link
                                </a>
                                <a href="https://www.meksikogadai.com/" 
                                   target="_blank" 
                                   class="dropdown-link">
                                    <i class="fas fa-coins mr-3 text-orange-400"></i>Meksiko Gadai
                                </a>
                                <a href="" 
                                   class="dropdown-link">
                                    <i class="fas fa-cogs mr-3 text-indigo-400"></i>Layanan Lainnya
                                </a>
                            </div>
                        </div>
                    </div>

                    <a href="" 
                       class="nav-link {{ request()->routeIs('about') ? 'active' : '' }}">
                        <i class="fas fa-info-circle mr-2"></i>Tentang
                    </a>

                    <a href="" 
                       class="nav-link {{ request()->routeIs('contact') ? 'active' : '' }}">
                        <i class="fas fa-envelope mr-2"></i>Kontak
                    </a>
                </div>
            </div>

            {{-- CTA Button --}}
            <div class="hidden md:block">
                <a href="" 
                   class="relative group bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600 text-white px-6 py-3 rounded-full font-semibold transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-purple-500/25">
                    <span class="relative z-10 flex items-center">
                        <i class="fas fa-paper-plane mr-2"></i>Hubungi Kami
                    </span>
                    <div class="absolute inset-0 bg-gradient-to-r from-purple-600 to-pink-600 rounded-full blur opacity-0 group-hover:opacity-30 transition-opacity duration-300"></div>
                </a>
            </div>

            {{-- Mobile Menu Button --}}
            <div class="md:hidden">
                <button id="mobile-menu-button" 
                        class="mobile-menu-button bg-white/10 backdrop-blur-sm rounded-lg p-2 border border-white/20 hover:border-white/30 transition-all duration-300">
                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile Menu --}}
    <div id="mobile-menu" class="hidden md:hidden bg-slate-800/95 backdrop-blur-lg border-t border-white/10">
        <div class="px-4 pt-4 pb-6 space-y-2">
            <a href="{{ route('home') }}" 
               class="mobile-nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
                <i class="fas fa-home mr-3"></i>Beranda
            </a>
            
            <div class="mobile-dropdown">
                <button class="mobile-dropdown-button">
                    <i class="fas fa-store mr-3"></i>Toko Kami
                    <i class="fas fa-chevron-down ml-auto text-xs dropdown-arrow"></i>
                </button>
                <div class="mobile-dropdown-content">
                    <a href="" class="mobile-dropdown-link">
                        <i class="fas fa-box-open mr-3 text-purple-400"></i>Semua Produk
                    </a>
                    <a href="" class="mobile-dropdown-link">
                        <i class="fas fa-tags mr-3 text-blue-400"></i>Kategori
                    </a>
                    <a href="" class="mobile-dropdown-link">
                        <i class="fas fa-star mr-3 text-yellow-400"></i>Produk Unggulan
                    </a>
                    <a href="" class="mobile-dropdown-link">
                        <i class="fas fa-sparkles mr-3 text-green-400"></i>Produk Terbaru
                    </a>
                </div>
            </div>

            <a href="https://heyzine.com/flip-book/20c342c823.html" 
               target="_blank" 
               class="mobile-nav-link">
                <i class="fas fa-book-open mr-3"></i>Katalog Digital
            </a>

            <div class="mobile-dropdown">
                <button class="mobile-dropdown-button">
                    <i class="fas fa-link mr-3"></i>Layanan
                    <i class="fas fa-chevron-down ml-auto text-xs dropdown-arrow"></i>
                </button>
                <div class="mobile-dropdown-content">
                    <a href="https://linktr.ee/meksikostore" 
                       target="_blank" 
                       class="mobile-dropdown-link">
                        <i class="fas fa-external-link-alt mr-3 text-cyan-400"></i>Semua Link
                    </a>
                    <a href="https://www.meksikogadai.com/" 
                       target="_blank" 
                       class="mobile-dropdown-link">
                        <i class="fas fa-coins mr-3 text-orange-400"></i>Meksiko Gadai
                    </a>
                    <a href="" 
                       class="mobile-dropdown-link">
                        <i class="fas fa-cogs mr-3 text-indigo-400"></i>Layanan Lainnya
                    </a>
                </div>
            </div>

            <a href="" 
               class="mobile-nav-link {{ request()->routeIs('about') ? 'active' : '' }}">
                <i class="fas fa-info-circle mr-3"></i>Tentang
            </a>

            <a href="" 
               class="mobile-nav-link {{ request()->routeIs('contact') ? 'active' : '' }}">
                <i class="fas fa-envelope mr-3"></i>Kontak
            </a>

            <div class="pt-4 border-t border-white/10 mt-4">
                <a href="" 
                   class="w-full bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600 text-white px-4 py-3 rounded-lg font-semibold transition-all duration-300 flex items-center justify-center">
                    <i class="fas fa-paper-plane mr-2"></i>Hubungi Kami
                </a>
            </div>
        </div>
    </div>
</nav>