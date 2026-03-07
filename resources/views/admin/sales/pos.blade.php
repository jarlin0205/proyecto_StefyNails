@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-6" id="pos-app">
    <div class="flex flex-col lg:flex-row gap-6">
        
        <!-- Sección de Filtros y Productos (Izquierda) -->
        <div class="lg:w-2/3">
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <div class="flex items-center justify-between mb-6">
                    <h1 class="text-2xl font-bold text-gray-800 flex items-center">
                        <svg class="w-8 h-8 mr-2 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        Punto de Venta (POS)
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
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            Agregar
                        </button>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Sección de Carrito y Venta (Derecha) -->
        <div class="lg:w-1/3">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 flex flex-col h-full sticky top-6">
                <div class="p-6 border-b border-gray-100 italic">
                    <h2 class="text-lg font-bold text-gray-800 flex items-center justify-between">
                        Resumen de Venta
                        <span id="cart-count" class="bg-pink-100 text-pink-600 text-xs px-2 py-1 rounded-full">0 items</span>
                    </h2>
                </div>

                <div class="p-4 flex-grow overflow-y-auto max-h-[400px]" id="cart-items">
                    <!-- Items dinámicos -->
                    <div class="text-center py-10 text-gray-400 flex flex-col items-center">
                        <svg class="w-16 h-16 mb-2 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <p class="text-sm">El carrito está vacío</p>
                    </div>
                </div>

                <div class="p-6 bg-gray-50 rounded-b-xl border-t border-gray-100 space-y-4">
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

                    <div class="space-y-3 pt-4">
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Nombre Cliente</label>
                                <input type="text" id="customer_name" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-pink-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Teléfono / WhatsApp</label>
                                <input type="text" id="customer_phone" placeholder="WhatsApp activo" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-pink-500 outline-none">
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Método de Pago</label>
                            <select id="payment_method" onchange="togglePaymentAmounts()" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm font-semibold focus:ring-2 focus:ring-pink-500 outline-none appearance-none">
                                <option value="cash">Efectivo 💵</option>
                                <option value="transfer">Transferencia 💳</option>
                                <option value="hybrid">Híbrido 🔄</option>
                            </select>
                        </div>

                        <div id="hybrid-amounts" class="hidden grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Efectivo ($)</label>
                                <input type="number" id="cash_amount" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-pink-500 outline-none" value="0">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Transfer ($)</label>
                                <input type="number" id="transfer_amount" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-pink-500 outline-none" value="0">
                            </div>
                        </div>

                        <button onclick="finishSale()" id="btn-finish" class="w-full bg-pink-600 hover:bg-pink-700 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-pink-200 transition-all flex items-center justify-center space-x-2 group active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span id="btn-text">FINALIZAR VENTA</span>
                            <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M14 5l7 7m0 0l-7 7m7-7H3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                    </div>
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
    let cart = [];

    function addToCart(element) {
        const id = element.dataset.id;
        const name = element.dataset.name;
        const price = parseFloat(element.dataset.price);
        const stock = parseInt(element.dataset.stock);

        const existing = cart.find(item => item.id === id);
        if (existing) {
            if (existing.quantity < stock) {
                existing.quantity++;
            } else {
                Swal.fire('Oops!', 'No hay más stock disponible', 'warning');
                return;
            }
        } else {
            cart.push({ id, name, price, stock, quantity: 1 });
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
            if (newQty > 0 && newQty <= item.stock) {
                item.quantity = newQty;
                renderCart();
            } else if (newQty === 0) {
                removeFromCart(id);
            }
        }
    }

    function renderCart() {
        const container = document.getElementById('cart-items');
        const countBadge = document.getElementById('cart-count');
        
        if (cart.length === 0) {
            container.innerHTML = `
                <div class="text-center py-10 text-gray-400 flex flex-col items-center">
                    <svg class="w-16 h-16 mb-2 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <p class="text-sm">El carrito está vacío</p>
                </div>`;
            document.getElementById('cart-total').innerText = '$0.00';
            document.getElementById('cart-subtotal').innerText = '$0.00';
            countBadge.innerText = '0 items';
            return;
        }

        let html = '<div class="space-y-3">';
        let total = 0;
        let count = 0;

        cart.forEach(item => {
            total += item.price * item.quantity;
            count += item.quantity;
            html += `
                <div class="flex items-center justify-between bg-white border border-gray-100 p-3 rounded-lg shadow-sm animate-fadeIn">
                    <div class="min-w-0 flex-1 pr-4">
                        <p class="text-sm font-bold text-gray-800 truncate">${item.name}</p>
                        <p class="text-xs text-pink-600 font-bold">$${(item.price * item.quantity).toFixed(2)}</p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div class="flex items-center border border-gray-200 rounded-lg overflow-hidden h-8">
                            <button onclick="updateQuantity('${item.id}', -1)" class="px-2 hover:bg-gray-100 text-gray-500 font-bold">-</button>
                            <span class="px-3 text-xs font-bold text-gray-700 bg-gray-50 h-full flex items-center">${item.quantity}</span>
                            <button onclick="updateQuantity('${item.id}', 1)" class="px-2 hover:bg-gray-100 text-gray-500 font-bold">+</button>
                        </div>
                        <button onclick="removeFromCart('${item.id}')" class="text-gray-300 hover:text-red-500 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                    </div>
                </div>`;
        });

        html += '</div>';
        container.innerHTML = html;
        document.getElementById('cart-total').innerText = `$${total.toFixed(2)}`;
        document.getElementById('cart-subtotal').innerText = `$${total.toFixed(2)}`;
        countBadge.innerText = `${count} items`;
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

    async function finishSale() {
        if (cart.length === 0) {
            Swal.fire('Oops!', 'Agrega productos al carrito primero', 'info');
            return;
        }

        const btn = document.getElementById('btn-finish');
        const btnText = document.getElementById('btn-text');
        
        const data = {
            customer_name: document.getElementById('customer_name').value,
            customer_phone: document.getElementById('customer_phone').value,
            payment_method: document.getElementById('payment_method').value,
            cash_amount: parseFloat(document.getElementById('cash_amount').value) || 0,
            transfer_amount: parseFloat(document.getElementById('transfer_amount').value) || 0,
            items: cart.map(i => ({ product_id: i.id, quantity: i.quantity }))
        };

        // Validar híbrido
        if (data.payment_method === 'hybrid') {
            const total = cart.reduce((acc, i) => acc + (i.price * i.quantity), 0);
            if (Math.abs((data.cash_amount + data.transfer_amount) - total) > 0.1) {
                Swal.fire('Error', 'La suma de efectivo y transferencia debe coincidir con el total ($' + total.toFixed(2) + ')', 'error');
                return;
            }
        }

        btn.disabled = true;
        btnText.innerText = 'PROCESANDO...';

        try {
            const response = await fetch('{{ route("admin.sales.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            });

            const res = await response.json();

            if (res.success) {
                await Swal.fire({
                    title: '¡Venta Realizada!',
                    text: res.message,
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
                window.location.reload();
            } else {
                Swal.fire('Error', res.message || 'Error desconocido', 'error');
            }
        } catch (error) {
            console.error(error);
            Swal.fire('Error', 'Error de red o servidor', 'error');
        } finally {
            btn.disabled = false;
            btnText.innerText = 'FINALIZAR VENTA';
        }
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
</script>
@endsection
