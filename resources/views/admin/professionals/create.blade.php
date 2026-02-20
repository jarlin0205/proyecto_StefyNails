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
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Especialidad</label>
                        <input type="text" name="specialty" value="{{ old('specialty') }}" placeholder="Ej: Manicurista, Pedicurista"
                               class="w-full border-gray-200 rounded-lg focus:ring-pink-500 focus:border-pink-500 transition-all">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Teléfono de Contacto</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" 
                               class="w-full border-gray-200 rounded-lg focus:ring-pink-500 focus:border-pink-500 transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Foto de Perfil</label>
                        <input type="file" name="photo" accept="image/*"
                               class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-pink-50 file:text-pink-700 hover:file:bg-pink-100 transition-all">
                    </div>
                </div>
            </div>

            <div class="bg-pink-50 rounded-xl border border-pink-100 p-6 space-y-6">
                <div class="flex items-center gap-3 mb-4">
                    <input type="checkbox" name="create_user" id="create_user" value="1" {{ old('create_user') ? 'checked' : '' }}
                           class="h-5 w-5 text-pink-600 focus:ring-pink-500 border-pink-300 rounded transition-all">
                    <label for="create_user" class="text-sm font-bold text-pink-700 cursor-pointer">Crear cuenta de acceso para este profesional</label>
                </div>

                <div id="user_fields" class="{{ old('create_user') ? '' : 'hidden' }} space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Correo Electrónico (Login) *</label>
                        <input type="email" name="email" value="{{ old('email') }}" 
                               class="w-full border-gray-200 rounded-lg focus:ring-pink-500 focus:border-pink-500 transition-all">
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
