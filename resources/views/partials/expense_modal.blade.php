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
                            <input type="number" name="amount" id="expense_amount" step="0.01" required 
                                   class="w-full pl-8 pr-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition-all outline-none" 
                                   placeholder="0">
                        </div>
                    </div>

                    <!-- Payment Method Selector -->
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">¿De dónde sale el dinero?</label>
                        <input type="hidden" name="payment_method" id="expense_payment_method" value="cash">
                        <div class="grid grid-cols-3 gap-3">
                            <button type="button" onclick="selectExpenseMethod('cash', this)" 
                                    class="expense-method-btn flex flex-col items-center p-3 rounded-xl border-2 border-green-500 bg-green-50 transition-all">
                                <i class="fas fa-money-bill-wave text-green-600 mb-1"></i>
                                <span class="text-[10px] font-bold text-green-700 uppercase">Caja</span>
                                <span class="text-[8px] text-green-500 font-medium">Efectivo</span>
                            </button>
                            <button type="button" onclick="selectExpenseMethod('transfer', this)" 
                                    class="expense-method-btn flex flex-col items-center p-3 rounded-xl border-2 border-gray-100 opacity-60 hover:opacity-100 transition-all">
                                <i class="fas fa-university text-blue-600 mb-1"></i>
                                <span class="text-[10px] font-bold text-blue-700 uppercase">Cuenta</span>
                                <span class="text-[8px] text-blue-500 font-medium">Transfer</span>
                            </button>
                            <button type="button" onclick="selectExpenseMethod('hybrid', this)" 
                                    class="expense-method-btn flex flex-col items-center p-3 rounded-xl border-2 border-gray-100 opacity-60 hover:opacity-100 transition-all">
                                <i class="fas fa-layer-group text-purple-600 mb-1"></i>
                                <span class="text-[10px] font-bold text-purple-700 uppercase">Mixto</span>
                                <span class="text-[8px] text-purple-500 font-medium">Híbrido</span>
                            </button>
                        </div>
                    </div>

                    <!-- Hybrid Inputs -->
                    <div id="expense-hybrid-inputs" class="hidden animate-fade-in bg-gray-50 p-4 rounded-xl border border-gray-200">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Pago desde Caja</label>
                                <input type="number" name="cash_amount" id="expense_cash_amount" placeholder="0"
                                       class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-green-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Pago desde Cuenta</label>
                                <input type="number" name="transfer_amount" id="expense_transfer_amount" placeholder="0"
                                       class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 outline-none">
                            </div>
                        </div>
                        <div class="mt-3 flex items-center justify-between text-xs">
                            <span class="text-gray-500">Suma: <b id="expense-hybrid-sum">$0</b></span>
                            <span id="expense-hybrid-diff" class="font-bold text-red-500">Falta: $0</span>
                            <span id="expense-hybrid-ok" class="hidden font-bold text-green-600"><i class="fas fa-check-circle"></i> ¡Coincide!</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Fecha del Gasto</label>
                        <input type="date" name="date" value="{{ date('Y-m-d') }}" required 
                               class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition-all outline-none">
                    </div>
                </div>

                <!-- Footer Buttons -->
                <div class="bg-gray-50 px-6 py-4 flex flex-row-reverse gap-3 font-semibold">
                    <button type="submit" id="btn-save-expense" class="bg-pink-600 text-white px-8 py-2.5 rounded-xl font-bold hover:bg-pink-700 shadow-lg shadow-pink-200 transition-all transform active:scale-95">
                        Registrar Gasto
                    </button>
                    <button type="button" onclick="document.getElementById('modalGasto').classList.add('hidden')" 
                            class="bg-white text-gray-700 px-6 py-2.5 rounded-xl font-bold border border-gray-200 hover:bg-gray-50 transition-all">
                        Cerrar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let selectedExpenseMethod = 'cash';

function selectExpenseMethod(method, btn) {
    selectedExpenseMethod = method;
    document.getElementById('expense_payment_method').value = method;
    
    // Reset buttons
    document.querySelectorAll('.expense-method-btn').forEach(b => {
        b.classList.remove('border-green-500', 'bg-green-50', 'border-blue-500', 'bg-blue-50', 'border-purple-500', 'bg-purple-50', 'opacity-100');
        b.classList.add('border-gray-100', 'opacity-60');
    });

    btn.classList.remove('border-gray-100', 'opacity-60');
    btn.classList.add('opacity-100');
    
    const hybridSection = document.getElementById('expense-hybrid-inputs');
    const total = parseFloat(document.getElementById('expense_amount').value) || 0;

    if (method === 'cash') {
        btn.classList.add('border-green-500', 'bg-green-50');
        hybridSection.classList.add('hidden');
    } else if (method === 'transfer') {
        btn.classList.add('border-blue-500', 'bg-blue-50');
        hybridSection.classList.add('hidden');
    } else if (method === 'hybrid') {
        btn.classList.add('border-purple-500', 'bg-purple-50');
        hybridSection.classList.remove('hidden');
        document.getElementById('expense_cash_amount').value = total;
        document.getElementById('expense_transfer_amount').value = 0;
        updateExpenseHybridSum();
    }
}

function updateExpenseHybridSum() {
    const total = parseFloat(document.getElementById('expense_amount').value) || 0;
    const cash = parseFloat(document.getElementById('expense_cash_amount').value) || 0;
    const transfer = parseFloat(document.getElementById('expense_transfer_amount').value) || 0;
    const sum = cash + transfer;
    const diff = total - sum;

    document.getElementById('expense-hybrid-sum').innerText = '$' + new Intl.NumberFormat().format(sum);
    const diffEl = document.getElementById('expense-hybrid-diff');
    const okEl = document.getElementById('expense-hybrid-ok');
    const btnSave = document.getElementById('btn-save-expense');

    if (Math.abs(diff) < 1) {
        diffEl.classList.add('hidden');
        okEl.classList.remove('hidden');
        btnSave.disabled = false;
        btnSave.classList.remove('opacity-50', 'cursor-not-allowed');
    } else {
        diffEl.classList.remove('hidden');
        diffEl.innerText = diff > 0 ? `Falta: $${new Intl.NumberFormat().format(diff)}` : `Exceso: $${new Intl.NumberFormat().format(Math.abs(diff))}`;
        okEl.classList.add('hidden');
        btnSave.disabled = true;
        btnSave.classList.add('opacity-50', 'cursor-not-allowed');
    }
}

document.getElementById('expense_amount').addEventListener('input', () => {
    if (selectedExpenseMethod === 'hybrid') updateExpenseHybridSum();
});
document.getElementById('expense_cash_amount').addEventListener('input', updateExpenseHybridSum);
document.getElementById('expense_transfer_amount').addEventListener('input', updateExpenseHybridSum);
</script>
