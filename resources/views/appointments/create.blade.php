@extends('layouts.public')

@section('title', 'Agendar Cita')

@section('header', 'Agendar Cita')
@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.12/build/css/intlTelInput.css">
<style>
    .flatpickr-calendar { background: #fff; border: 1px solid #fce7f3; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
    .flatpickr-day.selected { background: #ec4899 !important; border-color: #ec4899 !important; }
    .flatpickr-day:hover { background: #fdf2f8; }
    
    /* Estilos personalizados para intl-tel-input */
    .iti { width: 100%; }
    .iti__flag-container { border-right: 1px solid #e5e7eb; }
    .iti__selected-flag { padding: 0 8px 0 12px; }
    .iti__country-list { box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); border: 1px solid #fce7f3; }
    .iti__country:hover { background-color: #fdf2f8; }
    .segment-btn {
        flex: 1;
        background: white;
        border: 1px solid #fce7f3;
        color: #9ca3af;
        padding: 0.6rem 0.4rem;
        border-radius: 0.75rem;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.025em;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.25rem;
    }
    
    .segment-btn i { font-size: 1rem; }
    
    .segment-btn:hover:not(.active) {
        border-color: #fbcfe8;
        color: #db2777;
        background: #fff5f8;
        transform: translateY(-1px);
    }
    
    .segment-btn.active {
        background: #ec4899;
        border-color: #ec4899;
        color: white;
        box-shadow: 0 4px 12px rgba(236, 72, 153, 0.25);
    }

    .segment-btn.active i {
        animation: heartBeat 1.3s infinite;
    }

    @keyframes heartBeat {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.15); }
    }

    /* PREMIUM TIME SLOT BUTTONS */
    .slot-btn {
        background: white;
        border: 1px solid #f3f4f6;
        color: #4b5563;
        padding: 0.75rem 0.5rem;
        border-radius: 1rem;
        font-size: 0.85rem;
        font-weight: 700;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        text-align: center;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }

    .slot-btn:hover:not(.active) {
        border-color: #fbcfe8;
        background: #fff5f8;
        color: #db2777;
        transform: translateY(-2px) scale(1.02);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }

    .slot-btn.active {
        background: linear-gradient(135deg, #ec4899 0%, #be185d 100%);
        border-color: #be185d;
        color: white;
        transform: scale(1.05);
        box-shadow: 0 10px 15px -3px rgba(236, 72, 153, 0.4);
        z-index: 10;
    }

    #time_placeholder_container .shadow-sm {
        cursor: pointer;
        transition: all 0.3s ease;
    }

    #time_placeholder_container:hover .shadow-sm {
        border-color: #fbcfe8;
        background: #fff5f8;
    }
</style>
@endpush

@section('content')
<div class="py-12 bg-gray-50 flex-grow">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-xl overflow-hidden">
            <div class="bg-pink-600 px-6 py-4">
                <h2 class="text-2xl font-bold text-white text-center">Agendar tu Cita</h2>
            </div>
            
            <div class="p-8">
                @if (session('error'))
                    <div class="flex items-start bg-red-50 border-l-4 border-red-500 p-4 mb-8 shadow-md rounded-r-lg animate-pulse" role="alert">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-bold text-red-800">Lo sentimos, este horario está ocupado</h3>
                            <p class="text-red-700 mt-1 text-sm leading-relaxed">
                                {{ session('error') }}
                            </p>
                        </div>
                    </div>
                @endif

                <form action="{{ route('appointments.store') }}" method="POST" id="appointmentForm">
                    @csrf
                    <input type="hidden" name="reference_image_path" id="reference_image_path">
                    
                    <div class="space-y-8">
                        <!-- Sección 1: Información Personal -->
                        <div>
                            <h3 class="text-lg font-bold text-pink-700 mb-4 flex items-center">
                                <span class="bg-pink-100 text-pink-600 w-8 h-8 rounded-full flex items-center justify-center mr-2 text-sm">1</span>
                                Información de Contacto
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6 bg-gray-50 rounded-xl border border-gray-100">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-600 mb-2 uppercase tracking-wide" for="customer_name">Nombre Completo</label>
                                    <input class="shadow-sm border border-gray-200 rounded-lg w-full py-3 px-4 text-gray-700 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-all" 
                                           id="customer_name" type="text" name="customer_name" required placeholder="Ej: María Pérez" value="{{ old('customer_name') }}">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-600 mb-2 uppercase tracking-wide" for="customer_phone">Teléfono / WhatsApp</label>
                                    <input class="shadow-sm border border-gray-200 rounded-lg w-full py-3 px-4 text-gray-700 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-all" 
                                           id="customer_phone" type="tel" name="customer_phone" required value="{{ old('customer_phone') }}">
                                    <input type="hidden" id="customer_phone_full" name="customer_phone_full">
                                </div>
                            </div>
                        </div>

                        <!-- Sección 2: Elige tu Servicio -->
                        <div>
                            <h3 class="text-lg font-bold text-pink-700 mb-4 flex items-center">
                                <span class="bg-pink-100 text-pink-600 w-8 h-8 rounded-full flex items-center justify-center mr-2 text-sm">2</span>
                                ¿Qué servicio deseas?
                            </h3>
                            <div class="p-6 bg-pink-50 bg-opacity-30 rounded-xl border border-pink-100 space-y-6">
                                <div>
                                    <select class="shadow-sm border border-pink-200 rounded-lg w-full py-3 px-4 text-gray-700 focus:outline-none focus:ring-2 focus:ring-pink-500 bg-white transition-all font-medium" 
                                            id="service_id" name="service_id" required>
                                        <option value="">Selecciona un servicio...</option>
                                        @php $currentCategory = null; @endphp
                                        @foreach($services as $service)
                                            @if($service->category->name !== $currentCategory)
                                                @if($currentCategory !== null) </optgroup> @endif
                                                <optgroup label="✨ {{ $service->category->name }} ✨">
                                                @php $currentCategory = $service->category->name; @endphp
                                            @endif
                                            <option value="{{ $service->id }}" data-price="{{ $service->price }}" 
                                                    data-image="{{ $service->image_path ? asset($service->image_path) : '' }}"
                                                    data-gallery="{{ $service->images->pluck('image_path')->map(fn($p) => asset($p))->toJson() }}"
                                                    {{ (old('service_id') == $service->id || (isset($selectedServiceId) && $selectedServiceId == $service->id)) ? 'selected' : '' }}>
                                                {{ $service->name }} — {{ $service->price_display }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Galería de Referencia Inline -->
                                <div id="service-image-preview" class="hidden animate-fade-in border-t border-pink-100 pt-6">
                                    <div class="flex items-center justify-between mb-4">
                                        <p class="text-sm font-bold text-pink-600 uppercase tracking-tighter">Diseños Inspiradores</p>
                                        <p class="text-[10px] text-gray-400 font-medium italic">Selecciona uno si te encanta ✨</p>
                                    </div>
                                    <div id="preview-grid" class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-5 gap-3">
                                        <!-- Dynamic Images -->
                                    </div>
                                    <div class="text-center mt-4">
                                        <button type="button" id="view-more-btn" class="hidden text-pink-500 text-xs font-bold hover:underline transition-all">
                                            + Ver todos los diseños
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sección 3: Agenda tu Cita -->
                        <div>
                            <h3 class="text-lg font-bold text-pink-700 mb-4 flex items-center">
                                <span class="bg-pink-100 text-pink-600 w-8 h-8 rounded-full flex items-center justify-center mr-2 text-sm">3</span>
                                ¿Cuándo nos vemos?
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Fecha -->
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 mb-1 ml-1 uppercase">Día del Servicio</label>
                                    <div class="relative group">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none transition-colors group-focus-within:text-pink-500 text-gray-300">
                                            <i class="far fa-calendar-alt"></i>
                                        </div>
                                        <input type="text" id="date_selector" readonly
                                               class="shadow-sm border border-gray-200 rounded-xl w-full py-4 pl-10 pr-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent cursor-pointer bg-white transition-all font-semibold" 
                                               placeholder="Toca para elegir fecha...">
                                    </div>
                                </div>
                                
                                <!-- Placeholder de Hora (Visual) -->
                                <div id="time_placeholder_container">
                                    <label class="block text-xs font-bold text-gray-500 mb-1 ml-1 uppercase">Hora Seleccionada</label>
                                    <div class="shadow-sm border border-gray-100 rounded-xl w-full py-4 px-4 text-pink-500 bg-gray-50 transition-all font-semibold flex justify-between items-center italic">
                                        <span id="time_display">Selecciona un día primero</span>
                                        <div class="flex items-center gap-2">
                                            <span class="text-[10px] font-bold bg-pink-100 text-pink-600 px-2 py-0.5 rounded-full uppercase tracking-tighter opacity-0 transition-opacity" id="tap-to-open">Toca para abrir</span>
                                            <i class="far fa-clock"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Contenedor INLINE de Horas -->
                            <div id="inline-slots-container" style="display: none;" class="animate-fade-in">
                                <div class="flex items-center justify-between mb-4 border-b border-pink-100 pb-2">
                                    <h4 class="text-xs font-black text-pink-600 uppercase tracking-widest">Turnos Disponibles</h4>
                                    <span id="slots-count-badge" class="bg-pink-500 text-white text-[9px] px-2 py-0.5 rounded-full font-bold"></span>
                                </div>

                                <!-- Segmentadores de Tiempo -->
                                <div class="flex gap-2 mb-6 overflow-x-auto pb-2 scrollbar-hide">
                                    <button type="button" class="segment-btn active" data-segment="all">
                                        <i class="fas fa-th-large text-pink-400"></i>
                                        Todos
                                    </button>
                                    <button type="button" class="segment-btn" data-segment="morning">
                                        <i class="fas fa-sun text-amber-400"></i>
                                        Mañana
                                    </button>
                                    <button type="button" class="segment-btn" data-segment="afternoon">
                                        <i class="fas fa-cloud-sun text-orange-400"></i>
                                        Tarde
                                    </button>
                                    <button type="button" class="segment-btn" data-segment="evening">
                                        <i class="fas fa-moon text-indigo-400"></i>
                                        Noche
                                    </button>
                                </div>

                                <div id="slots_container" class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-6 gap-3">
                                    <!-- Slots dynamically injected -->
                                </div>
                                <p id="no_slots_msg" class="text-center text-sm text-red-400 font-medium hidden py-4">No hay turnos para esta franja 🌸</p>
                            </div>
                            
                            <input type="hidden" name="appointment_date" id="appointment_date_raw" required>
                        </div>

                        <!-- Sección 4: Detalles Finales -->
                        <div>
                            <h3 class="text-lg font-bold text-pink-700 mb-4 flex items-center">
                                <span class="bg-pink-100 text-pink-600 w-8 h-8 rounded-full flex items-center justify-center mr-2 text-sm">4</span>
                                Detalles Finales
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6 bg-gray-50 rounded-xl border border-gray-100">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-600 mb-2 uppercase tracking-wide" for="location">¿Dónde será el servicio?</label>
                                    <select class="shadow-sm border border-gray-200 rounded-lg w-full py-3 px-4 text-gray-700 focus:outline-none focus:ring-2 focus:ring-pink-500 transition-all font-medium" 
                                            id="location" name="location" required>
                                        <option value="salon" {{ old('location') == 'salon' ? 'selected' : '' }}>💅 En el Salón (Normal)</option>
                                        <option value="home" {{ old('location') == 'home' ? 'selected' : '' }}>🏠 A Domicilio (+ $5k)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-600 mb-2 uppercase tracking-wide" for="offered_price">¿Deseas ofertar un precio?</label>
                                    <input class="shadow-sm border border-gray-200 rounded-lg w-full py-3 px-4 text-gray-700 focus:outline-none focus:ring-2 focus:ring-pink-500 transition-all" 
                                           id="offered_price" type="number" name="offered_price" placeholder="Escribe tu oferta..." value="{{ old('offered_price') }}">
                                    <!-- Feedback Container -->
                                    <div id="price-feedback" class="mt-2 text-[10px] hidden p-2 rounded bg-pink-50 border border-pink-100 flex items-center space-x-2">
                                        <span class="font-bold text-pink-700">Precio Sugerido:</span>
                                        <span id="real-price-display" class="font-black text-pink-800">$0</span>
                                        <div id="offer-warning" class="hidden text-red-500 font-bold ml-auto">• Mínimo Sugerido: <span id="final-adjusted-price"></span></div>
                                    </div>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-semibold text-gray-600 mb-2 uppercase tracking-wide" for="notes">Notas especiales (Color, diseño, etc.)</label>
                                    <textarea class="shadow-sm border border-gray-200 rounded-lg w-full py-3 px-4 text-gray-700 focus:outline-none focus:ring-2 focus:ring-pink-500 transition-all" 
                                              id="notes" name="notes" rows="2" placeholder="Cuéntanos cualquier detalle adicional...">{{ old('notes') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="flex items-center justify-center">
                        <button class="bg-pink-600 hover:bg-pink-700 text-white font-bold py-3 px-8 rounded-full focus:outline-none focus:shadow-outline transition duration-300 transform hover:scale-105" 
                                type="submit">
                            Solicitar Cita
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.12/build/js/intlTelInput.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // INICIALIZAR INTL-TEL-INPUT
        const phoneInput = document.querySelector("#customer_phone");
        const phoneInputFull = document.querySelector("#customer_phone_full");
        
        const iti = window.intlTelInput(phoneInput, {
            initialCountry: "co",
            preferredCountries: ["co", "us", "mx", "es", "ar", "ve"],
            utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.12/build/js/utils.js",
            separateDialCode: true,
            autoPlaceholder: "aggressive",
            formatOnDisplay: true,
            nationalMode: false
        });
        
        // Actualizar el campo oculto con el número completo en formato internacional
        phoneInput.addEventListener('blur', function() {
            const fullNumber = iti.getNumber();
            phoneInputFull.value = fullNumber;
            console.log('Número completo:', fullNumber);
        });
        
        // Validar antes de enviar el formulario
        const form = document.getElementById('appointmentForm');
        form.addEventListener('submit', function(e) {
            const name = document.getElementById('customer_name').value.trim();
            const fullNumber = iti.getNumber();
            phoneInputFull.value = fullNumber;
            
            // 1. Validar Nombre
            if (!name) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Falta tu nombre',
                    text: 'Por favor, dinos quién eres para agendar tu cita.',
                    confirmButtonColor: '#ec4899'
                });
                return false;
            }

            // 2. Validar Servicio
            const service = document.getElementById('service_id').value;
            if (!service) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Selecciona un servicio',
                    text: 'Por favor, elige el servicio que deseas realizarte.',
                    confirmButtonColor: '#ec4899'
                });
                return false;
            }

            // 3. Validar Teléfono / WhatsApp
            if (!iti.isValidNumber()) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Número inválido',
                    text: 'El número de teléfono ingresado no es válido para el país seleccionado.',
                    confirmButtonColor: '#ec4899'
                });
                return false;
            }

            // 3. Validar que sea Celular (WhatsApp)
            const numberType = iti.getNumberType();
            if (numberType !== intlTelInputUtils.numberType.MOBILE && numberType !== intlTelInputUtils.numberType.FIXED_LINE_OR_MOBILE) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: '¿Es un celular?',
                    text: 'Para enviarte recordatorios por WhatsApp, necesitamos un número de celular válido.',
                    confirmButtonColor: '#ec4899'
                });
                return false;
            }

            // 4. Validar Fecha
            if (!selectedDate) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Selecciona una fecha',
                    text: 'Aun no has elegido el día para tu servicio.',
                    confirmButtonColor: '#ec4899'
                });
                // Hacer scroll al selector de fecha si es necesario
                document.getElementById('date_selector').scrollIntoView({ behavior: 'smooth', block: 'center' });
                return false;
            }

            // 5. Validar Hora
            if (!selectedTime) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Elige una hora',
                    text: '¡Casi terminamos! Por favor selecciona un horario disponible.',
                    confirmButtonColor: '#ec4899'
                });
                // Abrir el contenedor de slots si está cerrado
                const container = document.getElementById('inline-slots-container');
                if (container.style.display === 'none') {
                    document.getElementById('time_placeholder_container').click();
                } else {
                    container.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                return false;
            }
        });
        
        const serviceSelect = document.getElementById('service_id');
        const offerInput = document.getElementById('offered_price');
        const feedbackDiv = document.getElementById('price-feedback');
        const realPriceDisplay = document.getElementById('real-price-display');
        const warningDiv = document.getElementById('offer-warning');
        const okDiv = document.getElementById('offer-ok');
        const finalAdjustedDisplay = document.getElementById('final-adjusted-price');

        // Image elements
        const imagePreviewDiv = document.getElementById('service-image-preview');
        const previewGrid = document.getElementById('preview-grid');
        const viewMoreBtn = document.getElementById('view-more-btn');
        
        let currentImages = [];
        let isExpanded = false;
        let selectedImageSrc = null;
        let selectedDate = null;
        let selectedTime = null;

        // INITIALIZE FLATPICKR
        const dateSelector = document.getElementById('date_selector');
        const fp = flatpickr(dateSelector, {
            inline: false,
            locale: "es",
            minDate: "today",
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "F j, Y",
            onClose: function(selectedDates, dateStr) {
                if (dateStr) {
                    selectedDate = dateStr;
                    selectedTime = null;
                    document.getElementById('appointment_date_raw').value = '';
                    
                    // Reset UI
                    document.getElementById('time_display').innerText = 'Toca aquí para elegir hora...';
                    document.getElementById('time_display').classList.remove('text-gray-400', 'text-pink-600');
                    document.getElementById('time_display').classList.add('text-pink-500');
                    document.getElementById('tap-to-open').classList.remove('opacity-0');
                    
                    document.getElementById('inline-slots-container').style.display = 'none';
                    
                    fetchBusySlots(dateStr);
                }
            }
        });

        // Toggle para mostrar/ocultar slots
        document.getElementById('time_placeholder_container').addEventListener('click', function() {
            if (!selectedDate) {
                Swal.fire({
                    icon: 'info',
                    text: 'Por favor, selecciona una fecha primero 📅',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
                return;
            }
            
            const container = document.getElementById('inline-slots-container');
            if (container.style.display === 'none') {
                container.style.display = 'block';
                container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                document.getElementById('tap-to-open').classList.add('opacity-0');
            } else {
                container.style.display = 'none';
                if (!selectedTime) {
                    document.getElementById('tap-to-open').classList.remove('opacity-0');
                }
            }
        });

        // Lógica de Segmentación
        let allAvailableSlots = [];
        let currentSegment = 'all';

        document.querySelectorAll('.segment-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.segment-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                currentSegment = btn.dataset.segment;
                renderFilteredSlots();
            });
        });

        function renderFilteredSlots() {
            const container = document.getElementById('slots_container');
            const msg = document.getElementById('no_slots_msg');
            container.innerHTML = '';
            
            const filtered = allAvailableSlots.filter(time => {
                const hour = parseInt(time.split(':')[0]);
                if (currentSegment === 'morning') return hour < 12;
                if (currentSegment === 'afternoon') return hour >= 12 && hour < 17;
                if (currentSegment === 'evening') return hour >= 17;
                return true; // includes 'all'
            });

            if (filtered.length === 0) {
                msg.classList.remove('hidden');
                document.getElementById('slots-count-badge').classList.add('hidden');
                return;
            }

            msg.classList.add('hidden');
            const badge = document.getElementById('slots-count-badge');
            badge.innerText = `${filtered.length} ${currentSegment === 'all' ? 'total' : 'disponibles'}`;
            badge.classList.remove('hidden');
            filtered.forEach(time => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.innerText = formatTo12h(time);
                btn.className = "slot-btn" + (selectedTime === time ? " active" : "");
                btn.onclick = () => {
                    document.querySelectorAll('.slot-btn').forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    selectedTime = time;
                    document.getElementById('time_display').innerText = formatTo12h(time);
                    document.getElementById('time_display').classList.remove('text-pink-500', 'italic');
                    document.getElementById('time_display').classList.add('text-pink-600', 'font-black');
                    document.getElementById('appointment_date_raw').value = `${selectedDate} ${time}`;
                    
                    if (window.innerWidth < 768) {
                        btn.closest('form').querySelector('button[type="submit"]').scrollIntoView({ behavior: 'smooth', block: 'end' });
                    }
                };
                container.appendChild(btn);
            });
        }

        async function fetchBusySlots(date) {
            const container = document.getElementById('slots_container');
            const msg = document.getElementById('no_slots_msg');
            const inlineContainer = document.getElementById('inline-slots-container');
            const badge = document.getElementById('slots-count-badge');
            
            container.innerHTML = '<div class="col-span-full py-8 text-center text-pink-400 font-medium"><i class="fas fa-magic fa-spin mr-2"></i> Buscando turnos disponibles...</div>';
            // inlineContainer.style.display = 'block'; // Hacemos que sea manual
            msg.classList.add('hidden');
            badge.classList.add('hidden');
            allAvailableSlots = [];
            
            // Remove previous custom message if exists
            const prevCustomMsg = document.getElementById('custom_availability_msg');
            if (prevCustomMsg) prevCustomMsg.remove();

            try {
                const resp = await fetch(`{{ url('api/bot/busy-slots') }}?date=${date}`);
                if (!resp.ok) {
                     let errText = resp.statusText;
                     try {
                         const errJson = await resp.json();
                         if(errJson.message) errText = errJson.message;
                     } catch(jsonErr) {}
                     throw new Error('Error ' + resp.status + ': ' + errText);
                }
                const data = await resp.json();
                
                let busySlots = [];
                let workingHours = null;
                let customMessage = null;

                if (Array.isArray(data)) {
                    busySlots = data;
                } else {
                    busySlots = data.busy || [];
                    workingHours = data.working_hours;
                    customMessage = data.message;
                }

                if (customMessage) {
                    const msgDiv = document.createElement('div');
                    msgDiv.id = 'custom_availability_msg';
                    msgDiv.className = 'col-span-full mb-3 p-3 bg-blue-50 text-blue-700 text-sm rounded-lg border border-blue-200 font-medium flex items-center';
                    msgDiv.innerHTML = `<i class="fas fa-info-circle mr-2"></i> ${customMessage}`;
                    container.parentElement.insertBefore(msgDiv, container);
                }

                generateSlots(busySlots, workingHours);
            } catch (e) {
                container.innerHTML = '<div class="col-span-full py-4 text-center text-red-500 font-bold">Error cargando horarios</div>';
                console.error(e);
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error al consultar horarios',
                    text: e.message || 'Error desconocido',
                    footer: '<small>Si persiste, contacta al administrador.</small>',
                    confirmButtonColor: '#ec4899'
                });
            }
        }

        function formatTo12h(timeStr) {
            if (!timeStr) return '';
            const [h, m] = timeStr.split(':');
            let hh = parseInt(h);
            const ampm = hh >= 12 ? ' PM' : ' AM';
            hh = hh % 12 || 12;
            return `${hh}:${m}${ampm}`;
        }

        function generateSlots(busySlots, workingHours = null) {
            const container = document.getElementById('slots_container');
            const msg = document.getElementById('no_slots_msg');
            const badge = document.getElementById('slots-count-badge');
            container.innerHTML = '';
            
            let possibleSlots = [];

            if (workingHours && Array.isArray(workingHours) && workingHours.length > 0) {
                possibleSlots = workingHours;
            } else {
                for (let h = 8; h <= 21; h++) {
                    possibleSlots.push(`${h.toString().padStart(2, '0')}:00`);
                    possibleSlots.push(`${h.toString().padStart(2, '0')}:30`);
                }
            }

            let availableCount = 0;
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const todayStr = `${year}-${month}-${day}`;
            
            const currentH = now.getHours();
            const currentM = now.getMinutes();
            const currentTimeVal = currentH * 60 + currentM;

            possibleSlots.forEach(time => {
                const [h, m] = time.split(':').map(Number);
                const slotTimeVal = h * 60 + m;
                
                // Check past time
                if (typeof selectedDate !== 'undefined' && selectedDate === todayStr && slotTimeVal <= currentTimeVal) {
                    return;
                }

                // Check busy
                // Busy slots are ranges [start, end)
                let isBusy = false;
                for (let b of busySlots) {
                    const [bh1, bm1] = b.start.split(':').map(Number);
                    const [bh2, bm2] = b.end.split(':').map(Number);
                    const startVal = bh1 * 60 + bm1;
                    const endVal = bh2 * 60 + bm2;
                    
                    // Slot is busy if it STARTs within a busy range
                    // Or if it overlaps? System seems to be slot-based starts.
                    // If slot is 10:00, and busy is 10:00-11:00, then 10:00 is busy.
                    if (slotTimeVal >= startVal && slotTimeVal < endVal) {
                        isBusy = true;
                        break;
                    }
                }

                if (!isBusy) {
                    allAvailableSlots.push(time);
                }
            });

            if (allAvailableSlots.length === 0) {
                container.innerHTML = '';
                msg.classList.remove('hidden');
                badge.classList.add('hidden');
            } else {
                badge.innerText = `${allAvailableSlots.length} total`;
                badge.classList.remove('hidden');
                
                // Set default segment to 'all' as requested
                currentSegment = 'all';
                document.querySelectorAll('.segment-btn').forEach(b => {
                    b.classList.toggle('active', b.dataset.segment === 'all');
                });

                renderFilteredSlots();
            }
        }

        function updateFeedback() {
            const selectedOption = serviceSelect.options[serviceSelect.selectedIndex];
            if(!selectedOption) return;

            const basePrice = parseFloat(selectedOption.getAttribute('data-price'));
            const imageUrl = selectedOption.getAttribute('data-image');
            const rawGallery = selectedOption.getAttribute('data-gallery');
            const gallery = rawGallery ? JSON.parse(rawGallery) : [];
            const offerValue = parseFloat(offerInput.value);

            let allImages = [];
            if (imageUrl) allImages.push(imageUrl);
            if (gallery.length > 0) allImages = allImages.concat(gallery);
            
            if (JSON.stringify(allImages) !== JSON.stringify(currentImages)) {
                currentImages = allImages;
                isExpanded = false;
                selectedImageSrc = null;
                document.getElementById('reference_image_path').value = '';
            }

            if (allImages.length > 0) {
                imagePreviewDiv.classList.remove('hidden');
                renderImages();
            } else {
                imagePreviewDiv.classList.add('hidden');
            }

            if (!basePrice) {
                feedbackDiv.classList.add('hidden');
                offerInput.disabled = true;
                return;
            }

            checkRestrictions(basePrice, offerValue);
        }

        function renderImages() {
            previewGrid.innerHTML = '';
            const limit = isExpanded ? currentImages.length : 4;
            const visibleImages = currentImages.slice(0, limit);
            
            visibleImages.forEach(src => {
                createImageElement(src);
            });

            if (currentImages.length > 4) {
                viewMoreBtn.classList.remove('hidden');
                viewMoreBtn.innerText = isExpanded ? "- Ver menos" : "+ Ver más diseños (" + (currentImages.length - 4) + " más)";
                viewMoreBtn.onclick = () => { isExpanded = !isExpanded; renderImages(); };
            } else {
                viewMoreBtn.classList.add('hidden');
            }
        }

        function createImageElement(src) {
             const div = document.createElement('div');
             let classes = "aspect-square relative overflow-visible rounded-lg shadow-md border bg-gray-50 group hover:z-50 transition-all duration-300 transform hover:scale-110 cursor-pointer";
             
             if (src === selectedImageSrc) {
                 classes += " ring-4 ring-pink-500 scale-105 z-40 border-pink-500";
             } else {
                 classes += " border-pink-100 hover:border-pink-300";
             }
             
             div.className = classes;
             
             if (src === selectedImageSrc) {
                 const badge = document.createElement('div');
                 badge.className = "absolute -top-2 -right-2 bg-pink-600 text-white rounded-full p-1 shadow-lg z-50";
                 badge.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>';
                 div.appendChild(badge);
             }

             const img = document.createElement('img');
             img.src = src;
             img.className = "w-full h-full object-cover rounded-lg shadow-sm";
             
             div.onclick = () => {
                 selectedImageSrc = (selectedImageSrc === src) ? null : src;
                 document.getElementById('reference_image_path').value = selectedImageSrc || '';
                 renderImages();
             };
             
             div.appendChild(img);
             previewGrid.appendChild(div);
        }

        function checkRestrictions(basePrice, offerValue) {
            if (basePrice < 40000) {
                offerInput.disabled = true;
                offerInput.value = '';
                offerInput.placeholder = "No disponible para este servicio";
                offerInput.classList.add('bg-gray-100', 'cursor-not-allowed');
                feedbackDiv.classList.remove('hidden');
                realPriceDisplay.innerText = '$' + basePrice.toLocaleString('es-CO');
                warningDiv.classList.add('hidden');
                okDiv.classList.add('hidden');
                
                let resMsg = document.getElementById('restriction-msg');
                if(!resMsg) {
                    resMsg = document.createElement('div');
                    resMsg.id = 'restriction-msg';
                    resMsg.className = 'text-gray-500 font-semibold text-xs mt-1';
                    resMsg.innerText = '⚠️ Las ofertas solo aplican para servicios mayores a $40.000';
                    feedbackDiv.appendChild(resMsg);
                }
                resMsg.classList.remove('hidden');
                return;
            } else {
                offerInput.disabled = false;
                offerInput.placeholder = "Ingrese su oferta...";
                offerInput.classList.remove('bg-gray-100', 'cursor-not-allowed');
                const rMsg = document.getElementById('restriction-msg');
                if(rMsg) rMsg.classList.add('hidden');
            }

            realPriceDisplay.innerText = '$' + basePrice.toLocaleString('es-CO');
            feedbackDiv.classList.remove('hidden');
            
            if (!offerValue) {
                warningDiv.classList.add('hidden');
                okDiv.classList.add('hidden');
                return;
            }

            const minAllowed = basePrice - 5000;
            if (offerValue < minAllowed) {
                warningDiv.classList.remove('hidden');
                okDiv.classList.add('hidden');
                finalAdjustedDisplay.innerText = '$' + minAllowed.toLocaleString('es-CO');
            } else {
                warningDiv.classList.add('hidden');
                okDiv.classList.remove('hidden');
            }
        }

        serviceSelect.addEventListener('change', updateFeedback);
        offerInput.addEventListener('input', updateFeedback);
        updateFeedback();
    });
</script>
@endpush
@endsection
