@extends('layouts.admin')

@section('content')
<div class="flex flex-col">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Gestionar Disponibilidad</h1>
            <p class="text-gray-600">Configura horarios especiales por día.</p>
        </div>
        @if(auth()->user()->isAdmin())
            <div class="flex items-center gap-3">
                <label class="text-sm font-bold text-gray-700">Profesional:</label>
                <form action="{{ route('admin.availability.index') }}" method="GET" id="prof-selector-form">
                    <select name="professional_id" onchange="this.form.submit()" class="border-gray-200 rounded-lg focus:ring-pink-500 focus:border-pink-500 text-sm font-semibold py-2">
                        @foreach($professionals as $p)
                            <option value="{{ $p->id }}" {{ $professionalId == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
        @endif
    </div>

    <!-- Main Content Wrapper -->
    <div class="flex flex-col md:flex-row flex-1 bg-white rounded-lg shadow min-h-[600px]">
        <!-- Calendar Sidebar -->
        <div class="w-full md:w-1/3 border-r bg-gray-50 p-6 flex flex-col items-center">
            <h3 class="font-bold text-gray-700 mb-4 self-start">Selecciona una Fecha</h3>
            <div id="availability-calendar" class="bg-white rounded-lg shadow mb-6 mx-auto"></div>
            
            <div class="w-full">
                <h4 class="font-bold text-gray-700 mb-3 border-b pb-1">Días Personalizados ({{ auth()->user()->isAdmin() ? ($professionals->find($professionalId)->name ?? 'Gral') : 'Mios' }})</h4>
                <div class="space-y-2">
                    @forelse($availabilities as $av)
                        @php $date = \Carbon\Carbon::parse($av->date); @endphp
                        <button onclick="jumpToDate('{{ $date->format('Y-m-d') }}')" class="w-full text-left bg-white p-3 rounded-lg border hover:border-pink-500 hover:shadow-sm transition flex justify-between items-center group">
                            <div>
                                <span class="font-bold text-gray-800 text-sm block">{{ $date->locale('es')->isoFormat('D MMM YYYY') }}</span>
                                <span class="text-xs text-gray-500 truncate max-w-[150px] block">{{ $av->message ?: 'Horario especial' }}</span>
                            </div>
                            <svg class="w-4 h-4 text-gray-300 group-hover:text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </button>
                    @empty
                        <p class="text-xs text-gray-400 text-center py-2">No configuraciones.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Slot Manager -->
        <div class="w-full md:w-2/3 p-6 flex flex-col min-h-[600px]">
            <div id="editor-panel" class="hidden flex flex-col">
                <div class="mb-4 pb-4 border-b flex-shrink-0">
                    <div class="flex justify-between items-start">
                        <div>
                            <h2 class="text-xl font-bold text-gray-800">Editando: <span id="selected-date-display" class="text-pink-600 capitalize"></span></h2>
                            <p class="text-sm text-gray-500">Selecciona las horas disponibles para {{ auth()->user()->isAdmin() ? ($professionals->find($professionalId)->name ?? 'este profesional') : 'este día' }}.</p>
                        </div>
                        <span class="bg-pink-50 text-pink-700 text-[10px] font-bold px-2 py-1 rounded uppercase tracking-wider border border-pink-100">
                            {{ auth()->user()->isAdmin() ? 'Modo Admin' : 'Mi Horario' }}
                        </span>
                    </div>
                </div>

                <div class="mb-4 flex-shrink-0">
                    <label class="block text-gray-700 font-bold mb-1">Mensaje para el Cliente (Opcional)</label>
                    <input type="text" id="day-message" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-pink-500 transition-all shadow-sm" placeholder="Ej: Solo turno tarde...">
                </div>

                <div class="flex-grow flex flex-col">
                    <div class="flex justify-between items-end mb-2 flex-shrink-0">
                        <label class="block text-gray-700 font-bold">Franjas Horarias</label>
                        <div class="text-xs space-x-2">
                            <span class="inline-block w-4 h-4 bg-gray-200 rounded align-middle border"></span> Cerrado
                            <span class="inline-block w-4 h-4 bg-pink-500 rounded align-middle border border-pink-600 shadow-sm"></span> Abierto
                        </div>
                    </div>
                    
                    <div id="slots-grid-container" class="border rounded-lg bg-gray-50/50 p-4 min-h-[300px]">
                        <div id="slots-grid" class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-3">
                            <!-- Slots generated by JS -->
                        </div>
                    </div>
                    
                    <div class="mt-8 flex gap-4 border-t pt-6 bg-white flex-shrink-0">
                        <button onclick="saveAvailability()" class="flex-1 bg-pink-600 text-white font-bold py-3 rounded-xl hover:bg-pink-700 transition shadow-lg flex justify-center items-center gap-2 transform hover:-translate-y-0.5">
                            <i class="fas fa-save w-5 h-5"></i>
                            Guardar Horarios
                        </button>
                        <button onclick="clearDay()" class="px-6 py-3 border border-red-200 text-red-600 rounded-xl hover:bg-red-50 font-semibold transition hover:border-red-300" title="Restablecer">
                            <i class="fas fa-undo mr-1"></i> Restablecer
                        </button>
                    </div>
                </div>
            </div>

            <div id="empty-state" class="flex flex-col items-center justify-center text-gray-400 flex-grow py-20">
                <i class="far fa-calendar-plus text-6xl mb-4 opacity-20"></i>
                <p class="text-lg font-medium">Selecciona una fecha en el calendario para configurar.</p>
                <p class="text-sm mt-2 opacity-60 italic text-center max-w-xs">Puedes habilitar o deshabilitar bloques de media hora para este día específico.</p>
            </div>
        </div>
    </div> <!-- End Wrapper -->
</div> <!-- End Main Container -->
    
@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/airbnb.css">
<style>
    .slot-btn { transition: all 0.2s; cursor: pointer; }
    .slot-btn.active { background-color: #ec4899; color: white; border-color: #db2777; box-shadow: 0 4px 6px -1px rgba(236, 72, 153, 0.4); transform: translateY(-1px); }
    .slot-btn.inactive { background-color: white; color: #9ca3af; border-color: #e5e7eb; }
    .slot-btn.inactive:hover { border-color: #ec4899; color: #ec4899; background: #fff1f2; }
    
    .flatpickr-day.has-config { border-bottom: 2px solid #ec4899 !important; font-weight: bold; }
    .flatpickr-day.has-config::after { content: ''; position: absolute; bottom: 2px; left: 50%; transform: translateX(-50%); width: 3px; height: 3px; background-color: #ec4899; border-radius: 50%; }
</style>
@endpush

@push('scripts')
<script>
    let selectedDate = null;
    let activeSlots = new Set();
    let fpInstance = null;
    const defaultHoursStart = 8;
    const defaultHoursEnd = 21; 
    const currentProfessionalId = "{{ $professionalId }}";
    
    // Dates with config to mark in calendar
    const configuredDates = @json($availabilities->pluck('date')->map(fn($d)=> \Carbon\Carbon::parse($d)->format('Y-m-d')));

    document.addEventListener('DOMContentLoaded', function() {
        fpInstance = flatpickr("#availability-calendar", {
            inline: true,
            locale: "es",
            dateFormat: "Y-m-d",
            disableMobile: true,
            onDayCreate: function(dObj, dStr, fp, dayElem) {
                const date = dayElem.dateObj;
                const offset = date.getTimezoneOffset();
                const localDate = new Date(date.getTime() - (offset*60*1000));
                const isoDate = localDate.toISOString().split('T')[0];
                if (configuredDates.includes(isoDate)) dayElem.classList.add('has-config');
            },
            onChange: function(selectedDates, dateStr) {
                if (dateStr) loadDate(dateStr);
            }
        });
    });

    function jumpToDate(dateStr) {
        if(fpInstance) {
            fpInstance.setDate(dateStr, false);
            loadDate(dateStr);
        }
    }

    async function loadDate(dateStr) {
        selectedDate = dateStr;
        const [y, m, d] = dateStr.split('-');
        const dateObj = new Date(y, m-1, d);
        document.getElementById('selected-date-display').innerText = dateObj.toLocaleDateString('es-CO', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
        
        document.getElementById('empty-state').classList.add('hidden');
        document.getElementById('editor-panel').classList.remove('hidden');
        document.getElementById('slots-grid').innerHTML = '<div class="col-span-full py-12 flex flex-col items-center"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-pink-600 mb-2"></div><p class="text-sm text-gray-500">Consultando disponibilidad...</p></div>';
        document.getElementById('day-message').value = '';

        try {
            const resp = await fetch(`{{ route('admin.availability.show') }}?date=${dateStr}&professional_id=${currentProfessionalId}`);
            const data = await resp.json();
            renderGrid(data.exists ? data.active_slots : null); 
            if (data.message) document.getElementById('day-message').value = data.message;
        } catch (e) {
            Swal.fire('Error', 'No se pudo cargar la configuración', 'error');
        }
    }

    function renderGrid(savedSlots) {
        const grid = document.getElementById('slots-grid');
        grid.innerHTML = '';
        activeSlots.clear();

        const allSlots = [];
        for (let h = defaultHoursStart; h <= defaultHoursEnd; h++) {
            allSlots.push(`${h.toString().padStart(2, '0')}:00`);
            allSlots.push(`${h.toString().padStart(2, '0')}:30`);
        }

        const isDefault = (savedSlots === null);
        allSlots.forEach(time => {
            const btn = document.createElement('button');
            btn.className = "slot-btn flex flex-col items-center p-3 rounded-xl border text-sm font-bold shadow-sm transition-all";
            btn.innerHTML = `<span>${formatTo12h(time).split(' ')[0]}</span><span class="text-[10px] opacity-60">${formatTo12h(time).split(' ')[1]}</span>`;
            
            let isActive = isDefault ? true : savedSlots.includes(time);
            if (isActive) { btn.classList.add('active'); activeSlots.add(time); }
            else btn.classList.add('inactive');

            btn.onclick = () => {
                if (activeSlots.has(time)) {
                    activeSlots.delete(time); btn.classList.remove('active'); btn.classList.add('inactive');
                } else {
                    activeSlots.add(time); btn.classList.remove('inactive'); btn.classList.add('active');
                }
            };
            grid.appendChild(btn);
        });
    }

    async function saveAvailability() {
        if (!selectedDate) return;
        try {
            Swal.fire({title: 'Guardando...', didOpen: () => Swal.showLoading()});
            const resp = await fetch(`{{ route('admin.availability.store') }}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({
                    date: selectedDate,
                    active_slots: Array.from(activeSlots).sort(),
                    message: document.getElementById('day-message').value,
                    professional_id: currentProfessionalId
                })
            });
            if (resp.ok) Swal.fire({ icon: 'success', title: 'Guardado', timer: 1000, showConfirmButton: false }).then(() => location.reload());
            else throw new Error('Error al guardar');
        } catch (e) { Swal.fire('Error', e.message, 'error'); }
    }

    function clearDay() {
        if (!selectedDate) return;
        Swal.fire({
            title: '¿Restablecer?',
            text: "Se volverá al horario normal para este profesional.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, restablecer'
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    const resp = await fetch(`{{ route('admin.availability.destroy') }}?date=${selectedDate}&professional_id=${currentProfessionalId}`, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                    });
                    if (resp.ok) location.reload();
                } catch (e) { Swal.fire('Error', 'No se pudo restablecer', 'error'); }
            }
        });
    }

    function formatTo12h(timeStr) {
        if (!timeStr) return '';
        const [h, m] = timeStr.split(':');
        let hh = parseInt(h);
        const ampm = hh >= 12 ? ' PM' : ' AM';
        hh = hh % 12 || 12;
        return `${hh}:${m}${ampm}`;
    }
</script>
@endpush
@endsection
