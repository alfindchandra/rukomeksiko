<!DOCTYPE html>
<html lang="en" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val))" :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meksiko Store</title>
    @vite('resources/css/app.css')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 transition-colors duration-300">

    <nav class="bg-white dark:bg-gray-800 shadow-lg border-b border-gray-100 dark:border-gray-700 sticky top-0 z-50" x-data="{ isMenuOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex-shrink-0 flex items-center">
                    <div class="flex items-center space-x-2">
                        <div class="w-10 h-10 bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg flex items-center justify-center">
                            {{-- Placeholder for logo if needed, or remove this div if not used --}}
                            <span class="text-white text-lg font-bold">MS</span>
                        </div>
                        <div class="hidden sm:block">
                            <h1 class="text-xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                                Meksiko Store
                            </h1>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Premium Products</p>
                        </div>
                    </div>
                </div>

                <div class="hidden md:block">
                    <div class="ml-10 flex items-baseline space-x-1">
                        <a href="/" class="group flex items-center space-x-2 px-4 py-2 rounded-lg text-gray-700 dark:text-gray-300 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-gray-700 transition-all duration-200 font-medium">
                            <span>Beranda</span>
                        </a>
                        <a href="https://heyzine.com/flip-book/20c342c823.html" target="_blank" rel="noopener noreferrer" class="group flex items-center space-x-2 px-4 py-2 rounded-lg text-gray-700 dark:text-gray-300 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-gray-700 transition-all duration-200 font-medium">
                            <span>Katalog Digital</span>
                        </a>
                        <a href="https://linktr.ee/meksikostore" target="_blank" rel="noopener noreferrer" class="group flex items-center space-x-2 px-4 py-2 rounded-lg text-gray-700 dark:text-gray-300 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-gray-700 transition-all duration-200 font-medium">
                            <span>Link Tree</span>
                        </a>
                        <a href="https://www.meksikogadai.com/" target="_blank" rel="noopener noreferrer" class="group flex items-center space-x-2 px-4 py-2 rounded-lg text-gray-700 dark:text-gray-300 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-gray-700 transition-all duration-200 font-medium">
                            <span>Meksiko Gadai</span>
                        </a>
                    </div>
                </div>

                <div class="flex items-center space-x-4">
                   
                    <div class="hidden md:block">
                        <button
                            class="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-medium px-6 py-2 rounded-lg shadow-md hover:shadow-lg transition-all duration-200"
                            onclick="window.open('https://linktr.ee/meksikostore', '_blank')"
                        >
                            Hubungi Kami
                        </button>
                    </div>
                </div>

                <div class="md:hidden flex items-center space-x-2">
                    
                    <button type="button" class="text-gray-700 dark:text-gray-300 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-gray-700 p-2 rounded-md" @click="isMenuOpen = !isMenuOpen">
                        <span x-show="!isMenuOpen">☰</span> {{-- Menu icon --}}
                        <span x-show="isMenuOpen" x-cloak>✕</span> {{-- X icon --}}
                    </button>
                </div>
            </div>
        </div>

        <div class="md:hidden bg-white dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700 shadow-lg" x-show="isMenuOpen" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
            <div class="px-2 pt-2 pb-3 space-y-1">
                <a href="/" class="group flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 dark:text-gray-300 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-gray-700 transition-all duration-200 font-medium" @click="isMenuOpen = false">
                    <span>Beranda</span>
                </a>
                <a href="https://heyzine.com/flip-book/20c342c823.html" target="_blank" rel="noopener noreferrer" class="group flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 dark:text-gray-300 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-gray-700 transition-all duration-200 font-medium" @click="isMenuOpen = false">
                    <span>Katalog Digital</span>
                </a>
                <a href="https://linktr.ee/meksikostore" target="_blank" rel="noopener noreferrer" class="group flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 dark:text-gray-300 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-gray-700 transition-all duration-200 font-medium" @click="isMenuOpen = false">
                    <span>Link Tree</span>
                </a>
                <a href="https://www.meksikogadai.com/" target="_blank" rel="noopener noreferrer" class="group flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 dark:text-gray-300 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-gray-700 transition-all duration-200 font-medium" @click="isMenuOpen = false">
                    <span>Meksiko Gadai</span>
                </a>
                <div class="pt-2 border-t border-gray-100 dark:border-gray-700">
                    <button
                        class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-medium py-3 rounded-lg shadow-md hover:shadow-lg transition-all duration-200"
                        onclick="window.open('https://linktr.ee/meksikostore', '_blank'); this.closest('[x-data]').isMenuOpen = false;"
                    >
                        Hubungi Kami
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="text-center">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-gray-100 mb-4">Selamat Datang di Meksiko Store</h1>
            <p class="text-xl text-gray-600 dark:text-gray-300 mb-8">Temukan produk-produk premium berkualitas tinggi</p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-12">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-md hover:shadow-lg transition-shadow duration-200">
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center mb-4 mx-auto">
                        <span class="text-blue-600 dark:text-blue-300 text-2xl">📚</span> {{-- Book icon placeholder --}}
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Katalog Digital</h3>
                    <p class="text-gray-600 dark:text-gray-300">Jelajahi koleksi produk kami dalam format digital yang interaktif</p>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-md hover:shadow-lg transition-shadow duration-200">
                    <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center mb-4 mx-auto">
                        <span class="text-purple-600 dark:text-purple-300 text-2xl">🔗</span> {{-- Link icon placeholder --}}
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Link Tree</h3>
                    <p class="text-gray-600 dark:text-gray-300">Akses semua platform dan layanan kami dalam satu tempat</p>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-md hover:shadow-lg transition-shadow duration-200">
                    <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center mb-4 mx-auto">
                        <span class="text-green-600 dark:text-green-300 text-2xl">📈</span> {{-- External link icon placeholder --}}
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Meksiko Gadai</h3>
                    <p class="text-gray-600 dark:text-gray-300">Layanan gadai terpercaya dengan proses yang mudah dan cepat</p>
                </div>
            </div>
        </div>
    </main>

</body>
</html>