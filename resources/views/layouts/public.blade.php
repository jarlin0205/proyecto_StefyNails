<!DOCTYPE html>
<html lang="es" style="background-color: #fdf2f8;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stefy Nails - @yield('title', 'Inicio')</title>
    @vite(['resources/js/app.js'])
    <style>
        /* Estilos Críticos Inmediatos (Evitan el estado Blanco y Negro) */
        :root { background-color: #fdf2f8; }
        html, body { margin: 0; padding: 0; background-color: #fdf2f8; font-family: ui-sans-serif, system-ui, sans-serif; }
        
        /* Navbar de Emergencia (Aparece antes de que Tailwind cargue) */
        .nav-bridge { 
            background-color: white !important; 
            height: 64px; 
            width: 100%; 
            position: fixed; 
            top: 0; 
            left: 0; 
            z-index: 100; 
            display: flex; 
            align-items: center; 
            border-bottom: 1px solid #f1f5f9;
            box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1);
        }
        .logo-bridge { color: #db2777 !important; font-size: 1.5rem; font-weight: bold; padding-left: 2rem; text-decoration: none; }
        
        /* Hero de Emergencia */
        .hero-bridge { background-color: #fdf2f8; height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; }
        .hero-title-bridge { color: #be185d; font-size: 3.5rem; font-weight: bold; margin: 0; }
        
        /* Ocultar contenido crudo hasta que el diseño esté listo */
        body { visibility: hidden; }
    </style>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Mostrar la página solo cuando los estilos base estén listos
        setTimeout(() => { document.body.style.visibility = 'visible'; }, 50);
        window.addEventListener('load', () => { document.body.style.visibility = 'visible'; });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @stack('styles')
</head>
<body class="bg-white font-sans antialiased scroll-smooth">
    <!-- Navbar -->
    <nav class="bg-white shadow-md fixed w-full z-[100] top-0 left-0 border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center">
                     <a href="{{ route('home') }}" class="text-2xl font-bold text-pink-600 hover:text-pink-700 transition">
                        Stefy Nails
                     </a>
                </div>
                
                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-6">
                    <a href="{{ route('home') }}#inicio" class="text-gray-600 hover:text-pink-600 font-medium transition">Inicio</a>
                    <a href="{{ route('home') }}#servicios" class="text-gray-600 hover:text-pink-600 font-medium transition">Servicios</a>
                    <a href="{{ route('home') }}#contacto" class="text-gray-600 hover:text-pink-600 font-medium transition">Contacto</a>
                    <a href="{{ route('appointments.create') }}" class="bg-pink-600 text-white px-5 py-2 rounded-full font-bold shadow-md hover:bg-pink-700 hover:shadow-lg transition transform hover:-translate-y-0.5">
                        Agendar Cita
                    </a>
                    <a href="{{ route('login') }}" class="text-gray-400 hover:text-pink-600 transition" title="Admin Login">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                    </a>
                </div>

                <!-- Mobile Menu Button (Simple Placeholder if needed, or just let links stack/scroll) -->
                <div class="md:hidden flex items-center">
                    <a href="{{ route('appointments.create') }}" class="bg-pink-600 text-white text-sm px-3 py-2 rounded-lg font-bold mr-2">
                        Agendar
                    </a>
                    <!-- Mobile Login -->
                    <a href="{{ route('login') }}" class="text-gray-500 p-2">
                         <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Content -->
    <div class="pt-20"> <!-- Increased padding for proper spacing -->
        @yield('content')
    </div>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-8 mt-auto">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <p>&copy; {{ date('Y') }} Stefy Nails. Todos los derechos reservados.</p>
        </div>
    </footer>
    
    @stack('scripts')
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if($errors->any())
                Swal.fire({
                    icon: 'error',
                    title: 'Errores en el formulario',
                    html: '<ul class="text-left">@foreach($errors->all() as $error)<li>• {{ $error }}</li>@endforeach</ul>',
                    confirmButtonColor: '#ec4899'
                });
            @endif

            @if(session('success'))
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: "{{ session('success') }}",
                    confirmButtonColor: '#ec4899'
                });
            @endif

            @if(session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: "{{ session('error') }}",
                    confirmButtonColor: '#ec4899'
                });
            @endif
        });
    </script>
</body>
</html>
