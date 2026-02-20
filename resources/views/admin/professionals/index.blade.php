@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Gestionar Profesionales</h1>
        <a href="{{ route('admin.professionals.create') }}" class="bg-pink-600 text-white px-4 py-2 rounded-lg hover:bg-pink-700 transition shadow-md">
            <i class="fas fa-plus mr-2"></i> Nuevo Profesional
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Profesional</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Especialidad</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Contacto / Usuario</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($professionals as $p)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        @if($p->photo_path)
                                            <img class="h-10 w-10 rounded-full object-cover border-2 border-pink-100" src="{{ asset('storage/' . $p->photo_path) }}" alt="{{ $p->name }}">
                                        @else
                                            <div class="h-10 w-10 rounded-full bg-pink-100 flex items-center justify-center text-pink-600 font-bold">
                                                {{ substr($p->name, 0, 1) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-bold text-gray-900">{{ $p->name }}</div>
                                        <div class="text-xs text-gray-500">ID: #{{ $p->id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-normal">
                                <div class="flex flex-wrap gap-1 mb-1">
                                    @forelse($p->categories as $category)
                                        <span class="px-2 py-0.5 text-[10px] font-bold bg-pink-100 text-pink-700 rounded-full">
                                            {{ $category->name }}
                                        </span>
                                    @empty
                                        <span class="px-2 py-0.5 text-[10px] font-bold bg-gray-100 text-gray-500 rounded-full">
                                            General
                                        </span>
                                    @endforelse
                                </div>
                                @if($p->specialty)
                                    <div class="text-[10px] text-gray-400 italic leading-tight">{{ $p->specialty }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $p->phone ?: 'Sin teléfono' }}</div>
                                @if($p->user)
                                    <div class="text-xs text-pink-600 font-medium italic">{{ $p->user->email }}</div>
                                @else
                                    <div class="text-xs text-gray-400">Sin cuenta de usuario</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($p->is_active)
                                    <span class="px-2 py-1 text-xs font-bold bg-green-100 text-green-700 rounded-full">Activo</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-bold bg-red-100 text-red-700 rounded-full">Inactivo</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.professionals.edit', $p->id) }}" class="bg-blue-50 text-blue-600 hover:bg-blue-100 p-2.5 rounded-lg transition-all shadow-sm border border-blue-100 flex items-center gap-2" title="Editar Perfil">
                                        <i class="fas fa-edit text-lg"></i>
                                        <span class="font-bold">Gestionar</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                <i class="fas fa-user-tie text-4xl mb-4 block text-gray-300"></i>
                                <p>No hay profesionales registrados aún.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
