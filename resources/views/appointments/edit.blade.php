@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endpush

@section('header', 'Reprogramar / Editar Cita')

@section('content')
<div class="bg-white rounded-lg shadow p-6 max-w-4xl mx-auto">
    <form action="{{ route('admin.appointments.update', $appointment) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-6 border-b pb-4">
            <h2 class="text-xl font-bold text-gray-800">Cita #{{ $appointment->id }} - {{ $appointment->customer_name }}</h2>
            <p class="text-sm text-gray-500 italic">Fecha actual: {{ $appointment->appointment_date->format('d/m/Y h:i A') }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Left Side: Calendar and Slots -->
            <div class="space-y-4">
                <label class="block text-sm font-bold text-gray-700">Selecciona Nueva Fecha y Hora</label>
                <div id="inline-calendar"></div>
                <input type="hidden" name="appointment_date" id="appointment_date_raw" value="{{ $appointment->appointment_date->format('Y-m-d H:i') }}" required>
                
                <div id="time_selection" class="hidden">
                    <label class="text-xs font-bold text-gray-500 uppercase tracking-widest">Horas Disponibles</label>
                    <div id="slots_container" class="grid grid-cols-3 sm:grid-cols-4 gap-2 mt-2"></div>
                </div>
            </div>

            <!-- Right Side: Details -->
            <div class="space-y-4">
                <!-- Reason -->
                <div>
                     <label for="reason_msg" class="block text-sm font-bold text-gray-700 mb-1">Motivo de Reprogramación</label>
                     <textarea name="reason_msg" id="reason_msg" rows="3" placeholder="Ej: Cliente solicitó cambio de hora..." class="w-full rounded-md border-gray-300 shadow-sm focus:ring-pink-500 focus:border-pink-500"></textarea>
                </div>
                
                <!-- Notes -->
                 <div>
                    <label for="notes" class="block text-sm font-bold text-gray-700 mb-1">Notas de la Cita</label>
                    <textarea name="notes" id="notes" rows="4" class="w-full rounded-md border-gray-300 shadow-sm focus:ring-pink-500 focus:border-pink-500">{{ old('notes', $appointment->notes) }}</textarea>
                </div>
            </div>
        </div>

        <div class="mt-8 pt-6 border-t flex justify-end space-x-3">
            <a href="{{ route('admin.appointments.index') }}" class="px-6 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition font-bold">Cancelar</a>
            <button type="submit" class="px-6 py-2 bg-pink-600 text-white rounded-lg hover:bg-pink-700 transition font-bold shadow-md">Guardar Nueva Fecha</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
<script>
    let selectedDate = "{{ $appointment->appointment_date->format('Y-m-d') }}";
    let initialTime = "{{ $appointment->appointment_date->format('H:i') }}";

    document.addEventListener('DOMContentLoaded', function() {
        flatpickr("#inline-calendar", {
            inline: true,
            locale: "es",
            minDate: "today",
            defaultDate: selectedDate,
            dateFormat: "Y-m-d",
            onChange: function(selectedDates, dateStr) {
                selectedDate = dateStr;
                fetchBusySlots(dateStr);
            }
        });
        
        // Carga inicial
        fetchBusySlots(selectedDate);
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
        container.innerHTML = '<div class="col-span-full text-pink-300 text-xs">Cargando...</div>';
        timeDiv.classList.remove('hidden');

        try {
            const resp = await fetch(`{{ url('api/bot/busy-slots') }}?date=${date}`);
            const data = await resp.json();
            
            let busySlots = data.busy || [];
            let workingHours = data.working_hours;

            container.innerHTML = '';
            let possibleSlots = workingHours || [];
            if (possibleSlots.length === 0) {
                 for (let h = 8; h <= 21; h++) {
                    possibleSlots.push(`${h.toString().padStart(2, '0')}:00`);
                    possibleSlots.push(`${h.toString().padStart(2, '0')}:30`);
                }
            }

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
                
                if (date === todayStr && slotTimeVal <= currentTimeVal) return;

                const isBusy = busySlots.some(busy => time >= busy.start && time < busy.end);
                
                // EXCEPT: if it's the current appointment's time, it shouldn't be "busy" for editing it
                const isCurrentTime = (date === "{{ $appointment->appointment_date->format('Y-m-d') }}" && time === initialTime);
                
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.innerText = formatTo12h(time);
                
                if (isBusy && !isCurrentTime) {
                    btn.disabled = true;
                    btn.className = "py-2 border-2 border-gray-100 rounded-lg text-xs font-semibold text-gray-300 cursor-not-allowed bg-gray-50";
                } else {
                    btn.className = "py-2 border-2 border-pink-100 rounded-lg text-xs font-semibold text-gray-700 hover:border-pink-500 hover:bg-pink-50 transition-all";
                    if (isCurrentTime) {
                        btn.className = "py-2 border-2 border-pink-600 bg-pink-600 text-white rounded-lg text-xs font-bold shadow-md transform scale-105";
                    }
                }
                
                btn.onclick = () => {
                    if (btn.disabled) return;
                    document.querySelectorAll('#slots_container button').forEach(b => {
                        if (!b.disabled) b.className = "py-2 border-2 border-pink-100 rounded-lg text-xs font-semibold text-gray-700 hover:border-pink-500 hover:bg-pink-50 transition-all";
                    });
                    btn.className = "py-2 border-2 border-pink-600 bg-pink-600 text-white rounded-lg text-xs font-bold shadow-md transform scale-105 transition-all";
                    document.getElementById('appointment_date_raw').value = `${date} ${time}`;
                };
                container.appendChild(btn);
            });
        } catch (e) {
            container.innerHTML = 'Error';
        }
    }
</script>
@endpush
