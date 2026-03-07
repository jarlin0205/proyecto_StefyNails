@extends('layouts.admin')

@section('header', 'Editar Venta POS')

@section('content')
<div class="container mx-auto px-4 py-6" id="pos-app">
    <div class="mb-6">
        <a href="{{ route('admin.sales.index') }}" class="text-pink-600 font-bold flex items-center hover:text-pink-700 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i> Volver al Listado
        </a>
    </div>

    <div class="flex flex-col lg:flex-row gap-6">
        
        <!-- Sección de Filtros y Productos (Izquierda) -->
        <div class="lg:w-2/3">
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <div class="flex items-center justify-between mb-6">
                    <h1 class="text-2xl font-bold text-gray-800 flex items-center">
                        <svg class="w-8 h-8 mr-2 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Editando Venta #{{ str_pad($sale->id, 5, '0', STR_PAD_LEFT) }}
                    </h1>
                    <div class="relative w-64">
                        <input type="text" id="productSearch" placeholder="Buscar producto..." 
                               class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-all outline-none text-sm">
                        <svg class="w-5 h-5 absolute left-3 top-2.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4 max-h-[600px] overflow-y-auto pr-2" id="products-grid">
                    @foreach($products as $product)
                    <div class="product-card bg-gray-50 rounded-xl p-4 border border-transparent hover:border-pink-200 hover:shadow-md transition-all cursor-pointer group flex flex-col justify-between"
                         data-id="{{ $product->id }}" 
                         data-name="{{ $product->name }}" 
                         data-price="{{ $product->price }}" 
                         data-stock="{{ $product->stock }}"
                         onclick="addToCart(this)">
                        <div class="relative mb-3">
                            @if($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-32 object-cover rounded-lg group-hover:scale-105 transition-transform duration-300">
                            @else
                                <div class="w-full h-32 bg-pink-50 rounded-lg flex items-center justify-center text-pink-200 group-hover:scale-105 transition-transform duration-300">
                                    <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" />
                                    </svg>
                                </div>
                            @endif
                            <span class="absolute top-2 right-2 bg-white/90 backdrop-blur-sm text-[10px] font-bold px-2 py-0.5 rounded-full text-gray-600 shadow-sm">
                                Stock: {{ $product->stock }}
                            </span>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 text-sm mb-1 truncate">{{ $product->name }}</h3>
                            <p class="text-pink-600 font-extrabold text-base">${{ number_format($product->price, 2) }}</p>
                        </div>
                        <button class="mt-3 w-full bg-white text-pink-600 border border-pink-200 py-1.5 rounded-lg text-xs font-bold group-hover:bg-pink-600 group-hover:text-white group-hover:border-pink-600 transition-colors flex items-center justify-center">
                            <i class="fas fa-plus mr-1"></i> Agregar
                        </button>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Sección de Carrito y Edición (Derecha) -->
        <div class="lg:w-1/3">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 flex flex-col h-full sticky top-6">
                <div class="p-6 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                    <h2 class="text-lg font-bold text-gray-800">Resumen de Venta</h2>
                    <span id="cart-count" class="bg-pink-100 text-pink-600 text-xs px-2 py-1 rounded-full">0 items</span>
                </div>

                <div class="p-4 flex-grow overflow-y-auto max-h-[400px]" id="cart-items">
                    <!-- Items dinámicos -->
                </div>

                <div class="p-6 bg-white rounded-b-xl border-t border-gray-100 space-y-4">
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>Subtotal</span>
                            <span id="cart-subtotal">$0.00</span>
                        </div>
                        <div class="flex justify-between items-center text-xl font-black text-gray-900 pt-2 border-t border-gray-200">
                            <span>TOTAL</span>
                            <span id="cart-total" class="text-pink-600">$0.00</span>
                        </div>
                    </div>

                    <form id="update-sale-form" action="{{ route('admin.sales.update', $sale->id) }}" method="POST" class="space-y-3 pt-4">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1 tracking-widest">Nombre Cliente</label>
                                <input type="text" name="customer_name" id="customer_name" value="{{ $sale->customer_name }}" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-pink-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1 tracking-widest">Teléfono / WhatsApp</label>
                                <input type="text" name="customer_phone" id="customer_phone" value="{{ $sale->customer_phone }}" placeholder="WhatsApp activo" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-pink-500 outline-none">
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1 tracking-widest">Método de Pago</label>
                            <select name="payment_method" id="payment_method" onchange="togglePaymentAmounts()" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm font-semibold focus:ring-2 focus:ring-pink-500 outline-none appearance-none">
                                <option value="cash" {{ $sale->payment_method === 'cash' ? 'selected' : '' }}>Efectivo 💵</option>
                                <option value="transfer" {{ $sale->payment_method === 'transfer' ? 'selected' : '' }}>Transferencia 💳</option>
                                <option value="hybrid" {{ $sale->payment_method === 'hybrid' ? 'selected' : '' }}>Híbrido 🔄</option>
                            </select>
                        </div>

                        <div id="hybrid-amounts" class="{{ $sale->payment_method === 'hybrid' ? '' : 'hidden' }} grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1 tracking-widest">Efectivo ($)</label>
                                <input type="number" name="cash_amount" id="cash_amount" value="{{ $sale->cash_amount }}" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-pink-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1 tracking-widest">Transfer ($)</label>
                                <input type="number" name="transfer_amount" id="transfer_amount" value="{{ $sale->transfer_amount }}" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-pink-500 outline-none">
                            </div>
                        </div>

                        {{-- Contenedor oculto para los items del carrito --}}
                        <div id="cart-hidden-inputs"></div>

                        <button type="button" onclick="submitEdit()" id="btn-finish" class="w-full bg-pink-600 hover:bg-pink-700 text-white font-black py-4 rounded-xl shadow-lg shadow-pink-200 transition-all flex items-center justify-center space-x-2 active:scale-95">
                            <span>GUARDAR CAMBIOS</span>
                            <i class="fas fa-save ml-2"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .product-card { transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); }
    .product-card:active { transform: scale(0.97); }
    #cart-items::-webkit-scrollbar { width: 4px; }
    #cart-items::-webkit-scrollbar-thumb { background: #fee2e2; border-radius: 10px; }
</style>

<script>
    // Inicializar carrito con items existentes
    let cart = [];
    @foreach($sale->items as $item)
        cart.push({
            id: '{{ $item->product_id }}',
            name: '{{ $item->product->name }}',
            price: {{ $item->unit_price }},
            stock: {{ $item->product->stock }},
            quantity: {{ $item->quantity }},
            original_quantity: {{ $item->quantity }} // Guardar para saber cuánto podemos aumentar
        });
    @endforeach

    function addToCart(element) {
        const id = element.dataset.id;
        const name = element.dataset.name;
        const price = parseFloat(element.dataset.price);
        const stock = parseInt(element.dataset.stock);

        const existing = cart.find(item => item.id === id);
        if (existing) {
            // Podemos vender lo que hay en stock MAS lo que ya tenemos en esta venta
            if (existing.quantity < (stock + existing.original_quantity)) {
                existing.quantity++;
            } else {
                Swal.fire('Atención', 'No hay más stock disponible incluso devolviendo la venta actual', 'warning');
                return;
            }
        } else {
            if (stock > 0) {
                cart.push({ id, name, price, stock, quantity: 1, original_quantity: 0 });
            } else {
                Swal.fire('Sin Stock', 'Este producto no tiene existencias', 'error');
                return;
            }
        }
        renderCart();
    }

    function removeFromCart(id) {
        cart = cart.filter(item => item.id !== id);
        renderCart();
    }

    function updateQuantity(id, delta) {
        const item = cart.find(i => i.id === id);
        if (item) {
            const newQty = item.quantity + delta;
            if (newQty > 0) {
                if (newQty <= (item.stock + item.original_quantity)) {
                    item.quantity = newQty;
                    renderCart();
                } else {
                    Swal.fire('Atención', 'Stock insuficiente', 'warning');
                }
            } else {
                removeFromCart(id);
            }
        }
    }

    function renderCart() {
        const container = document.getElementById('cart-items');
        const countBadge = document.getElementById('cart-count');
        const hiddenInputs = document.getElementById('cart-hidden-inputs');
        
        if (cart.length === 0) {
            container.innerHTML = `
                <div class="text-center py-10 text-gray-400 flex flex-col items-center">
                    <i class="fas fa-shopping-cart text-5xl opacity-10 mb-4"></i>
                    <p class="text-sm italic">El carrito está vacío</p>
                </div>`;
            document.getElementById('cart-total').innerText = '$0.00';
            document.getElementById('cart-subtotal').innerText = '$0.00';
            countBadge.innerText = '0';
            hiddenInputs.innerHTML = '';
            return;
        }

        let html = '<div class="space-y-3">';
        let hiddenHtml = '';
        let total = 0;
        let count = 0;

        cart.forEach((item, index) => {
            total += item.price * item.quantity;
            count += item.quantity;
            html += `
                <div class="flex items-center justify-between bg-white border border-gray-100 p-3 rounded-xl shadow-sm hover:shadow-md transition-shadow">
                    <div class="min-w-0 flex-1 pr-4">
                        <p class="text-sm font-black text-gray-800 truncate uppercase tracking-tighter">${item.name}</p>
                        <p class="text-xs text-pink-600 font-bold">$${(item.price * item.quantity).toLocaleString()}</p>
                    </div>
                    <div class="flex items-center space-x-3">
                        <div class="flex items-center border-2 border-pink-50 rounded-lg overflow-hidden h-9">
                            <button type="button" onclick="updateQuantity('${item.id}', -1)" class="px-3 hover:bg-pink-50 text-pink-400 font-black transition-colors">-</button>
                            <span class="px-4 text-xs font-black text-gray-800 bg-pink-50/30 h-full flex items-center">${item.quantity}</span>
                            <button type="button" onclick="updateQuantity('${item.id}', 1)" class="px-3 hover:bg-pink-50 text-pink-400 font-black transition-colors">+</button>
                        </div>
                        <button type="button" onclick="removeFromCart('${item.id}')" class="text-gray-300 hover:text-red-500 transition-all hover:scale-110">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>`;
            
            hiddenHtml += `<input type="hidden" name="items[${index}][product_id]" value="${item.id}">`;
            hiddenHtml += `<input type="hidden" name="items[${index}][quantity]" value="${item.quantity}">`;
        });

        html += '</div>';
        container.innerHTML = html;
        hiddenInputs.innerHTML = hiddenHtml;
        document.getElementById('cart-total').innerText = `$${total.toLocaleString()}`;
        document.getElementById('cart-subtotal').innerText = `$${total.toLocaleString()}`;
        countBadge.innerText = count;
    }

    function togglePaymentAmounts() {
        const method = document.getElementById('payment_method').value;
        const hybridDiv = document.getElementById('hybrid-amounts');
        if (method === 'hybrid') {
            hybridDiv.classList.remove('hidden');
        } else {
            hybridDiv.classList.add('hidden');
        }
    }

    function submitEdit() {
        if (cart.length === 0) {
            Swal.fire('Error', 'Debe haber al menos un producto en la venta', 'error');
            return;
        }

        const method = document.getElementById('payment_method').value;
        if (method === 'hybrid') {
            const total = cart.reduce((acc, i) => acc + (i.price * i.quantity), 0);
            const cash = parseFloat(document.getElementById('cash_amount').value) || 0;
            const transfer = parseFloat(document.getElementById('transfer_amount').value) || 0;
            
            if (Math.abs((cash + transfer) - total) > 0.1) {
                Swal.fire('Error', 'La suma de efectivo y transferencia ($' + (cash+transfer).toLocaleString() + ') no coincide con el total ($' + total.toLocaleString() + ')', 'error');
                return;
            }
        }

        Swal.fire({
            title: '¿Guardar cambios?',
            text: "Se actualizará la venta y se ajustará el stock automáticamente.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#db2777',
            confirmButtonText: 'Sí, guardar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('update-sale-form').submit();
            }
        });
    }

    // Filtro de búsqueda
    document.getElementById('productSearch').addEventListener('input', function(e) {
        const term = e.target.value.toLowerCase();
        const cards = document.querySelectorAll('.product-card');
        cards.forEach(card => {
            const name = card.dataset.name.toLowerCase();
            if (name.includes(term)) {
                card.classList.remove('hidden');
            } else {
                card.classList.add('hidden');
            }
        });
    });

    // Render inicial
    renderCart();
</script>
@endsection
