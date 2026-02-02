@extends('layouts.admin')

@section('header', 'Agendar Nueva Cita (Admin)')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.12/build/css/intlTelInput.css">
<style>
    .iti { width: 100%; }
    .iti__flag-container { border-right: 1px solid #e5e7eb; }
    .iti__selected-flag { padding: 0 8px 0 12px; }
    .iti__country-list { box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); border: 1px solid #fce7f3; }
    .iti__country:hover { background-color: #fdf2f8; }
    .iti__country.iti__highlight { background-color: #fce7f3; }
</style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto bg-white rounded-lg shadow p-6">
    <form action="{{ route('admin.appointments.store') }}" method="POST">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Left Column: Customer & Service -->
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nombre del Cliente</label>
                    <input type="text" name="customer_name" required class="w-full rounded-md border-gray-300 shadow-sm focus:ring-pink-500 focus:border-pink-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Teléfono</label>
                    <input type="tel" id="customer_phone" name="customer_phone" required class="w-full rounded-md border-gray-300 shadow-sm focus:ring-pink-500 focus:border-pink-500">
                    <input type="hidden" id="customer_phone_full" name="customer_phone_full">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Servicio</label>
                    <select name="service_id" id="service_selector" required class="w-full rounded-md border-gray-300 shadow-sm focus:ring-pink-500 focus:border-pink-500">
                        @foreach($services as $service)
                            <option value="{{ $service->id }}">{{ $service->name }} (${{ number_format($service->price, 0) }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Ubicación</label>
                    <select name="location" class="w-full rounded-md border-gray-300 shadow-sm focus:ring-pink-500 focus:border-pink-500">
                        <option value="salon">En el Salón</option>
                        <option value="home">A Domicilio</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Notas</label>
                    <textarea name="notes" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:ring-pink-500 focus:border-pink-500"></textarea>
                </div>
            </div>

            <!-- Right Column: Date & Time -->
            <div class="space-y-4">
                <label class="block text-sm font-medium text-gray-700">Seleccionar Día y Hora</label>
                <div id="inline-calendar"></div>
                <input type="hidden" name="appointment_date" id="appointment_date_raw">
                
                <div id="time_selection" class="hidden">
                    <label class="text-xs font-bold text-gray-500 uppercase">Horas Disponibles</label>
                    <div id="slots_container" class="grid grid-cols-4 gap-2 mt-2"></div>
                </div>
            </div>
        </div>

        <div class="mt-8 flex justify-end">
            <button type="submit" class="bg-pink-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-pink-700 transition">Agendar Cita</button>
        </div>
    </form>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.12/build/js/intlTelInput.min.js"></script>
<script>
    let selectedDate = null;
    
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
        
        // Actualizar el campo oculto con el número completo
        phoneInput.addEventListener('blur', function() {
            phoneInputFull.value = iti.getNumber();
        });
        
        // Validar antes de enviar
        const form = phoneInput.closest('form');
        form.addEventListener('submit', function(e) {
            phoneInputFull.value = iti.getNumber();
            
            if (!iti.isValidNumber()) {
                e.preventDefault();
                alert('Por favor ingresa un número de teléfono válido para el país seleccionado.');
                return false;
            }
        });
        
        flatpickr("#inline-calendar", {
            inline: true,
            locale: "es",
            minDate: "today",
            dateFormat: "Y-m-d",
            onChange: function(selectedDates, dateStr) {
                selectedDate = dateStr;
                fetchBusySlots(dateStr);
            }
        });
    });

    function formatTo12h(timeStr) {
        if (!timeStr) return '';
        const [h, m] = timeStr.split(':');
        let hh = parseInt(h);
        const ampm = hh >= 12 ? ' PM' : ' AM';
        hh = hh % 12;
        if (hh === 0) hh = 12;
        return `${hh}:${m}${ampm}`;
    }

    async function fetchBusySlots(date) {
        const container = document.getElementById('slots_container');
        const timeDiv = document.getElementById('time_selection');
        
        container.innerHTML = '<div class="col-span-full py-4 text-center text-pink-300">Cargando horarios...</div>';
        timeDiv.classList.remove('hidden');
        
        // Remove previous custom message if exists
        const prevCustomMsg = document.getElementById('custom_availability_msg');
        if (prevCustomMsg) prevCustomMsg.remove();

        try {
            const resp = await fetch(`{{ url('api/bot/busy-slots') }}?date=${date}`);
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
                msgDiv.className = 'col-span-full mb-3 p-3 bg-blue-50 text-blue-700 text-xs rounded-lg border border-blue-200 font-medium';
                msgDiv.innerText = customMessage;
                container.parentElement.insertBefore(msgDiv, container);
            }

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
            const todayStr = new Date().toISOString().split('T')[0];
            const currentH = now.getHours();
            const currentM = now.getMinutes();
            const currentTimeVal = currentH * 60 + currentM;

            possibleSlots.forEach(time => {
                const [h, m] = time.split(':').map(Number);
                const slotTimeVal = h * 60 + m;

                if (date === todayStr && slotTimeVal <= currentTimeVal) return;

                const isBusy = busySlots.some(busy => time >= busy.start && time < busy.end);
                
                availableCount++;
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.innerText = formatTo12h(time);
                btn.disabled = isBusy;
                
                if (isBusy) {
                    btn.className = "py-2 px-1 border-2 border-gray-100 rounded-lg text-xs font-semibold text-gray-300 cursor-not-allowed bg-gray-50";
                } else {
                    btn.className = "py-2 px-1 border-2 border-pink-100 rounded-lg text-xs font-semibold text-gray-700 hover:border-pink-500 hover:bg-pink-50 transition-all";
                }
                
                btn.onclick = () => {
                    if (isBusy) return;
                    document.querySelectorAll('#slots_container button').forEach(b => {
                        if (!b.disabled) b.className = "py-2 px-1 border-2 border-pink-100 rounded-lg text-xs font-semibold text-gray-700 hover:border-pink-500 hover:bg-pink-50 transition-all";
                    });
                    btn.className = "py-2 px-1 border-2 border-pink-600 bg-pink-600 text-white rounded-lg text-xs font-bold shadow-md transform scale-105 transition-all";
                    document.getElementById('appointment_date_raw').value = `${date} ${time}`;
                };
                container.appendChild(btn);
            });

            if (availableCount === 0) {
                container.innerHTML = '<div class="col-span-full py-4 text-center text-red-500 text-xs">No hay horarios disponibles para este día.</div>';
            }
        } catch (e) {
            container.innerHTML = '<div class="col-span-full py-4 text-center text-red-500">Error cargando horarios</div>';
            console.error(e);
        }
    }
</script>
@endpush
@endsection
