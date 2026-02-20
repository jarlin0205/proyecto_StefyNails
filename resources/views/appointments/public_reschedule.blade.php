@extends('layouts.public')

@section('title', 'Reprogramar Cita')

@section('header', 'Reprogramar Cita')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
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

    #time_placeholder_container {
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
                <h2 class="text-2xl font-bold text-white text-center">Reprogramar tu Cita</h2>
            </div>
            
            <div class="p-8">
                <div class="mb-8 bg-pink-50 p-4 rounded-lg border border-pink-100 italic text-gray-700">
                    <p class="font-bold text-pink-700 mb-1">Hola {{ $appointment->customer_name }},</p>
                    <p>Estás reprogramando tu cita para <strong>{{ $appointment->service->name }}</strong> con <strong>{{ $appointment->professional->name ?? 'nuestro personal' }}</strong>.</p>
                    <p class="text-xs mt-2 text-gray-500">Fecha actual: {{ $appointment->appointment_date->format('d/m/Y h:i A') }}</p>
                </div>

                <form action="{{ route('public.appointments.updateReschedule', $appointment->reschedule_token) }}" method="POST" id="rescheduleForm">
                    @csrf
                    
                    <div class="space-y-6">
                        <!-- Fecha y Hora Moderno -->
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Selector de Fecha -->
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 mb-1 ml-1 uppercase">Nuevo Día</label>
                                    <div class="relative group">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-300">
                                            <i class="far fa-calendar-alt"></i>
                                        </div>
                                        <input type="text" id="date_selector" readonly
                                               class="shadow-sm border border-gray-200 rounded-xl w-full py-4 pl-10 pr-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-pink-500 cursor-pointer bg-white transition-all font-semibold" 
                                               placeholder="Toca para elegir fecha...">
                                    </div>
                                </div>

                                <!-- Hora Seleccionada (Visual) -->
                                <div id="time_placeholder_container">
                                    <label class="block text-xs font-bold text-gray-500 mb-1 ml-1 uppercase">Hora Elegida</label>
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
                                    <span id="slots-count-badge" class="bg-pink-500 text-white text-[9px] px-2 py-0.5 rounded-full font-bold hidden"></span>
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

        // Validar antes de enviar el formulario
        const form = document.getElementById('rescheduleForm');
        form.addEventListener('submit', function(e) {
            // 1. Validar Fecha
            if (!selectedDate) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Selecciona una fecha',
                    text: 'Aun no has elegido el día para tu servicio.',
                    confirmButtonColor: '#ec4899'
                });
                document.getElementById('date_selector').scrollIntoView({ behavior: 'smooth', block: 'center' });
                return false;
            }

            // 2. Validar Hora
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
                    document.getElementById('time_display').classList.replace('text-pink-500', 'text-pink-600');
                    document.getElementById('time_display').classList.add('font-black');
                    document.getElementById('appointment_date_raw').value = `${selectedDate} ${time}`;
                };
                container.appendChild(btn);
            });
        }

        async function fetchBusySlots(date) {
            const container = document.getElementById('slots_container');
            const msg = document.getElementById('no_slots_msg');
            const inlineContainer = document.getElementById('inline-slots-container');
            const badge = document.getElementById('slots-count-badge');
            
            container.innerHTML = '<div class="col-span-full py-6 text-center text-pink-400 font-medium"><i class="fas fa-spinner fa-spin mr-2"></i> Buscando horarios...</div>';
            // inlineContainer.style.display = 'block';
            msg.classList.add('hidden');
            badge.classList.add('hidden');
            allAvailableSlots = [];
            
            const prevCustomMsg = document.getElementById('custom_availability_msg');
            if (prevCustomMsg) prevCustomMsg.remove();

            try {
                const professionalId = "{{ $appointment->professional_id }}";
                const resp = await fetch(`{{ url('api/bot/busy-slots') }}?date=${date}&professional_id=${professionalId}`);
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
            const badge = document.getElementById('slots-count-badge');
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
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const todayStr = `${year}-${month}-${day}`;
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
    });
</script>
@endpush
@endsection
