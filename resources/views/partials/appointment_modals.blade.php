<!-- resources/views/partials/appointment_modals.blade.php -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.12/build/css/intlTelInput.css">
<style>
    .iti { width: 100%; }
    .iti__flag-container { border-right: 1px solid #e5e7eb; }
    .iti__selected-flag { padding: 0 8px 0 12px; }
    .iti__country-list { box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); border: 1px solid #fce7f3; z-index: 100 !important; }
</style>


<!-- Main Appointment Detail Modal -->
<div id="appointment-modal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true" onclick="closeAppointmentModal()">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="flex justify-between items-start border-b pb-4 mb-4">
                    <h3 class="text-xl font-bold text-gray-900" id="modal-title">Detalle de la Cita</h3>
                    <button onclick="closeAppointmentModal()" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-gray-500 uppercase font-bold tracking-wider">Cliente</p>
                            <p id="modal-customer" class="text-lg font-bold text-gray-800"></p>
                            <p id="modal-phone" class="text-sm text-gray-600"></p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-gray-500 uppercase font-bold tracking-wider">Estado</p>
                            <span id="modal-status" class="px-3 py-1 rounded-full text-xs font-bold inline-block mt-1"></span>
                        </div>
                    </div>
                    <div class="border-t pt-4">
                        <p class="text-xs text-gray-500 uppercase font-bold tracking-wider">Servicio & Precio</p>
                        <p id="modal-service" class="text-lg font-bold text-pink-600"></p>
                        <p id="modal-price" class="text-lg font-bold text-green-600"></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-bold tracking-wider">Fecha y Hora</p>
                        <p id="modal-date" class="text-lg font-medium text-gray-800"></p>
                    </div>
                    <div id="modal-image-container" class="hidden">
                        <p class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-2">Diseño de Referencia</p>
                        <img id="modal-image" src="" alt="Referencia" class="w-full h-48 object-cover rounded-lg border">
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-bold tracking-wider">Notas</p>
                        <p id="modal-notes" class="text-sm text-gray-700 bg-gray-50 p-2 rounded italic font-serif leading-relaxed"></p>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 flex flex-wrap gap-2 justify-center border-t">
                <button id="btn-confirm" onclick="handleAction('confirmar')" class="flex-1 min-w-[120px] bg-green-600 text-white rounded px-4 py-2 font-bold hover:bg-green-700 transition shadow-sm hidden">
                    Confirmar
                </button>
                <button id="btn-complete" onclick="handleAction('completar')" class="flex-1 min-w-[120px] bg-blue-600 text-white rounded px-4 py-2 font-bold hover:bg-blue-700 transition shadow-sm">
                    Completar
                </button>
                <button id="btn-cancel" onclick="handleAction('cancelar')" class="flex-1 min-w-[120px] bg-red-100 text-red-700 rounded px-4 py-2 font-bold hover:bg-red-200 transition">
                    Cancelar
                </button>
                <button id="btn-reschedule-link" onclick="openRescheduleModal(currentGlobalApp)" class="flex-1 min-w-[120px] bg-yellow-400 text-white text-center rounded px-4 py-2 font-bold hover:bg-yellow-500 transition shadow-sm">
                    Reprogramar
                </button>
                <button id="btn-delete" onclick="handleAction('eliminar')" class="flex-1 min-w-[120px] border border-red-300 text-red-600 rounded px-4 py-2 font-bold hover:bg-red-50 transition">
                    Eliminar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Specialized Conflict Reschedule Modal -->
<div id="reschedule-modal" class="fixed inset-0 z-[60] hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true" onclick="closeRescheduleModal()">
            <div class="absolute inset-0 bg-gray-900 opacity-80 backdrop-blur-sm"></div>
        </div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full border-2 border-yellow-400">
            <div class="bg-yellow-50 px-6 py-4 border-b border-yellow-100 flex justify-between items-center">
                <h3 class="text-lg font-bold text-yellow-800 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0L2.407 15.021c-.38 1.56 1.16 2.518 2.49 1.56L10 13.5l5.103 3.081c1.33.803 2.87-.155 2.49-1.56L11.49 3.17z" clip-rule="evenodd" />
                    </svg>
                    Reprogramar Solicitud
                </h3>
                <button onclick="closeRescheduleModal()" class="text-yellow-600 hover:text-yellow-800">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form id="reschedule-form" method="POST">
                @csrf
                <input type="hidden" name="status" value="confirmed">
                <div class="p-6 space-y-4">
                    <p id="reschedule-modal-msg" class="text-sm text-gray-600 italic bg-yellow-50 p-3 rounded border-l-4 border-yellow-400">Reprograma la cita eligiendo un nuevo horario y explica el motivo al cliente.</p>
                    
                    <!-- Calendar and Time Selection -->
                    <div class="space-y-4">
                        <label class="block text-sm font-bold text-gray-700">1. Selecciona el Día</label>
                        <div class="flex flex-col lg:flex-row gap-4">
                            <div id="reschedule-inline-calendar" class="shadow-sm border border-yellow-100 rounded-lg overflow-hidden flex-shrink-0"></div>
                            <div id="reschedule_time_selection" class="flex-1 hidden">
                                <label class="block text-xs font-semibold text-gray-600 mb-2 uppercase tracking-wider">2. Selecciona la Hora</label>
                                <div id="reschedule_slots_container" class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-2 max-h-80 overflow-y-auto pr-2">
                                    <!-- slots dynamically injected -->
                                </div>
                                <p id="reschedule_no_slots_msg" class="text-xs text-red-500 hidden mt-2">No hay horarios disponibles para este día.</p>
                            </div>
                        </div>
                        <input type="hidden" name="appointment_date" id="reschedule_date_raw" required>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Motivo / Mensaje</label>
                        <textarea name="reason_msg" required rows="3" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-yellow-500 focus:outline-none" placeholder="Ej: Ya tengo una cita a esa hora, te propongo este nuevo horario..."></textarea>
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-4 flex gap-3 border-t">
                    <button type="button" onclick="closeRescheduleModal()" class="flex-1 px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition font-bold">
                        Cancelar
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition font-bold shadow-md">
                        Confirmar Nueva Hora
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Create Appointment Modal -->
<div id="create-modal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true" onclick="closeCreateModal()">
            <div class="absolute inset-0 bg-gray-900 opacity-80 backdrop-blur-sm"></div>
        </div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            <div class="bg-pink-50 px-6 py-4 border-b border-pink-100 flex justify-between items-center">
                <h3 class="text-lg font-bold text-pink-800 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Agendar Nueva Cita
                </h3>
                <button onclick="closeCreateModal()" class="text-pink-600 hover:text-pink-800">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form id="create-appointment-form" action="{{ route('admin.appointments.store') }}" method="POST">
                @csrf
                <div class="p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Customer & Service -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700">Nombre del Cliente</label>
                                <input type="text" name="customer_name" required class="w-full mt-1 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-pink-500">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700">Teléfono</label>
                                <input type="tel" id="create_customer_phone" name="customer_phone" required class="w-full mt-1 border rounded-lg focus:ring-2 focus:ring-pink-500">
                                <input type="hidden" id="create_customer_phone_full" name="customer_phone_full">
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700">Servicio</label>
                                    <select name="service_id" required class="w-full mt-1 px-3 py-2 border rounded-lg focus:ring-2 focus:ring-pink-500">
                                        @foreach($allServices ?? [] as $service)
                                            <option value="{{ $service->id }}">{{ $service->name }} (${{ number_format($service->price, 0) }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700">Ubicación</label>
                                    <select name="location" class="w-full mt-1 px-3 py-2 border rounded-lg focus:ring-2 focus:ring-pink-500">
                                        <option value="salon">En el Salón</option>
                                        <option value="home">A Domicilio</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700">Notas</label>
                                <textarea name="notes" rows="3" class="w-full mt-1 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-pink-500" placeholder="Ej: Trae diseño propio..."></textarea>
                            </div>
                        </div>

                        <!-- Date & Time -->
                        <div class="space-y-4">
                            <label class="block text-sm font-bold text-gray-700">Seleccionar Día y Hora</label>
                            <div id="create-inline-calendar" class="shadow-sm border rounded-lg overflow-hidden"></div>
                            <input type="hidden" name="appointment_date" id="create_date_raw" required>
                            
                            <div id="create_time_selection" class="hidden">
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Horas Disponibles</label>
                                <div id="create_slots_container" class="grid grid-cols-3 sm:grid-cols-4 gap-2 max-h-48 overflow-y-auto pr-2"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-4 flex gap-3 border-t">
                    <button type="button" onclick="closeCreateModal()" class="flex-1 px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg font-bold hover:bg-gray-100 transition">
                        Cancelar
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-pink-600 text-white rounded-lg font-bold hover:bg-pink-700 transition shadow-md">
                        Agendar Cita
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="global-action-form" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="status" id="global-action-status">
    <input type="hidden" name="reason" id="global-action-reason">
</form>

<form id="global-delete-form" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
let currentGlobalApp = null;
    let reschedulePicker = null;
    let createPicker = null;
    let itiInstance = null;
    let selectedRescheduleDate = null;
    let selectedRescheduleTime = null;
    let selectedCreateDate = null;

    document.addEventListener('DOMContentLoaded', function() {
        // We will init intl-tel-input globally for the body or just when needed
        // but since it's a modal, let's init it once for the create form
        const phoneInput = document.querySelector("#create_customer_phone");
        if(phoneInput && typeof window.intlTelInput !== 'undefined') {
             itiInstance = window.intlTelInput(phoneInput, {
                initialCountry: "co",
                preferredCountries: ["co", "us", "mx", "es", "ar", "ve"],
                utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.12/build/js/utils.js",
                separateDialCode: true,
                autoPlaceholder: "aggressive"
            });
            
            phoneInput.addEventListener('blur', () => {
                document.getElementById('create_customer_phone_full').value = itiInstance.getNumber();
            });
        }
        if(typeof flatpickr !== 'undefined') {
            reschedulePicker = flatpickr("#reschedule-inline-calendar", {
                inline: true,
                locale: "es",
                minDate: "today",
                dateFormat: "Y-m-d",
                onChange: function(selectedDates, dateStr) {
                    selectedRescheduleDate = dateStr;
                    selectedRescheduleTime = null;
                    document.getElementById('reschedule_date_raw').value = '';
                    fetchRescheduleBusySlots(dateStr);
                }
            });
        }
    });

    async function fetchRescheduleBusySlots(date) {
        const timeDiv = document.getElementById('reschedule_time_selection');
        const container = document.getElementById('reschedule_slots_container');
        const msg = document.getElementById('reschedule_no_slots_msg');
        
        container.innerHTML = '<div class="col-span-full py-4 text-center text-yellow-300">Cargando...</div>';
        timeDiv.classList.remove('hidden');
        msg.classList.add('hidden');

        try {
            const resp = await fetch(`{{ url('api/bot/busy-slots') }}?date=${date}`);
            if (!resp.ok) throw new Error('Error en el servidor');
            const busySlots = await resp.json();
            generateRescheduleSlots(busySlots);
        } catch (e) {
            container.innerHTML = '<div class="col-span-full py-4 text-center text-red-400">Error cargando horarios.</div>';
            console.error(e);
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

    function generateRescheduleSlots(data) {
        const container = document.getElementById('reschedule_slots_container');
        const msg = document.getElementById('reschedule_no_slots_msg');
        container.innerHTML = '';
        
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
            msgDiv.className = 'col-span-full mb-3 p-3 bg-blue-50 text-blue-700 text-xs rounded-lg border border-blue-200 font-medium';
            msgDiv.innerText = customMessage;
            container.parentElement.insertBefore(msgDiv, container);
        }

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
        const todayStr = now.toISOString().split('T')[0];
        const currentH = now.getHours();
        const currentM = now.getMinutes();
        const currentTimeVal = currentH * 60 + currentM;

        possibleSlots.forEach(time => {
            const [h, m] = time.split(':').map(Number);
            const slotTimeVal = h * 60 + m;

            if (selectedRescheduleDate === todayStr && slotTimeVal <= currentTimeVal) return;

            const isBusy = busySlots.some(busy => time >= busy.start && time < busy.end);
            
            if (!isBusy) {
                availableCount++;
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.innerText = formatTo12h(time);
                btn.className = "py-2 border-2 border-yellow-100 rounded-lg text-sm font-semibold text-gray-700 hover:border-yellow-500 hover:bg-yellow-50 transition-all";
                btn.onclick = () => {
                    selectedRescheduleTime = time;
                    document.querySelectorAll('#reschedule_slots_container button').forEach(b => b.className = "py-2 border-2 border-yellow-100 rounded-lg text-sm font-semibold text-gray-700 hover:border-yellow-500 hover:bg-yellow-50 transition-all");
                    btn.className = "py-2 border-2 border-yellow-500 bg-yellow-500 text-white rounded-lg text-sm font-bold shadow-md transform scale-105 transition-all";
                    document.getElementById('reschedule_date_raw').value = `${selectedRescheduleDate} ${time}`;
                };
                container.appendChild(btn);
            }
        });

        if (availableCount === 0) {
            msg.classList.remove('hidden');
            container.innerHTML = '';
        } else {
            msg.classList.add('hidden');
        }
    }

    function openCreateModal() {
        document.getElementById('create-modal').classList.remove('hidden');
        if(!createPicker && typeof flatpickr !== 'undefined') {
            createPicker = flatpickr("#create-inline-calendar", {
                inline: true,
                locale: "es",
                minDate: "today",
                dateFormat: "Y-m-d",
                onChange: function(selectedDates, dateStr) {
                    selectedCreateDate = dateStr;
                    fetchCreateBusySlots(dateStr);
                }
            });
        }
    }

    function closeCreateModal() {
        document.getElementById('create-modal').classList.add('hidden');
    }

    async function fetchCreateBusySlots(date) {
        const container = document.getElementById('create_slots_container');
        const timeDiv = document.getElementById('create_time_selection');
        container.innerHTML = '<div class="col-span-full py-4 text-center text-pink-300">...</div>';
        timeDiv.classList.remove('hidden');

        try {
            const resp = await fetch(`{{ url('api/bot/busy-slots') }}?date=${date}`);
            const data = await resp.json();
            generateCreateSlots(data);
        } catch (e) {
            container.innerHTML = 'Error';
        }
    }

    function generateCreateSlots(data) {
        const container = document.getElementById('create_slots_container');
        container.innerHTML = '';
        
        let busySlots = data.busy || [];
        let workingHours = data.working_hours;

        let possibleSlots = workingHours && workingHours.length > 0 ? workingHours : [];
        if (possibleSlots.length === 0) {
            for (let h = 8; h <= 21; h++) {
                possibleSlots.push(`${h.toString().padStart(2, '0')}:00`);
                possibleSlots.push(`${h.toString().padStart(2, '0')}:30`);
            }
        }

        const now = new Date();
        const todayStr = now.toISOString().split('T')[0];
        const currentTimeVal = now.getHours() * 60 + now.getMinutes();

        possibleSlots.forEach(time => {
            const [h, m] = time.split(':').map(Number);
            const slotTimeVal = h * 60 + m;
            if (selectedCreateDate === todayStr && slotTimeVal <= currentTimeVal) return;

            const isBusy = busySlots.some(busy => time >= busy.start && time < busy.end);
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.innerText = formatTo12h(time);
            btn.disabled = isBusy;
            
            if (isBusy) {
                btn.className = "py-2 border-2 border-gray-100 rounded-lg text-xs font-semibold text-gray-300 cursor-not-allowed bg-gray-50";
            } else {
                btn.className = "py-2 border-2 border-pink-100 rounded-lg text-xs font-semibold text-gray-700 hover:border-pink-500 hover:bg-pink-50 transition-all";
            }
            
            btn.onclick = () => {
                if (isBusy) return;
                document.querySelectorAll('#create_slots_container button').forEach(b => {
                    if (!b.disabled) b.className = "py-2 border-2 border-pink-100 rounded-lg text-xs font-semibold text-gray-700 hover:border-pink-500 hover:bg-pink-50 transition-all";
                });
                btn.className = "py-2 border-2 border-pink-600 bg-pink-600 text-white rounded-lg text-xs font-bold shadow-md transform scale-105 transition-all";
                document.getElementById('create_date_raw').value = `${selectedCreateDate} ${time}`;
            };
            container.appendChild(btn);
        });
    }

    function openAppointmentModal(data) {
        currentGlobalApp = data;
        
        document.getElementById('modal-customer').innerText = data.customer_name;
        document.getElementById('modal-phone').innerText = data.customer_phone;
        document.getElementById('modal-service').innerText = data.service_name;
        document.getElementById('modal-date').innerText = data.date;
        document.getElementById('modal-price').innerText = '$' + new Intl.NumberFormat().format(data.price);
        document.getElementById('modal-notes').innerText = data.notes || 'Sin notas adicionales';
        
        const statusEl = document.getElementById('modal-status');
        const statusMap = {
            'pending': { label: 'Pendiente', class: 'bg-yellow-100 text-yellow-800' },
            'confirmed': { label: 'Confirmada', class: 'bg-green-100 text-green-800' },
            'completed': { label: 'Completada', class: 'bg-blue-100 text-blue-800' },
            'cancelled': { label: 'Cancelada', class: 'bg-red-100 text-red-800' }
        };
        const s = statusMap[data.status] || { label: data.status, class: 'bg-gray-100' };
        statusEl.innerText = s.label;
        statusEl.className = `px-3 py-1 rounded-full text-xs font-bold inline-block mt-1 ${s.class}`;

        const imgContainer = document.getElementById('modal-image-container');
        if (data.image) {
            document.getElementById('modal-image').src = data.image;
            imgContainer.classList.remove('hidden');
        } else {
            imgContainer.classList.add('hidden');
        }

        document.getElementById('btn-reschedule-link').href = data.edit_url;
        document.getElementById('btn-reschedule-link').classList.toggle('hidden', data.status === 'completed' || data.status === 'cancelled');
        
        // Hide/Show complete button based on status (Cannot complete if pending, completed or cancelled)
        document.getElementById('btn-complete').classList.toggle('hidden', data.status === 'completed' || data.status === 'cancelled' || data.status === 'pending');
        
        // Hide/Show cancel button based on status (Cannot cancel if cancelled or completed, but CAN cancel if confirmed or pending)
        document.getElementById('btn-cancel').classList.toggle('hidden', data.status === 'cancelled' || data.status === 'completed');
        
        // Confirm button (only for pending)
        const btnConf = document.getElementById('btn-confirm');
        if(data.status === 'pending') {
            btnConf.classList.remove('hidden');
        } else {
            btnConf.classList.add('hidden');
        }

        document.getElementById('appointment-modal').classList.remove('hidden');
    }

    function closeAppointmentModal() {
        document.getElementById('appointment-modal').classList.add('hidden');
    }

    function openRescheduleModal(data) {
        console.log('Opening reschedule modal with data:', data);
        currentGlobalApp = data;
        const form = document.getElementById('reschedule-form');
        // Always use status_url for rescheduling from modal, as it expects POST and 'status'
        form.action = data.status_url;
        
        const msgEl = document.getElementById('reschedule-modal-msg');
        if (msgEl) {
            if (window.conflictDetected) {
                msgEl.innerText = "Este horario está ocupado. Elige uno nuevo y explica el motivo al cliente.";
                window.conflictDetected = false; // Reset for next time
            } else {
                msgEl.innerText = "Reprograma la cita eligiendo un nuevo horario y explica el motivo al cliente.";
            }
        }

        // Clear previous selections
        selectedRescheduleDate = null;
        selectedRescheduleTime = null;
        document.getElementById('reschedule_date_raw').value = '';
        const timeDiv = document.getElementById('reschedule_time_selection');
        if(timeDiv) timeDiv.classList.add('hidden');
        
        document.getElementById('reschedule-modal').classList.remove('hidden');
    }

    function closeRescheduleModal() {
        document.getElementById('reschedule-modal').classList.add('hidden');
    }

    function handleAction(action) {
        if (!currentGlobalApp) return;

        let title, text, icon, confirmText, color;

        if (action === 'confirmar') {
            title = '¿Confirmar esta cita?';
            text = 'El cliente recibirá un mensaje de confirmación.';
            icon = 'question';
            confirmText = 'Sí, Confirmar';
            color = '#16a34a';
        } else if (action === 'completar') {
            title = '¿Cita completada?';
            text = 'El servicio ha sido realizado.';
            icon = 'question';
            confirmText = 'Sí, Completar';
            color = '#2563eb';
        } else if (action === 'cancelar') {
            promptCancellation();
            return;
        } else if (action === 'eliminar') {
            title = '¿Eliminar permanentemente?';
            text = 'Esta acción no se puede deshacer.';
            icon = 'error';
            confirmText = 'Sí, Eliminar';
            color = '#dc2626';
        }

        Swal.fire({
            title: title, text: text, icon: icon, showCancelButton: true,
            confirmButtonColor: color, cancelButtonColor: '#6b7280',
            confirmButtonText: confirmText, cancelButtonText: 'No, volver'
        }).then((result) => {
            if (result.isConfirmed) {
                if (action === 'eliminar') {
                    const form = document.getElementById('global-delete-form');
                    form.action = currentGlobalApp.delete_url;
                    form.submit();
                } else {
                    const form = document.getElementById('global-action-form');
                    form.action = currentGlobalApp.status_url;
                    document.getElementById('global-action-status').value = 
                        (action === 'completar' ? 'completed' : (action === 'confirmar' ? 'confirmed' : 'pending'));
                    form.submit();
                }
            }
        });
    }

    function promptCancellation() {
        Swal.fire({
            title: 'Motivo de la cancelación',
            input: 'textarea',
            inputPlaceholder: 'Escribe por qué se rechaza la cita...',
            showCancelButton: true,
            confirmButtonText: 'Confirmar Rechazo',
            confirmButtonColor: '#ef4444',
            cancelButtonText: 'Volver',
            inputValidator: (value) => { if (!value) return '¡Debes proporcionar un motivo!'; }
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('global-action-form');
                form.action = currentGlobalApp.status_url;
                document.getElementById('global-action-status').value = 'cancelled';
                document.getElementById('global-action-reason').value = result.value;
                form.submit();
            }
        });
    }

    // Auto-reopen logic
    window.addEventListener('reopen-appointment-modal', function() {
        const data = @json(session('open_appointment_modal_data'));
        if(!data) return;

        // SI ES UN CHOQUE (CONFLICTO), ABRIMOS EL DE REPROGRAMAR DIRECTAMENTE
        @if(session('conflict_detected'))
             window.conflictDetected = true;
             // Adapt context for reschedule form
             data.update_url = data.status_url; 
             openRescheduleModal(data);
        @else
             openAppointmentModal(data);
        @endif
    });
</script>
