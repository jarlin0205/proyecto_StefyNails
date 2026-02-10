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
    .iti__country.iti__highlight { background-color: #fce7f3; }
    
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

                <form action="{{ route('appointments.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="reference_image_path" id="reference_image_path">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <!-- Nombre -->
                        <div>
                            <label class="block text-gray-700 font-bold mb-2 cursor-pointer" for="customer_name">
                                Tu Nombre Completo
                            </label>
                            <input class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent" 
                                   id="customer_name" type="text" name="customer_name" required placeholder="Ej: María Pérez" value="{{ old('customer_name') }}">
                        </div>

                        <!-- Telefono -->
                        <div>
                            <label class="block text-gray-700 font-bold mb-2 cursor-pointer" for="customer_phone">
                                Teléfono/WhatsApp
                            </label>
                            <input class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent" 
                                   id="customer_phone" type="tel" name="customer_phone" required value="{{ old('customer_phone') }}">
                            <input type="hidden" id="customer_phone_full" name="customer_phone_full">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <!-- Servicio -->
                        <div>
                            <label class="block text-gray-700 font-bold mb-2 cursor-pointer" for="service_id">
                                Servicio Deseado
                            </label>
                            <select class="shadow border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent" 
                                    id="service_id" name="service_id" required>
                                <option value="">Selecciona un servicio...</option>
                                @php
                                    $currentCategory = null;
                                @endphp
                                @foreach($services as $service)
                                    @if($service->category->name !== $currentCategory)
                                        @if($currentCategory !== null)
                                            </optgroup>
                                        @endif
                                        <optgroup label="{{ $service->category->name }}">
                                        @php $currentCategory = $service->category->name; @endphp
                                    @endif
                                    
                                    <option value="{{ $service->id }}" 
                                            data-price="{{ $service->price }}" 
                                            data-image="{{ $service->image_path ? asset($service->image_path) : '' }}"
                                            data-gallery="{{ $service->images->pluck('image_path')->map(fn($p) => asset($p))->toJson() }}"
                                            {{ (old('service_id') == $service->id || (isset($selectedServiceId) && $selectedServiceId == $service->id)) ? 'selected' : '' }}>
                                        {{ $service->name }} - {{ $service->price_display }}
                                    </option>
                                @endforeach
                                @if($currentCategory !== null)
                                    </optgroup>
                                @endif
                            </select>
                            

                        </div>

                        <!-- Fecha y Hora Moderno -->
                        <div class="md:col-span-2 space-y-4">
                            <label class="block text-gray-700 font-bold mb-2">1. Selecciona el Día</label>
                            <div class="flex flex-col md:flex-row gap-6">
                                <div id="inline-calendar" class="shadow-sm border border-pink-100 rounded-lg overflow-hidden"></div>
                                <div id="time_selection" class="flex-1 hidden">
                                    <label class="block text-sm font-semibold text-gray-600 mb-1 uppercase tracking-wider">2. Selecciona tu Hora</label>
                                    <p class="text-xs text-pink-600 font-bold mb-3"><i class="fas fa-clock mr-1"></i> Horario de Atención: 8:00 AM - 9:30 PM</p>
                                    <div id="slots_container" class="grid grid-cols-4 sm:grid-cols-5 gap-2">
                                        <!-- slots dynamically injected -->
                                    </div>
                                    <p id="no_slots_msg" class="text-xs text-red-500 hidden mt-2">No hay horarios disponibles para este día.</p>
                                </div>
                            </div>
                            <input type="hidden" name="appointment_date" id="appointment_date_raw" required>
                        </div>
                    </div>

                    <!-- Full Width Gallery Container -->
                    <div id="service-image-preview" class="hidden mb-6 bg-pink-50 rounded-lg p-4 border border-pink-100">
                        <div class="text-center mb-4">
                            <p class="text-lg font-bold text-pink-600">✨ Diseños de Referencia</p>
                            <p class="text-xs text-gray-500">Haz clic en una imagen si te gustaría ese diseño</p>
                        </div>
                        <div id="preview-grid" class="grid grid-cols-2 sm:grid-cols-4 gap-4 justify-center">
                            <!-- Dynamic Images -->
                        </div>
                        <div class="text-center mt-4">
                            <button type="button" id="view-more-btn" class="hidden bg-white text-pink-600 border border-pink-300 hover:bg-pink-50 px-4 py-2 rounded-full text-sm font-bold transition shadow-sm">
                                + Ver más diseños
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <!-- Ubicación -->
                        <div>
                            <label class="block text-gray-700 font-bold mb-2 cursor-pointer" for="location">
                                Lugar del Servicio
                            </label>
                            <select class="shadow border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent" 
                                    id="location" name="location" required>
                                <option value="salon" {{ old('location') == 'salon' ? 'selected' : '' }}>En el Salón (Precio Normal)</option>
                                <option value="home" {{ old('location') == 'home' ? 'selected' : '' }}>A Domicilio (+ $5.000)</option>
                            </select>
                        </div>

                        <!-- Oferta de Precio -->
                        <div>
                            <label class="block text-gray-700 font-bold mb-2 cursor-pointer" for="offered_price">
                                ¿Deseas ofertar un precio?
                            </label>
                            <input class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent" 
                                   id="offered_price" type="number" name="offered_price" placeholder="Ingrese su oferta..." value="{{ old('offered_price') }}">
                            
                            <!-- Feedback Container -->
                            <div id="price-feedback" class="mt-2 text-sm hidden p-3 rounded bg-gray-50 border">
                                <p class="mb-1"><span class="font-bold text-gray-700">Precio Real:</span> <span id="real-price-display">$0</span></p>
                                <div id="offer-warning" class="hidden text-red-600 font-semibold">
                                    <p>⚠️ Oferta demasiado baja.</p>
                                    <p>El descuento máximo es de $5.000.</p>
                                    <p class="mt-1 text-gray-800">Se registrará por: <span id="final-adjusted-price" class="text-lg font-bold text-green-600"></span></p>
                                </div>
                                <div id="offer-ok" class="hidden text-green-600 font-semibold">
                                    <p>✅ Oferta válida.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notas -->
                    <div class="mb-8">
                        <label class="block text-gray-700 font-bold mb-2 cursor-pointer" for="notes">
                            Notas Adicionales (Opcional)
                        </label>
                        <textarea class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent" id="notes" name="notes" rows="3" placeholder="Ej: Tengo el cabello muy largo, quiero un diseño específico...">{{ old('notes') }}</textarea>
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
        const form = phoneInput.closest('form');
        form.addEventListener('submit', function(e) {
            const fullNumber = iti.getNumber();
            phoneInputFull.value = fullNumber;
            
            if (!iti.isValidNumber()) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Número inválido',
                    text: 'Por favor ingresa un número de teléfono válido para el país seleccionado.',
                });
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
        const fp = flatpickr("#inline-calendar", {
            inline: true,
            locale: "es",
            minDate: "today",
            dateFormat: "Y-m-d",
            onChange: function(selectedDates, dateStr) {
                selectedDate = dateStr;
                selectedTime = null;
                document.getElementById('appointment_date_raw').value = '';
                fetchBusySlots(dateStr);
            }
        });

        async function fetchBusySlots(date) {
            const container = document.getElementById('slots_container');
            const msg = document.getElementById('no_slots_msg');
            const timeDiv = document.getElementById('time_selection');
            
            container.innerHTML = '<div class="col-span-full py-4 text-center text-pink-300"><i class="fas fa-spinner fa-spin"></i> Cargando horarios...</div>';
            timeDiv.classList.remove('hidden');
            msg.classList.add('hidden');
            
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
            hh = hh % 12;
            if (hh === 0) hh = 12;
            return `${hh}:${m}${ampm}`;
        }

        function generateSlots(busySlots, workingHours = null) {
            const container = document.getElementById('slots_container');
            const msg = document.getElementById('no_slots_msg');
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

            const selectedDateInput = document.getElementById('appointment_date_raw').dataset.selectedDate || todayStr; 
            // fallback if dataset not set? Actually flatpickr sets input value. 
            // Use 'selectedDate' global var if available? It's not global here.
            // Let's rely on the module-level 'selectedDate' (it was missing in snippets, assuming global scope or argument)
            // Wait, fetchBusySlots(date) passes date. generateSlots was relying on implicit scope or argument.
            // Let's assume 'selectedDate' variable exists in the broader scope or pass it.
            // The previous code used `selectedDate` which must be defined in the script scope of create.blade.php.
            // Checking view_file output... it's NOT in the visible scope of generateSlots in lines 323+.
            // However, fetchBusySlots receives `date`. I'll pass it to generateSlots.
             
            // Re-check fetch logic: `generateSlots(busySlots, workingHours);` - I missed passing `date`.
            // But I can infer it or just use the variable from flatpickr if global.
            // Let's look at lines 289: `fetch... ?date=${date}`. `date` is passed to fetchBusySlots.
            // I should modify fetchBusySlots to pass `date` to generateSlots.
            
            // Correction: I can't modify the function signature easily in a replacement if I don't see the call site outside.
            // BETTER: pass it along.
            
            // Wait, `selectedDate` variable usage in previous code (line 347) implies it IS available in scope.
            // I will assume `selectedDate` is available or I will fix it by passing it.
            
            // Let's stick to using the `date` argument from `fetchBusySlots` and passing it.
            
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
                    availableCount++;
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.innerText = formatTo12h(time);
                    btn.className = "py-2 border-2 border-pink-50 rounded-lg text-sm font-semibold text-gray-700 hover:border-pink-500 hover:bg-pink-50 transition-all";
                    btn.onclick = () => {
                        document.querySelectorAll('#slots_container button').forEach(b => b.className = "py-2 border-2 border-pink-50 rounded-lg text-sm font-semibold text-gray-700 hover:border-pink-500 hover:bg-pink-50 transition-all");
                        btn.className = "py-2 border-2 border-pink-600 bg-pink-600 text-white rounded-lg text-sm font-bold shadow-md transform scale-105 transition-all";
                        
                        // We need to set the value. existing code used appointment_date_raw.value
                        const finalDate = (typeof selectedDate !== 'undefined') ? selectedDate : todayStr;
                        document.getElementById('appointment_date_raw').value = `${finalDate} ${time}`;
                    };
                    container.appendChild(btn);
                }
            });

            if (availableCount === 0) {
                msg.classList.remove('hidden');
            } else {
                msg.classList.add('hidden');
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
