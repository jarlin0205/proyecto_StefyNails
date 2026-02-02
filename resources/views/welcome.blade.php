@extends('layouts.public')

@section('title', 'Bienvenido')

@section('content')
<!-- Hero Section -->
<section id="inicio" class="hero-bridge relative bg-pink-100 h-screen flex items-center justify-center overflow-hidden">
    <div class="absolute inset-0 z-0">
        <!-- Placeholder for hero background image if available, using CSS pattern for now -->
        <div class="absolute inset-0 bg-gradient-to-r from-pink-200 to-purple-200 opacity-50"></div>
    </div>
    <div class="relative z-10 text-center px-4">
        <h1 class="hero-title-bridge text-5xl md:text-7xl font-bold text-pink-700 mb-6 drop-shadow-md">
            Stylo Tefy
        </h1>
        <p class="text-xl md:text-2xl text-gray-700 mb-8 max-w-2xl mx-auto">
            Belleza y elegancia para tus manos y cabello. Déjate consentir por profesionales.
        </p>
        <a href="#servicios" class="bg-pink-600 text-white font-bold py-3 px-8 rounded-full text-lg hover:bg-pink-700 transition duration-300 shadow-lg transform hover:scale-105">
            Ver Servicios
        </a>
    </div>
</section>

<!-- Services Section -->
<section id="servicios" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-gray-800 mb-4">Nuestros Servicios</h2>
            <div class="w-24 h-1 bg-pink-500 mx-auto"></div>
        </div>

        @foreach($categories as $category)
            @if($category->services->count() > 0)
                <div class="mb-16">
                    <h3 class="text-3xl font-bold text-pink-600 mb-8 pl-4 border-l-4 border-pink-500">
                        {{ $category->name }}
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        @foreach($category->services as $service)
                            <div class="bg-white rounded-xl shadow-lg hover:shadow-2xl transition duration-300 overflow-hidden border border-gray-100 flex flex-col h-full">
                                <div class="h-40 overflow-hidden bg-gray-100 relative group service-carousel" data-interval="3000">
                                    <div class="carousel-track h-full w-full relative">
                                        <!-- Main Image -->
                                        @if($service->image_path)
                                            <img src="{{ asset($service->image_path) }}" alt="{{ $service->name }}" class="carousel-item absolute top-0 left-0 w-full h-full object-cover transition-opacity duration-1000 opacity-100">
                                        @else
                                            <div class="carousel-item absolute top-0 left-0 w-full h-full flex items-center justify-center text-gray-300 bg-gray-100 opacity-100">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                        @endif
                                        
                                        <!-- Gallery Images -->
                                        @foreach($service->images as $img)
                                            <img src="{{ asset($img->image_path) }}" alt="{{ $service->name }}" class="carousel-item absolute top-0 left-0 w-full h-full object-cover transition-opacity duration-1000 opacity-0">
                                    @endforeach
                                </div>
                            </div>
                            <div class="p-6 flex-grow flex flex-col justify-between">
                                    <div>
                                        <div class="flex justify-between items-baseline mb-2">
                                            <h4 class="text-base font-semibold text-gray-800">{{ $service->name }}</h4>
                                            <span class="text-pink-600 font-semibold text-sm whitespace-nowrap ml-2">{{ $service->price_display }}</span>
                                        </div>
                                        <p class="text-gray-600 mb-2 text-xs line-clamp-3">
                                            {{ $service->description }}
                                        </p>
                                        @if($service->duration)
                                            <div class="mb-3">
                                                <span class="text-[10px] font-semibold text-gray-500 bg-gray-50 border border-gray-100 px-1.5 py-0.5 rounded inline-flex items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    {{ $service->duration }}
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="mt-1">
                                        <a href="{{ route('appointments.create', ['service_id' => $service->id]) }}" class="block w-full text-center bg-pink-600 hover:bg-pink-700 text-white font-bold py-1.5 px-4 rounded-lg text-sm transition shadow-md">
                                            Agendar Cita
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
    </div>
</section>

<!-- Contact Section -->
<section id="contacto" class="py-20 bg-gray-900 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-bold mb-8">Sobre Stylo Tefy</h2>
        <p class="text-xl mb-8 max-w-3xl mx-auto text-gray-300">
            Stylo Tefy es un espacio creado para realzar la belleza y confianza de cada persona. Nos especializamos en técnicas modernas de manicura y estilismo, garantizando un servicio personalizado que se adapta a tus necesidades y resalta tu esencia única.
        </p>
        <div class="mt-12">
            <a href="https://wa.me/573043567815" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-3 bg-green-500 hover:bg-green-600 text-white font-bold py-4 px-8 rounded-full shadow-lg transform transition hover:scale-105">
                <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
                <span>Resolvemos tus dudas por WhatsApp</span>
            </a>
        </div>
</section>
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const carousels = document.querySelectorAll('.service-carousel');
        
        carousels.forEach(carousel => {
            const items = carousel.querySelectorAll('.carousel-item');
            if (items.length <= 1) return;

            let currentIndex = 0;
            const intervalTime = 3000; // 3 seconds

            setInterval(() => {
                // Fade out current
                items[currentIndex].style.opacity = '0';
                
                // Next index
                currentIndex = (currentIndex + 1) % items.length;
                
                // Fade in next
                items[currentIndex].style.opacity = '1';
            }, intervalTime);
        });
    });
</script>
@endpush
