<?php

namespace App\Http\Controllers;

use App\Models\Professional;
use App\Models\User;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeProfessional;

class ProfessionalController extends Controller
{
    public function index()
    {
        $professionals = Professional::with('user')->get();
        return view('admin.professionals.index', compact('professionals'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('admin.professionals.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'specialty' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'photo' => 'nullable|image|max:2048',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'create_user' => 'boolean',
            'email' => 'required_if:create_user,1|nullable|email', // Eliminamos unique para manejarlo nosotros
            'password' => 'required_if:create_user,1|nullable|min:8|confirmed',
            'role' => 'required_if:create_user,1|nullable|in:admin,employee',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('professionals', 'public');
        }

        $user = null;
        if ($request->create_user) {
            // Buscar si el usuario ya existe
            $user = User::where('email', $validated['email'])->first();

            if (!$user) {
                // Si no existe, lo creamos
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'role' => $validated['role'] ?? 'employee',
                ]);

                // Enviar correo de bienvenida solo si es nuevo
                try {
                    Mail::to($user->email)->send(new WelcomeProfessional($user, $request->password));
                } catch (\Exception $e) {
                    \Log::error('Error enviando correo a profesional: ' . $e->getMessage());
                }
            } else {
                // Si ya existe, opcionalmente actualizamos su rol si se seleccionó uno
                if (isset($validated['role'])) {
                    $user->update(['role' => $validated['role']]);
                }
            }
        }

        $professional = Professional::create([
            'user_id' => $user ? $user->id : null,
            'name' => $validated['name'],
            'specialty' => $validated['specialty'],
            'phone' => $validated['phone'],
            'photo_path' => $photoPath,
            'is_active' => true,
        ]);

        if ($request->has('categories')) {
            $professional->categories()->sync($validated['categories']);
        }

        return redirect()->route('admin.professionals.index')->with('success', 'Profesional creado correctamente.');
    }

    public function edit(Professional $professional)
    {
        $categories = Category::all();
        return view('admin.professionals.edit', compact('professional', 'categories'));
    }

    public function update(Request $request, Professional $professional)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'specialty' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'photo' => 'nullable|image|max:2048',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'is_active' => 'boolean',
            'create_user' => 'boolean',
            'email' => 'required_if:create_user,1|nullable|email',
            'password' => 'required_if:create_user,1|nullable|min:8|confirmed',
            'role' => 'required_if:create_user,1|nullable|in:admin,employee',
        ]);

        if ($request->hasFile('photo')) {
            if ($professional->photo_path) {
                Storage::disk('public')->delete($professional->photo_path);
            }
            $professional->photo_path = $request->file('photo')->store('professionals', 'public');
        }

        // Crear cuenta de acceso si se solicita y no tiene una
        if ($request->create_user && !$professional->user_id) {
            // Buscar si el usuario ya existe
            $user = User::where('email', $validated['email'])->first();

            if (!$user) {
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'role' => $validated['role'] ?? 'employee',
                ]);

                // Enviar correo de bienvenida solo si es nuevo
                try {
                    Mail::to($user->email)->send(new WelcomeProfessional($user, $request->password));
                } catch (\Exception $e) {
                    \Log::error('Error enviando correo a profesional (desde update): ' . $e->getMessage());
                }
            } else {
                // Si existe, actualizamos su rol si se seleccionó uno
                if (isset($validated['role'])) {
                    $user->update(['role' => $validated['role']]);
                }
            }

            $professional->user_id = $user->id;
        }

        $professional->update([
            'name' => $validated['name'],
            'specialty' => $validated['specialty'],
            'phone' => $validated['phone'],
            'is_active' => $request->has('is_active'),
        ]);

        $professional->categories()->sync($request->categories ?? []);

        return redirect()->route('admin.professionals.index')->with('success', 'Profesional actualizado.');
    }

    public function destroy(Professional $professional)
    {
        // No eliminamos físicamente para mantener integridad de citas, solo desactivamos o lanzamos error si tiene citas
        if ($professional->appointments()->count() > 0) {
            $professional->update(['is_active' => false]);
            return back()->with('success', 'El profesional ha sido desactivado porque tiene citas registradas.');
        }

        if ($professional->photo_path) {
            Storage::disk('public')->delete($professional->photo_path);
        }
        
        $professional->delete();
        return redirect()->route('admin.professionals.index')->with('success', 'Profesional eliminado.');
    }
}
