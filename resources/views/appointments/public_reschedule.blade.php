@extends('layouts.public')

@section('title', 'Reprogramar Cita')

@section('header', 'Reprogramar Cita')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .flatpickr-calendar { background: #fff; border: 1px solid #fce7f3; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
    .flatpickr-day.selected { background: #ec4899 !important; border-color: #ec4899 !important; }
    .flatpickr-day:hover { background: #fdf2f8; }
</style>
@endpush

@section('content')
<div class="py-12 bg-gray-50 flex-grow">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-xl overflow-hidden">
            <div class="bg-pink-600 px-6 py-4">
                <h2 class="text-2xl font-bold text-white text-center">Reprogramar tu Cita</h2>
            </div>
            
            <div class="p-8">
                <div class="mb-8 bg-pink-50 p-4 rounded-lg border border-pink-100 italic text-gray-700">
                    <p class="font-bold text-pink-700 mb-1">Hola {{ $appointment->customer_name }},</p>
                    <p>Estás reprogramando tu cita para <strong>{{ $appointment->service->name }}</strong>.</p>
                    <p class="text-xs mt-2 text-gray-500">Fecha actual: {{ $appointment->appointment_date->format('d/m/Y h:i A') }}</p>
                </div>

                <form action="{{ route('public.appointments.updateReschedule', $appointment->reschedule_token) }}" method="POST">
                    @csrf
                    
                    <div class="space-y-6">
                        <!-- Fecha y Hora Moderno -->
                        <div class="space-y-4">
                            <label class="block text-gray-700 font-bold mb-2">1. Selecciona el Nuevo Día</label>
                            <div class="flex flex-col md:flex-row gap-6">
                                <div id="inline-calendar" class="shadow-sm border border-pink-100 rounded-lg overflow-hidden mx-auto md:mx-0"></div>
                                <div id="time_selection" class="flex-1 hidden">
                                    <label class="block text-sm font-semibold text-gray-600 mb-1 uppercase tracking-wider">2. Selecciona tu Nueva Hora</label>
                                    <p class="text-xs text-pink-600 font-bold mb-3"><i class="fas fa-clock mr-1"></i> Horario disponible</p>
                                    <div id="slots_container" class="grid grid-cols-4 sm:grid-cols-5 gap-2">
                                        <!-- slots dynamically injected -->
                                    </div>
                                    <p id="no_slots_msg" class="text-xs text-red-500 hidden mt-2">No hay horarios disponibles para este día.</p>
                                </div>
                            </div>
                            <input type="hidden" name="appointment_date" id="appointment_date_raw" required>
                        </div>

                        <!-- Motivo -->
                        <div>
                            <label class="block text-gray-700 font-bold mb-2 cursor-pointer" for="reschedule_reason">
                                Motivo del Cambio (Opcional)
                            </label>
                            <textarea class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent" 
                                      id="reschedule_reason" name="reschedule_reason" rows="2" placeholder="Ej: Me surgió un inconveniente..."></textarea>
                        </div>

                        <div class="flex items-center justify-center pt-4">
                            <button class="bg-pink-600 hover:bg-pink-700 text-white font-bold py-3 px-8 rounded-full focus:outline-none focus:shadow-outline transition duration-300 transform hover:scale-105" 
                                    type="submit">
                                Confirmar Reprogramación
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let selectedDate = null;

        // INITIALIZE FLATPICKR
        const fp = flatpickr("#inline-calendar", {
            inline: true,
            locale: "es",
            minDate: "today",
            dateFormat: "Y-m-d",
            onChange: function(selectedDates, dateStr) {
                selectedDate = dateStr;
                document.getElementById('appointment_date_raw').value = '';
                fetchBusySlots(dateStr);
            }
        });

        async function fetchBusySlots(date) {
            const container = document.getElementById('slots_container');
            const msg = document.getElementById('no_slots_msg');
            const timeDiv = document.getElementById('time_selection');
            
            container.innerHTML = '<div class="col-span-full py-4 text-center text-pink-300"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>';
            timeDiv.classList.remove('hidden');
            msg.classList.add('hidden');
            
            const prevCustomMsg = document.getElementById('custom_availability_msg');
            if (prevCustomMsg) prevCustomMsg.remove();

            try {
                const resp = await fetch(`{{ url('api/bot/busy-slots') }}?date=${date}`);
                const data = await resp.json();
                
                let busySlots = data.busy || [];
                let workingHours = data.working_hours;
                let customMessage = data.message;

                if (customMessage) {
                    const msgDiv = document.createElement('div');
                    msgDiv.id = 'custom_availability_msg';
                    msgDiv.className = 'col-span-full mb-3 p-3 bg-blue-50 text-blue-700 text-sm rounded-lg border border-blue-200 font-medium flex items-center';
                    msgDiv.innerHTML = `<i class="fas fa-info-circle mr-2"></i> ${customMessage}`;
                    container.parentElement.insertBefore(msgDiv, container);
                }

                generateSlots(busySlots, workingHours);
            } catch (e) {
                container.innerHTML = '<div class="col-span-full py-4 text-center text-red-500 font-bold">Error</div>';
            }
        }

        function formatTo12h(timeStr) {
            const [h, m] = timeStr.split(':');
            let hh = parseInt(h);
            const ampm = hh >= 12 ? ' PM' : ' AM';
            hh = hh % 12 || 12;
            return `${hh}:${m}${ampm}`;
        }

        function generateSlots(busySlots, workingHours = null) {
            const container = document.getElementById('slots_container');
            const msg = document.getElementById('no_slots_msg');
            container.innerHTML = '';
            
            let possibleSlots = workingHours && workingHours.length > 0 ? workingHours : [];
            if (possibleSlots.length === 0) {
                for (let h = 8; h <= 21; h++) {
                    possibleSlots.push(`${h.toString().padStart(2, '0')}:00`);
                    possibleSlots.push(`${h.toString().padStart(2, '0')}:30`);
                }
            }

            let availableCount = 0;
            const now = new Date();
            const todayStr = now.toISOString().split('T')[0];
            const currentTimeVal = now.getHours() * 60 + now.getMinutes();

            possibleSlots.forEach(time => {
                const [h, m] = time.split(':').map(Number);
                const slotTimeVal = h * 60 + m;
                
                if (selectedDate === todayStr && slotTimeVal <= currentTimeVal) return;

                let isBusy = busySlots.some(b => {
                    const [bh1, bm1] = b.start.split(':').map(Number);
                    const [bh2, bm2] = b.end.split(':').map(Number);
                    return slotTimeVal >= (bh1 * 60 + bm1) && slotTimeVal < (bh2 * 60 + bm2);
                });

                if (!isBusy) {
                    availableCount++;
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.innerText = formatTo12h(time);
                    btn.className = "py-2 border-2 border-pink-50 rounded-lg text-sm font-semibold text-gray-700 hover:border-pink-500 hover:bg-pink-50 transition-all";
                    btn.onclick = () => {
                        document.querySelectorAll('#slots_container button').forEach(b => b.className = "py-2 border-2 border-pink-50 rounded-lg text-sm font-semibold text-gray-700 hover:border-pink-500 hover:bg-pink-50 transition-all");
                        btn.className = "py-2 border-2 border-pink-600 bg-pink-600 text-white rounded-lg text-sm font-bold shadow-md transform scale-105 transition-all";
                        document.getElementById('appointment_date_raw').value = `${selectedDate} ${time}`;
                    };
                    container.appendChild(btn);
                }
            });

            if (availableCount === 0) msg.classList.remove('hidden');
            else msg.classList.add('hidden');
        }
    });
</script>
@endpush
@endsection
