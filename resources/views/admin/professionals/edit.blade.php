@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="flex items-center gap-4 mb-8">
            <a href="{{ route('admin.professionals.index') }}" class="text-gray-400 hover:text-pink-600 transition">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <h1 class="text-2xl font-bold text-gray-800">Editar Profesional</h1>
        </div>

        @if($errors->any())
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.professionals.update', $professional->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 space-y-6">
                <div class="flex items-center gap-4 mb-4">
                    @if($professional->photo_path)
                        <img class="h-16 w-16 rounded-full object-cover border-2 border-pink-100" src="{{ asset('storage/' . $professional->photo_path) }}" alt="{{ $professional->name }}">
                    @else
                        <div class="h-16 w-16 rounded-full bg-pink-100 flex items-center justify-center text-pink-600 text-2xl font-bold">
                            {{ substr($professional->name, 0, 1) }}
                        </div>
                    @endif
                    <div>
                        <p class="text-sm text-gray-500">Actualizando perfil de</p>
                        <p class="text-lg font-bold text-gray-800">{{ $professional->name }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Nombre Completo *</label>
                        <input type="text" name="name" required value="{{ old('name', $professional->name) }}" 
                               class="w-full border-gray-200 rounded-lg focus:ring-pink-500 focus:border-pink-500 transition-all">
                    </div>
                    <div class="col-span-full">
                        <label class="block text-sm font-bold text-gray-700 mb-3">Especialidades (Categorías) *</label>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                            @foreach($categories as $category)
                                <label class="flex items-center gap-2 p-3 border rounded-lg hover:bg-pink-50 cursor-pointer transition-colors border-gray-200">
                                    <input type="checkbox" name="categories[]" value="{{ $category->id }}"
                                           {{ $professional->categories->contains($category->id) ? 'checked' : '' }}
                                           class="h-4 w-4 text-pink-600 focus:ring-pink-500 border-gray-300 rounded">
                                    <span class="text-sm font-medium text-gray-700">{{ $category->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="col-span-full">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Descripción Adicional de Especialidad (Opcional)</label>
                        <input type="text" name="specialty" value="{{ old('specialty', $professional->specialty) }}" placeholder="Ej: Especialista en diseño mano alzada"
                               class="w-full border-gray-200 rounded-lg focus:ring-pink-500 focus:border-pink-500 transition-all">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">WhatsApp de Contacto (Obligatorio para el Bot) *</label>
                        <input type="tel" id="phone" name="phone" value="{{ old('phone', $professional->phone) }}" required 
                               class="w-full border-gray-200 rounded-lg focus:ring-pink-500 focus:border-transparent transition-all">
                        <input type="hidden" name="phone_full" id="phone_full" value="{{ old('phone_full', $professional->phone) }}">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Cambiar Foto</label>
                        <input type="file" name="photo" accept="image/*"
                               class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-pink-50 file:text-pink-700 hover:file:bg-pink-100 transition-all">
                    </div>
                </div>

                @push('styles')
                <style>
                    .iti { width: 100%; }
                    .iti__flag-container { border-right: 1px solid #e5e7eb; }
                </style>
                @endpush

                @push('scripts')
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const phoneInput = document.querySelector("#phone");
                        const phoneFullInput = document.querySelector("#phone_full");
                        
                        const iti = window.intlTelInput(phoneInput, {
                            initialCountry: "co",
                            preferredCountries: ["co", "us", "es"],
                            separateDialCode: true,
                            utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.12/build/js/utils.js",
                        });

                        // Sincronizar número completo al cambiar o enviar
                        const updatePhone = () => {
                            phoneFullInput.value = iti.getNumber();
                        };

                        phoneInput.addEventListener('change', updatePhone);
                        phoneInput.addEventListener('keyup', updatePhone);
                        
                        // Validar antes de enviar
                        phoneInput.closest('form').addEventListener('submit', function(e) {
                            if (!iti.isValidNumber()) {
                                e.preventDefault();
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Número inválido',
                                    text: 'Por favor ingresa un número de WhatsApp válido.',
                                    confirmButtonColor: '#db2777'
                                });
                            }
                        });
                    });
                </script>
                @endpush

                <div class="flex items-center gap-3 pt-4 border-t border-gray-50">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" {{ $professional->is_active ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-pink-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-pink-600"></div>
                        <span class="ml-3 text-sm font-bold text-gray-700">Profesional Activo</span>
                    </label>
                </div>
            </div>

            <div class="bg-pink-50 rounded-xl border border-pink-100 p-6 space-y-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-pink-700 flex items-center">
                        <i class="fas fa-user-lock mr-2"></i> {{ $professional->user ? 'Gestionar Cuenta de Acceso' : 'Crear Cuenta de Acceso' }}
                    </h3>
                    @if(!$professional->user)
                        <input type="checkbox" name="create_user" id="create_user" value="1" {{ old('create_user') ? 'checked' : '' }}
                               class="h-5 w-5 text-pink-600 focus:ring-pink-500 border-pink-300 rounded transition-all">
                    @endif
                </div>

                <div id="user_fields" class="{{ $professional->user || old('create_user') ? '' : 'hidden' }} space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Correo Electrónico (Login) *</label>
                            <input type="email" name="email" value="{{ old('email', $professional->user ? $professional->user->email : '') }}" 
                                   class="w-full border-gray-200 rounded-lg focus:ring-pink-500 focus:border-pink-500 transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Rol de Acceso *</label>
                            <select name="role" class="w-full border-gray-200 rounded-lg focus:ring-pink-500 focus:border-pink-500 transition-all">
                                <option value="employee" {{ old('role', $professional->user?->role) == 'employee' ? 'selected' : '' }}>Empleado (Solo sus citas)</option>
                                <option value="admin" {{ old('role', $professional->user?->role) == 'admin' ? 'selected' : '' }}>Administrador (Control total)</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Contraseña {{ $professional->user ? '(Dejar vacío para mantener)' : '*' }}</label>
                            <input type="password" name="password" 
                                   class="w-full border-gray-200 rounded-lg focus:ring-pink-500 focus:border-pink-500 transition-all">
                            @if($professional->user)
                                <p class="text-[10px] text-pink-600 mt-1 italic">Si ingresas una contraseña nueva, se le enviará un correo con sus nuevos datos.</p>
                            @endif
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Confirmar Contraseña</label>
                            <input type="password" name="password_confirmation" 
                                   class="w-full border-gray-200 rounded-lg focus:ring-pink-500 focus:border-pink-500 transition-all">
                        </div>
                    </div>
                </div>
            </div>

            @if(!$professional->user)
                <script>
                    document.getElementById('create_user').addEventListener('change', function() {
                        const fields = document.getElementById('user_fields');
                        if (this.checked) {
                            fields.classList.remove('hidden');
                        } else {
                            fields.classList.add('hidden');
                        }
                    });
                </script>
            @endif

            <div class="flex justify-end pt-4">
                <button type="submit" class="bg-pink-600 text-white px-8 py-3 rounded-xl font-bold hover:bg-pink-700 transition shadow-lg transform hover:-translate-y-0.5">
                    Actualizar Profesional
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
