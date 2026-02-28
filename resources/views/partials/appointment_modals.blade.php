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
        <div class="fixed inset-0 transition-opacity z-0" aria-hidden="true" onclick="closeAppointmentModal()">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full relative z-50">
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
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <p class="text-xs text-gray-500 uppercase font-bold tracking-wider">Servicio</p>
                                <p id="modal-service" class="text-lg font-bold text-pink-600"></p>
                                <p id="modal-service-price" class="text-sm font-medium text-gray-400"></p>
                            </div>
                            <div id="modal-grand-total-section" class="text-right hidden">
                                <p class="text-xs text-gray-500 uppercase font-bold tracking-wider">Total Pagado</p>
                                <p id="modal-grand-total" class="text-2xl font-black text-green-600"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Products Breakdown (New) -->
                    <div id="modal-products-section" class="hidden bg-gray-50 rounded-lg p-3 border border-dashed border-gray-200">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 flex items-center">
                            <i class="fas fa-shopping-bag mr-1"></i> Productos Adquiridos
                        </p>
                        <div id="modal-products-list" class="space-y-1">
                            <!-- Injected by JS -->
                        </div>
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
                <!-- Botones de Factura (Ocultos por defecto) -->
                <button id="btn-view-invoice" onclick="handleInvoice()" class="flex-1 min-w-[120px] bg-pink-100 text-pink-700 rounded px-4 py-2 font-bold hover:bg-pink-200 transition hidden">
                    <i class="fas fa-file-pdf mr-1"></i> Factura
                </button>
                <button id="btn-whatsapp-invoice" onclick="sendWhatsAppInvoice()" class="flex-1 min-w-[120px] bg-green-100 text-green-700 rounded px-4 py-2 font-bold hover:bg-green-200 transition hidden">
                    <i class="fab fa-whatsapp mr-1"></i> Enviar
                </button>
                
                <button id="btn-cancel" onclick="handleAction('cancelar')" class="flex-1 min-w-[120px] bg-red-100 text-red-700 rounded px-4 py-2 font-bold hover:bg-red-200 transition">
                    Cancelar
                </button>
                <button id="btn-reschedule-link" onclick="openRescheduleModal(currentGlobalApp)" class="flex-1 min-w-[120px] bg-yellow-400 text-white text-center rounded px-4 py-2 font-bold hover:bg-yellow-500 transition shadow-sm">
                    Reprogramar
                </button>
                @if(auth()->user()->isAdmin())
                <button id="btn-delete" onclick="handleAction('eliminar')" class="flex-1 min-w-[120px] border border-red-300 text-red-600 rounded px-4 py-2 font-bold hover:bg-red-50 transition">
                    Eliminar
                </button>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Specialized Conflict Reschedule Modal -->
<div id="reschedule-modal" class="fixed inset-0 z-[60] hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity z-0" aria-hidden="true" onclick="closeRescheduleModal()">
            <div class="absolute inset-0 bg-gray-900 opacity-80 backdrop-blur-sm"></div>
        </div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full border-2 border-yellow-400 relative z-50">
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
                <input type="hidden" name="status" value="pending_client">
                <div class="p-6 space-y-4">
                    <p id="reschedule-modal-msg" class="text-sm text-gray-600 italic bg-yellow-50 p-3 rounded border-l-4 border-yellow-400">Reprograma la cita eligiendo un nuevo horario y explica el motivo al cliente.</p>
                    
                    <!-- Calendar and Time Selection -->
                    <div class="space-y-4">
                        <label class="block text-sm font-bold text-gray-700">1. Selecciona el Día</label>
                        <div class="flex flex-col lg:flex-row gap-4">
                            <div id="reschedule-inline-calendar" class="shadow-sm border border-yellow-100 rounded-lg overflow-hidden flex-shrink-0"></div>
                            <div id="reschedule_time_selection" class="flex-1 hidden">
                                <div class="mb-4">
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Profesional</label>
                                    <select id="reschedule_professional_selector" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-yellow-500">
                                        @foreach($professionals ?? [] as $p)
                                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
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
        <div class="fixed inset-0 transition-opacity z-0" aria-hidden="true" onclick="closeCreateModal()">
            <div class="absolute inset-0 bg-gray-900 opacity-80 backdrop-blur-sm"></div>
        </div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full relative z-50">
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
                                    <label class="block text-sm font-bold text-gray-700">Profesional</label>
                                    <select name="professional_id" id="create_modal_professional_selector" required class="w-full mt-1 px-3 py-2 border rounded-lg focus:ring-2 focus:ring-pink-500">
                                        @if(auth()->user()->isAdmin())
                                            @foreach($professionals ?? [] as $p)
                                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                                            @endforeach
                                        @else
                                            <option value="{{ auth()->user()->professional->id ?? '' }}">{{ auth()->user()->professional->name ?? 'Mío' }}</option>
                                        @endif
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

<!-- Payment Method Selection Modal -->
<div id="payment-modal" class="fixed inset-0 z-[70] hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity z-0" aria-hidden="true" onclick="closePaymentModal()">
            <div class="absolute inset-0 bg-gray-900 opacity-80 backdrop-blur-sm"></div>
        </div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border-2 border-blue-500 relative z-50">
            <div class="bg-blue-50 px-6 py-4 border-b border-blue-100 flex justify-between items-center">
                <h3 class="text-lg font-bold text-blue-800 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                    Método de Pago
                </h3>
                <button onclick="closePaymentModal()" class="text-blue-600 hover:text-blue-800">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <p class="text-sm text-gray-600 mb-4">Selecciona cómo pagó el cliente el servicio de <span id="payment-modal-service" class="font-bold text-gray-800"></span>.</p>
                
                <div class="grid grid-cols-1 gap-3">
                    <button onclick="selectPaymentMethod('cash', this)" class="flex items-center justify-between p-4 border-2 border-gray-100 rounded-xl hover:border-green-500 hover:bg-green-50 transition-all group">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-green-600 mr-3 group-hover:bg-green-200">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <div class="text-left">
                                <p class="font-bold text-gray-800">Efectivo</p>
                                <p class="text-xs text-gray-500">Pago total en físico</p>
                            </div>
                        </div>
                        <i class="fas fa-chevron-right text-gray-300 group-hover:text-green-500"></i>
                    </button>

                    <button onclick="selectPaymentMethod('transfer', this)" class="flex items-center justify-between p-4 border-2 border-gray-100 rounded-xl hover:border-blue-500 hover:bg-blue-50 transition-all group">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 mr-3 group-hover:bg-blue-200">
                                <i class="fas fa-university"></i>
                            </div>
                            <div class="text-left">
                                <p class="font-bold text-gray-800">Transferencia / Cuenta</p>
                                <p class="text-xs text-gray-500">Pago por banco o apps</p>
                            </div>
                        </div>
                        <i class="fas fa-chevron-right text-gray-300 group-hover:text-blue-500"></i>
                    </button>

                    <button onclick="selectPaymentMethod('hybrid', this)" class="flex items-center justify-between p-4 border-2 border-gray-100 rounded-xl hover:border-purple-500 hover:bg-purple-50 transition-all group">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 mr-3 group-hover:bg-purple-200">
                                <i class="fas fa-layer-group"></i>
                            </div>
                            <div class="text-left">
                                <p class="font-bold text-gray-800">Híbrido</p>
                                <p class="text-xs text-gray-500">Parte efectivo y parte cuenta</p>
                            </div>
                        </div>
                        <i class="fas fa-chevron-right text-gray-300 group-hover:text-purple-500"></i>
                    </button>
                </div>

                <div class="grid grid-cols-1 gap-3">
                    <!-- ... payment methods ... -->
                </div>

                <!-- Venta de Productos -->
                <div class="pt-4 border-t space-y-3">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-widest flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        Venta de Productos (Opcional)
                    </p>
                    
                    <div class="relative">
                        <select id="product-selector" onchange="addProductFromSelector(this.value)" class="w-full text-sm border-gray-200 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Selecciona un producto para agregar...</option>
                        </select>
                    </div>

                    <div id="selected-products-list" class="space-y-2 max-h-40 overflow-y-auto pr-1">
                        <!-- Productos agregados aparecerán aquí -->
                    </div>

                    <div id="products-total-row" class="hidden flex justify-between items-center bg-gray-50 p-2 rounded-lg border border-dashed text-sm">
                        <span class="text-gray-600 font-medium">Subtotal Productos:</span>
                        <span id="products-total-display" class="font-bold text-gray-900">$0</span>
                    </div>
                </div>

                <!-- Input section for Hybrid (hidden by default) -->
                <div id="hybrid-inputs" class="hidden space-y-3 pt-4 border-t">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-widest">Detalle del Pago Híbrido</p>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Efectivo</label>
                            <input type="number" id="cash_amount_input" oninput="updateHybridSum()" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 font-bold" placeholder="0">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Cuenta</label>
                            <input type="number" id="transfer_amount_input" oninput="updateHybridSum()" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 font-bold" placeholder="0">
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 p-4 rounded-xl border border-dashed border-gray-200 space-y-2">
                        <div class="flex justify-between items-center">
                            <span class="text-xs font-medium text-gray-500 uppercase">Suma Ingresada</span>
                            <span id="hybrid-sum-display" class="text-lg font-black text-gray-900">$0</span>
                        </div>
                        <div class="flex justify-between items-center pt-2 border-t border-gray-100">
                            <span class="text-xs font-medium text-gray-500 uppercase">Restante</span>
                            <span id="hybrid-remaining-display" class="text-sm font-bold text-pink-600">$0</span>
                        </div>
                        <div id="hybrid-match-success" class="hidden flex items-center justify-center text-green-600 text-[10px] font-bold uppercase mt-1">
                            <i class="fas fa-check-circle mr-1"></i> ¡Monto completado!
                        </div>
                    </div>
                </div>

                <!-- Total General Mostrar siempre -->
                <div class="pt-4 border-t flex justify-between items-end">
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-widest">Total a Pagar</span>
                    <span id="payment-modal-total-display" class="text-2xl font-black text-pink-600">$0</span>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex gap-3 border-t">
                <button type="button" onclick="closePaymentModal()" class="flex-1 px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg font-bold hover:bg-gray-100 transition">
                    Cancelar
                </button>
                <button id="btn-confirm-payment" onclick="confirmPaymentCompletion()" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700 transition shadow-md hidden">
                    Finalizar Cita
                </button>
            </div>
        </div>
    </div>
</div>

<form id="global-action-form" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="status" id="global-action-status">
    <input type="hidden" name="reason" id="global-action-reason">
    <input type="hidden" name="payment_method" id="global-payment-method">
    <input type="hidden" name="cash_amount" id="global-cash-amount">
    <input type="hidden" name="transfer_amount" id="global-transfer-amount">
    <input type="hidden" name="products_json" id="global-products-json">
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

            document.getElementById('reschedule_professional_selector').addEventListener('change', () => {
                if(selectedRescheduleDate) fetchRescheduleBusySlots(selectedRescheduleDate);
            });
        }
    });

    async function fetchRescheduleBusySlots(date) {
        const professionalId = document.getElementById('reschedule_professional_selector').value;
        const timeDiv = document.getElementById('reschedule_time_selection');
        const container = document.getElementById('reschedule_slots_container');
        const msg = document.getElementById('reschedule_no_slots_msg');
        
        container.innerHTML = '<div class="col-span-full py-4 text-center text-yellow-300">Cargando...</div>';
        timeDiv.classList.remove('hidden');
        msg.classList.add('hidden');

        try {
            const resp = await fetch(`{{ url('api/bot/busy-slots') }}?date=${date}&professional_id=${professionalId}`);
            if (!resp.ok) throw new Error('Error en el servidor');
            const data = await resp.json();
            generateRescheduleSlots(data);
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

            document.getElementById('create_modal_professional_selector').addEventListener('change', () => {
                if(selectedCreateDate) fetchCreateBusySlots(selectedCreateDate);
            });
        }
    }

    function closeCreateModal() {
        document.getElementById('create-modal').classList.add('hidden');
    }

    async function fetchCreateBusySlots(date) {
        const professionalId = document.getElementById('create_modal_professional_selector').value;
        const container = document.getElementById('create_slots_container');
        const timeDiv = document.getElementById('create_time_selection');
        container.innerHTML = '<div class="col-span-full py-4 text-center text-pink-300">...</div>';
        timeDiv.classList.remove('hidden');

        try {
            const resp = await fetch(`{{ url('api/bot/busy-slots') }}?date=${date}&professional_id=${professionalId}`);
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
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const todayStr = `${year}-${month}-${day}`;
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
        
        const priceFmt = '$' + new Intl.NumberFormat().format(data.price);
        const servicePriceEl = document.getElementById('modal-service-price');
        if (servicePriceEl) servicePriceEl.innerText = priceFmt;
        
        // Products handling
        const productsSection = document.getElementById('modal-products-section');
        const productsList = document.getElementById('modal-products-list');
        const totalSection = document.getElementById('modal-grand-total-section');
        const totalDisplay = document.getElementById('modal-grand-total');

        if (data.products && data.products.length > 0) {
            productsSection.classList.remove('hidden');
            totalSection.classList.remove('hidden');
            totalDisplay.innerText = '$' + new Intl.NumberFormat().format(data.grand_total);
            
            productsList.innerHTML = '';
            data.products.forEach(p => {
                const item = document.createElement('div');
                item.className = 'flex justify-between text-xs text-gray-600';
                item.innerHTML = `
                    <span>${p.quantity}x ${p.name}</span>
                    <span class="font-medium">$${new Intl.NumberFormat().format(p.price * p.quantity)}</span>
                `;
                productsList.appendChild(item);
            });
        } else {
            productsSection.classList.add('hidden');
            if (data.status === 'completed') {
                totalSection.classList.remove('hidden');
                totalDisplay.innerText = priceFmt;
            } else {
                totalSection.classList.add('hidden');
            }
        }
        
        document.getElementById('modal-notes').innerText = data.notes || 'Sin notas adicionales';
        
        const statusEl = document.getElementById('modal-status');
        const statusMap = {
            'pending_admin': { label: 'Esperando Admin', class: 'bg-orange-100 text-orange-800' },
            'pending_client': { label: 'Esperando Cliente', class: 'bg-yellow-100 text-yellow-800' },
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

        // Actions for Employees and Admin: Complete and Cancel
        // Cannot complete if pending, completed or cancelled
        document.getElementById('btn-complete').classList.toggle('hidden', data.status === 'completed' || data.status === 'cancelled' || data.status === 'pending_admin' || data.status === 'pending_client');
        
        // Cannot cancel if already cancelled or completed
        document.getElementById('btn-cancel').classList.toggle('hidden', data.status === 'cancelled' || data.status === 'completed');
        
        // Reschedule (Reprogramar) - Available for all if not finished
        document.getElementById('btn-reschedule-link').classList.toggle('hidden', data.status === 'completed' || data.status === 'cancelled');
        
        // Confirm button (only for pending_admin)
        const btnConf = document.getElementById('btn-confirm');
        if(data.status === 'pending_admin') {
            btnConf.classList.remove('hidden');
        } else {
            btnConf.classList.add('hidden');
        }

        // Hide/Show delete button (only if completed or cancelled AND USER IS ADMIN)
        const btnDelete = document.getElementById('btn-delete');
        if (btnDelete) {
            btnDelete.classList.toggle('hidden', data.status !== 'completed' && data.status !== 'cancelled');
        }

        // Mostrar botones de factura solo si está completada
        document.getElementById('btn-view-invoice').classList.toggle('hidden', data.status !== 'completed');
        document.getElementById('btn-whatsapp-invoice').classList.toggle('hidden', data.status !== 'completed');

        document.getElementById('appointment-modal').classList.remove('hidden');
    }

    function closeAppointmentModal() {
        document.getElementById('appointment-modal').classList.add('hidden');
    }

    function openRescheduleModal(data) {
        console.log('Opening reschedule modal with data:', data);
        currentGlobalApp = data;
        const form = document.getElementById('reschedule-form');
        form.action = data.status_url || `{{ url('admin/appointments') }}/${data.id}/status`;
        
        // Force the status to pending_client for admin reschedules
        const statusInput = form.querySelector('input[name="status"]');
        if (statusInput) statusInput.value = 'pending_client';
        
        // Pre-select professional
        const profSelect = document.getElementById('reschedule_professional_selector');
        if (profSelect) {
            if (data.professional_id) profSelect.value = data.professional_id;
            // Employees shouldn't change professional usually, or at least they only see themselves
            @if(auth()->user()->role === 'employee')
                profSelect.disabled = true;
            @else
                profSelect.disabled = false;
            @endif
        }

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

        // PRE-CHECK: Prevent completing future-dated appointments
        if (action === 'completar') {
            // Replace space with T to ensure valid ISO 8601 parsing across all browsers
            const appDateStr = (currentGlobalApp.appointment_date || '').replace(' ', 'T');
            const appDate = new Date(appDateStr);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            appDate.setHours(0, 0, 0, 0);

            if (!window.isTestMode && !isNaN(appDate.getTime()) && appDate > today) {
                const formatted = appDate.toLocaleDateString('es-CO', { day: '2-digit', month: '2-digit', year: 'numeric' });
                Swal.fire({
                    title: '⏳ Cita en el futuro',
                    html: `Esta cita está agendada para el <strong>${formatted}</strong>, una fecha que aún no ha llegado.<br><br>
                           Si el cliente fue atendido hoy de forma anticipada, por favor <strong>edita primero la fecha de la cita a hoy</strong> antes de marcarla como completada.`,
                    icon: 'warning',
                    confirmButtonColor: '#ec4899',
                    confirmButtonText: 'Entendido',
                    showCancelButton: false,
                });
                return; // Block the action
            }
        }

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
                } else if (action === 'completar') {
                    openPaymentModal();
                } else {
                    const form = document.getElementById('global-action-form');
                    form.action = currentGlobalApp.status_url;
                    document.getElementById('global-action-status').value = 
                        (action === 'confirmar' ? 'confirmed' : 'pending_admin');
                    form.submit();
                }
            }
        });
    }

    let selectedPaymentMethod = null;
    let allProducts = [];
    let selectedProducts = [];

    async function initProductSelector() {
        if (allProducts.length === 0) {
            try {
                const response = await fetch('{{ route("admin.products.list") }}');
                allProducts = await response.json();
            } catch (e) {
                console.error("Error loading products:", e);
                return;
            }
        }
        
        const selector = document.getElementById('product-selector');
        selector.innerHTML = '<option value="">Selecciona un producto para agregar...</option>';
        allProducts.forEach(p => {
            selector.innerHTML += `<option value="${p.id}">${p.name} - ${p.category} ($${new Intl.NumberFormat().format(p.price)})</option>`;
        });
    }

    function addProductFromSelector(id) {
        if (!id) return;
        const product = allProducts.find(p => p.id == id);
        if (!product) return;

        const existing = selectedProducts.find(p => p.id == id);
        if (existing) {
            existing.quantity++;
        } else {
            selectedProducts.push({ ...product, quantity: 1 });
        }
        
        document.getElementById('product-selector').value = '';
        renderSelectedProducts();
        updatePaymentTotals();
    }

    function renderSelectedProducts() {
        const list = document.getElementById('selected-products-list');
        list.innerHTML = '';
        
        if (selectedProducts.length === 0) {
            document.getElementById('products-total-row').classList.add('hidden');
            return;
        }

        document.getElementById('products-total-row').classList.remove('hidden');
        
        selectedProducts.forEach(p => {
            const row = document.createElement('div');
            row.className = 'flex items-center justify-between bg-white border border-gray-100 p-2 rounded-lg shadow-sm text-sm';
            row.innerHTML = `
                <div class="flex-1">
                    <p class="font-bold text-gray-800 leading-tight">${p.name}</p>
                    <p class="text-[10px] text-gray-500">$${new Intl.NumberFormat().format(p.price)} c/u</p>
                </div>
                <div class="flex items-center space-x-3">
                    <div class="flex items-center border rounded-md">
                        <button onclick="changeProductQty(${p.id}, -1)" class="px-2 py-1 hover:bg-gray-50 text-gray-400">-</button>
                        <span class="px-2 font-bold text-gray-700 min-w-[20px] text-center">${p.quantity}</span>
                        <button onclick="changeProductQty(${p.id}, 1)" class="px-2 py-1 hover:bg-gray-50 text-gray-400">+</button>
                    </div>
                    <button onclick="removeProduct(${p.id})" class="text-red-400 hover:text-red-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            `;
            list.appendChild(row);
        });
    }

    function changeProductQty(id, delta) {
        const p = selectedProducts.find(p => p.id == id);
        if (!p) return;
        p.quantity += delta;
        if (p.quantity < 1) p.quantity = 1;
        renderSelectedProducts();
        updatePaymentTotals();
    }

    function removeProduct(id) {
        selectedProducts = selectedProducts.filter(p => p.id != id);
        renderSelectedProducts();
        updatePaymentTotals();
    }

    function updatePaymentTotals() {
        if (!currentGlobalApp) return;
        
        const productsTotal = selectedProducts.reduce((sum, p) => sum + (parseFloat(p.price) * p.quantity), 0);
        const serviceTotal = parseFloat(currentGlobalApp.price) || 0;
        const grandTotal = serviceTotal + productsTotal;
        
        const formatter = new Intl.NumberFormat('es-CO', {
            style: 'currency',
            currency: 'COP',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        });

        const pTotalDisplay = document.getElementById('products-total-display');
        const gTotalDisplay = document.getElementById('payment-modal-total-display');

        if(pTotalDisplay) pTotalDisplay.innerText = formatter.format(productsTotal).replace('COP', '').trim();
        if(gTotalDisplay) gTotalDisplay.innerText = formatter.format(grandTotal).replace('COP', '').trim();
        
        // Update hybrid calculations if active
        if (selectedPaymentMethod === 'hybrid') {
            updateHybridSum();
        }
    }

    function openPaymentModal() {
        if (!currentGlobalApp) return;
        document.getElementById('payment-modal-service').innerText = currentGlobalApp.service_name;
        document.getElementById('payment-modal').classList.remove('hidden');
        document.getElementById('appointment-modal').classList.add('hidden'); // Fix overlap
        
        selectedProducts = [];
        renderSelectedProducts();
        initProductSelector();
        resetPaymentSelections();
        updatePaymentTotals();
    }

    function closePaymentModal() {
        document.getElementById('payment-modal').classList.add('hidden');
        document.getElementById('appointment-modal').classList.remove('hidden');
    }

    function resetPaymentSelections() {
        selectedPaymentMethod = null;
        document.querySelectorAll('#payment-modal button[onclick^="selectPaymentMethod"]').forEach(btn => {
            btn.classList.remove('border-green-500', 'bg-green-50', 'border-blue-500', 'bg-blue-50', 'border-purple-500', 'bg-purple-50');
            btn.classList.add('border-gray-100');
        });
        document.getElementById('hybrid-inputs').classList.add('hidden');
        document.getElementById('btn-confirm-payment').classList.add('hidden');
        document.getElementById('cash_amount_input').value = '';
        document.getElementById('transfer_amount_input').value = '';
        updateHybridSum(); // Clear displays
    }

    function selectPaymentMethod(method, btn) {
        selectedPaymentMethod = method;
        resetPaymentSelections();
        selectedPaymentMethod = method; // reapplying after reset

        btn.classList.remove('border-gray-100');
        
        const productsTotal = selectedProducts.reduce((sum, p) => sum + (p.price * p.quantity), 0);
        const grandTotal = currentGlobalApp.price + productsTotal;

        if (method === 'cash') btn.classList.add('border-green-500', 'bg-green-50');
        if (method === 'transfer') btn.classList.add('border-blue-500', 'bg-blue-50');
        if (method === 'hybrid') {
            btn.classList.add('border-purple-500', 'bg-purple-50');
            document.getElementById('hybrid-inputs').classList.remove('hidden');
            // Suggest split
            document.getElementById('cash_amount_input').value = grandTotal; 
            document.getElementById('transfer_amount_input').value = 0;
            updateHybridSum();
        }

        document.getElementById('btn-confirm-payment').classList.remove('hidden');
    }

    function updateHybridSum() {
        if (!currentGlobalApp) return;
        
        const productsTotal = selectedProducts.reduce((sum, p) => sum + (parseFloat(p.price) * p.quantity), 0);
        const grandTotal = (parseFloat(currentGlobalApp.price) || 0) + productsTotal;
        
        const cash = parseFloat(document.getElementById('cash_amount_input').value) || 0;
        const transfer = parseFloat(document.getElementById('transfer_amount_input').value) || 0;
        const sum = cash + transfer;
        const remaining = grandTotal - sum;

        const sumDisplay = document.getElementById('hybrid-sum-display');
        const remainingDisplay = document.getElementById('hybrid-remaining-display');
        const successTag = document.getElementById('hybrid-match-success');
        const btnConfirm = document.getElementById('btn-confirm-payment');

        const formatter = new Intl.NumberFormat('es-CO', {
            style: 'currency',
            currency: 'COP',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        });

        if(sumDisplay) sumDisplay.innerText = formatter.format(sum).replace('COP', '').trim();
        if(remainingDisplay) remainingDisplay.innerText = formatter.format(remaining).replace('COP', '').trim();

        if (Math.abs(remaining) < 1) {
            if(remainingDisplay) remainingDisplay.classList.add('hidden');
            if(successTag) successTag.classList.remove('hidden');
            btnConfirm.disabled = false;
            btnConfirm.classList.remove('opacity-50', 'cursor-not-allowed');
        } else {
            if(remainingDisplay) remainingDisplay.classList.remove('hidden');
            if(successTag) successTag.classList.add('hidden');
            if (selectedPaymentMethod === 'hybrid') {
                btnConfirm.disabled = true;
                btnConfirm.classList.add('opacity-50', 'cursor-not-allowed');
            }
        }
    }

    function confirmPaymentCompletion() {
        if (!selectedPaymentMethod) return;

        const productsTotal = selectedProducts.reduce((sum, p) => sum + (parseFloat(p.price) * p.quantity), 0);
        const grandTotal = (parseFloat(currentGlobalApp.price) || 0) + productsTotal;
        
        const formatter = new Intl.NumberFormat('es-CO', {
            style: 'currency',
            currency: 'COP',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        });

        const form = document.getElementById('global-action-form');
        form.action = currentGlobalApp.status_url;
        
        document.getElementById('global-action-status').value = 'completed';
        document.getElementById('global-payment-method').value = selectedPaymentMethod;
        document.getElementById('global-products-json').value = JSON.stringify(selectedProducts);

        if (selectedPaymentMethod === 'hybrid') {
            const cash = parseFloat(document.getElementById('cash_amount_input').value) || 0;
            const transfer = parseFloat(document.getElementById('transfer_amount_input').value) || 0;

            if (Math.abs((cash + transfer) - grandTotal) > 1) {
                Swal.fire('Atención', `La suma ($${new Intl.NumberFormat().format(cash + transfer)}) debe coincidir con el total ($${new Intl.NumberFormat().format(grandTotal)})`, 'warning');
                return;
            }

            document.getElementById('global-cash-amount').value = cash;
            document.getElementById('global-transfer-amount').value = transfer;
        }

        Swal.fire({
            title: 'Finalizando...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        form.submit();
    }

    function promptCancellation() {
        Swal.fire({
            title: '¿Confirmar cancelación?',
            text: 'Se enviará un mensaje al cliente informando que no hay espacios disponibles.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, Cancelar Cita',
            confirmButtonColor: '#ef4444',
            cancelButtonText: 'Volver',
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('global-action-form');
                form.action = currentGlobalApp.status_url;
                document.getElementById('global-action-status').value = 'cancelled';
                document.getElementById('global-action-reason').value = 'No contamos con espacios disponibles';
                form.submit();
            }
        });
    }

    function handleInvoice() {
        if (!currentGlobalApp) return;
        const url = `{{ url('appointment-invoice') }}/${currentGlobalApp.id}`;
        window.open(url, '_blank');
    }

    async function sendWhatsAppInvoice() {
        if (!currentGlobalApp) return;

        Swal.fire({
            title: 'Enviando Factura...',
            text: 'Por favor espera un momento.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        try {
            const response = await fetch(`{{ url('admin/appointments') }}/${currentGlobalApp.id}/send-invoice`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                Swal.fire({
                    title: '¡Enviada!',
                    text: data.message,
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        } catch (error) {
            Swal.fire('Error', 'No se pudo enviar la factura por WhatsApp.', 'error');
        }
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
