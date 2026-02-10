<!-- Modal de Gastos -->
<div id="modalGasto" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
    <!-- Backdrop oscuro -->
    <div class="fixed inset-0 bg-gray-900 bg-opacity-60 backdrop-blur-sm transition-opacity" onclick="document.getElementById('modalGasto').classList.add('hidden')"></div>

    <!-- Contenedor del Modal -->
    <div class="fixed inset-0 flex items-center justify-center p-4 pointer-events-none">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg transform transition-all pointer-events-auto overflow-hidden border border-gray-100">
            <form action="{{ route('admin.expenses.store') }}" method="POST">
                @csrf
                <!-- Header -->
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="bg-red-100 p-2 rounded-lg mr-3">
                            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800">Registrar Gasto</h3>
                    </div>
                    <button type="button" onclick="document.getElementById('modalGasto').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Form Body -->
                <div class="p-6 space-y-5">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Descripción del Gasto</label>
                        <input type="text" name="description" required 
                               class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition-all outline-none" 
                               placeholder="Ej: Esmaltes, Alquiler, Insumos...">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Monto Total ($)</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-bold">$</span>
                            <input type="number" name="amount" step="0.01" required 
                                   class="w-full pl-8 pr-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition-all outline-none" 
                                   placeholder="0">
                        </div>
                        <p class="text-xs text-gray-500 mt-2 italic">Ingresa el valor real (Ej: 25000 para 25 mil)</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Fecha del Gasto</label>
                        <input type="date" name="date" value="{{ date('Y-m-d') }}" required 
                               class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition-all outline-none">
                    </div>
                </div>

                <!-- Footer Buttons -->
                <div class="bg-gray-50 px-6 py-4 flex flex-row-reverse gap-3">
                    <button type="submit" class="bg-pink-600 text-white px-6 py-2.5 rounded-xl font-bold hover:bg-pink-700 shadow-lg shadow-pink-200 transition-all transform active:scale-95">
                        Guardar Gasto
                    </button>
                    <button type="button" onclick="document.getElementById('modalGasto').classList.add('hidden')" 
                            class="bg-white text-gray-700 px-6 py-2.5 rounded-xl font-bold border border-gray-200 hover:bg-gray-50 transition-all">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
