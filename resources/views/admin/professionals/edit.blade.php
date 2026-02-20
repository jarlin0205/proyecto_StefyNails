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
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Especialidad</label>
                        <input type="text" name="specialty" value="{{ old('specialty', $professional->specialty) }}" placeholder="Ej: Manicurista, Pedicurista"
                               class="w-full border-gray-200 rounded-lg focus:ring-pink-500 focus:border-pink-500 transition-all">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Teléfono de Contacto</label>
                        <input type="text" name="phone" value="{{ old('phone', $professional->phone) }}" 
                               class="w-full border-gray-200 rounded-lg focus:ring-pink-500 focus:border-pink-500 transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Cambiar Foto</label>
                        <input type="file" name="photo" accept="image/*"
                               class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-pink-50 file:text-pink-700 hover:file:bg-pink-100 transition-all">
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-4 border-t border-gray-50">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" {{ $professional->is_active ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-pink-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-pink-600"></div>
                        <span class="ml-3 text-sm font-bold text-gray-700">Profesional Activo</span>
                    </label>
                </div>
            </div>

            @if($professional->user)
                <div class="bg-gray-50 rounded-xl border border-gray-200 p-6">
                    <h3 class="text-sm font-bold text-gray-700 mb-2 flex items-center">
                        <i class="fas fa-user-lock mr-2 text-gray-400"></i> Cuenta de Usuario Vinculada
                    </h3>
                    <p class="text-sm text-gray-600">Este profesional tiene acceso con el correo: <span class="font-bold">{{ $professional->user->email }}</span></p>
                    <p class="text-xs text-gray-400 mt-2 italic">Para cambiar la contraseña, usa el módulo de usuarios del sistema.</p>
                </div>
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
