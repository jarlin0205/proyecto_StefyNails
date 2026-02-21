@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="flex items-center gap-4 mb-8">
            <a href="{{ route('admin.professionals.index') }}" class="text-gray-400 hover:text-pink-600 transition">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <h1 class="text-2xl font-bold text-gray-800">Nuevo Profesional</h1>
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

        <form action="{{ route('admin.professionals.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Nombre Completo *</label>
                        <input type="text" name="name" required value="{{ old('name') }}" 
                               class="w-full border-gray-200 rounded-lg focus:ring-pink-500 focus:border-pink-500 transition-all">
                    </div>
                    <div class="col-span-full">
                        <label class="block text-sm font-bold text-gray-700 mb-3">Especialidades (Categorías) *</label>
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                            @foreach($categories as $category)
                                <label class="flex items-center gap-2 p-3 border rounded-lg hover:bg-pink-50 cursor-pointer transition-colors border-gray-200">
                                    <input type="checkbox" name="categories[]" value="{{ $category->id }}"
                                           {{ is_array(old('categories')) && in_array($category->id, old('categories')) ? 'checked' : '' }}
                                           class="h-4 w-4 text-pink-600 focus:ring-pink-500 border-gray-300 rounded">
                                    <span class="text-sm font-medium text-gray-700">{{ $category->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="col-span-full">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Descripción Adicional de Especialidad (Opcional)</label>
                        <input type="text" name="specialty" value="{{ old('specialty') }}" placeholder="Ej: Especialista en diseño mano alzada"
                               class="w-full border-gray-200 rounded-lg focus:ring-pink-500 focus:border-pink-500 transition-all">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">WhatsApp de Contacto (Para el Bot) *</label>
                        <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" required 
                               class="w-full border-gray-200 rounded-lg focus:ring-pink-500 focus:border-transparent transition-all">
                        <input type="hidden" name="phone_full" id="phone_full" value="{{ old('phone_full') }}">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Foto de Perfil</label>
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
            </div>

            <div class="bg-pink-50 rounded-xl border border-pink-100 p-6 space-y-6">
                <div class="flex items-center gap-3 mb-4">
                    <input type="checkbox" name="create_user" id="create_user" value="1" {{ old('create_user') ? 'checked' : '' }}
                           class="h-5 w-5 text-pink-600 focus:ring-pink-500 border-pink-300 rounded transition-all">
                    <label for="create_user" class="text-sm font-bold text-pink-700 cursor-pointer">Crear cuenta de acceso para este profesional</label>
                </div>

                <div id="user_fields" class="{{ old('create_user') ? '' : 'hidden' }} space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Correo Electrónico (Login) *</label>
                            <input type="email" name="email" value="{{ old('email') }}" 
                                   class="w-full border-gray-200 rounded-lg focus:ring-pink-500 focus:border-pink-500 transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Rol de Acceso *</label>
                            <select name="role" class="w-full border-gray-200 rounded-lg focus:ring-pink-500 focus:border-pink-500 transition-all">
                                <option value="employee" {{ old('role') == 'employee' ? 'selected' : '' }}>Empleado (Solo sus citas)</option>
                                <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Administrador (Control total)</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Contraseña *</label>
                            <input type="password" name="password" 
                                   class="w-full border-gray-200 rounded-lg focus:ring-pink-500 focus:border-pink-500 transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Confirmar Contraseña *</label>
                            <input type="password" name="password_confirmation" 
                                   class="w-full border-gray-200 rounded-lg focus:ring-pink-500 focus:border-pink-500 transition-all">
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-4">
                <button type="submit" class="bg-pink-600 text-white px-8 py-3 rounded-xl font-bold hover:bg-pink-700 transition shadow-lg transform hover:-translate-y-0.5">
                    Guardar Profesional
                </button>
            </div>
        </form>
    </div>
</div>

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
@endsection
