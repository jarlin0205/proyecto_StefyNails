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
        $professionals = Professional::with(['user', 'categories'])->get();
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
            'phone_full' => 'nullable|string|max:25',
            'photo' => 'nullable|image|max:2048',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'create_user' => 'boolean',
            'email' => [
                'required_if:create_user,1',
                'nullable',
                'email',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        // Verificar si ya existe un profesional con este email
                        $exists = Professional::whereHas('user', function($q) use ($value) {
                            $q->where('email', $value);
                        })->exists();
                        if ($exists) {
                            $fail('Este correo ya está asignado a otro profesional.');
                        }
                    }
                },
            ],
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
                // Si el usuario ya existe, verificar que no esté asociado a OTRO profesional
                $isUsedByOther = Professional::where('user_id', $user->id)->exists();
                if ($isUsedByOther) {
                    return back()->withErrors(['email' => 'Este usuario ya está vinculado a otro profesional.'])->withInput();
                }

                // Si ya existe pero está libre, opcionalmente actualizamos su rol
                if (isset($validated['role'])) {
                    $user->update(['role' => $validated['role']]);
                }
            }
        }

        $professional = Professional::create([
            'user_id' => $user ? $user->id : null,
            'name' => $validated['name'],
            'specialty' => $validated['specialty'],
            'phone' => $validated['phone_full'] ?? $validated['phone'],
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
            'phone_full' => 'nullable|string|max:25',
            'photo' => 'nullable|image|max:2048',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'is_active' => 'boolean',
            'create_user' => 'boolean',
            'email' => [
                'required_if:create_user,1',
                'nullable',
                'email',
                function ($attribute, $value, $fail) use ($professional) {
                    if ($value) {
                        // Verificar si ya existe otro profesional con este email
                        $exists = Professional::where('id', '!=', $professional->id)
                            ->whereHas('user', function($q) use ($value) {
                                $q->where('email', $value);
                            })->exists();
                        if ($exists) {
                            $fail('Este correo ya está asignado a otro profesional.');
                        }
                    }
                },
            ],
            'password' => 'required_if:create_user,1|nullable|min:8|confirmed',
            'role' => 'required_if:create_user,1|nullable|in:admin,employee',
        ]);

        if ($request->hasFile('photo')) {
            if ($professional->photo_path) {
                Storage::disk('public')->delete($professional->photo_path);
            }
            $professional->photo_path = $request->file('photo')->store('professionals', 'public');
        }

        // Crear o actualizar cuenta de acceso
        if ($request->create_user || $professional->user_id) {
            $user = $professional->user;
            
            if (!$user) {
                // ... (igual que store) ...
                $user = User::where('email', $validated['email'])->first();
                if (!$user) {
                    $user = User::create([
                        'name' => $validated['name'],
                        'email' => $validated['email'],
                        'password' => Hash::make($validated['password']),
                        'role' => $validated['role'] ?? 'employee',
                    ]);
                    try {
                        Mail::to($user->email)->send(new WelcomeProfessional($user, $request->password));
                    } catch (\Exception $e) {}
                }
                $professional->user_id = $user->id;
            } else {
                // Verificar si el User es compartido por varios profesionales (el bug que reportas)
                $isShared = Professional::where('user_id', $user->id)->count() > 1;

                if ($isShared && $user->email !== $validated['email']) {
                    // Si es compartido y estamos cambiando el correo, NO actualizamos el User común.
                    // Creamos uno nuevo para este profesional específico para "romper el vínculo".
                    $newUser = User::create([
                        'name' => $validated['name'],
                        'email' => $validated['email'],
                        'password' => !empty($validated['password']) ? Hash::make($validated['password']) : $user->password,
                        'role' => $validated['role'] ?? $user->role,
                    ]);
                    $professional->user_id = $newUser->id;
                    $professional->save(); // Guardar el cambio de user_id de inmediato
                } else {
                    // Actualizar usuario existente (si es único o si el email es el mismo)
                    $userUpdate = ['name' => $validated['name'], 'email' => $validated['email']];
                    if (isset($validated['role'])) $userUpdate['role'] = $validated['role'];
                    if (!empty($validated['password'])) $userUpdate['password'] = Hash::make($validated['password']);
                    $user->update($userUpdate);
                }
            }
        }

        $professional->update([
            'name' => $validated['name'],
            'specialty' => $validated['specialty'],
            'phone' => $validated['phone_full'] ?? $validated['phone'],
            'is_active' => $request->has('is_active'),
        ]);

        $professional->categories()->sync($request->categories ?? []);

        return redirect()->route('admin.professionals.index')->with('success', 'Profesional actualizado.');
    }

    public function destroy(Professional $professional)
    {
        $user = $professional->user;

        // Si el profesional tiene citas, no lo eliminamos físicamente para mantener integridad, 
        // pero sí le quitamos el acceso al sistema eliminando su User.
        if ($professional->appointments()->count() > 0) {
            $professional->update(['is_active' => false, 'user_id' => null]);
            
            if ($user) {
                $user->delete();
            }

            return back()->with('success', 'El profesional ha sido desactivado y su acceso revocado porque tiene citas registradas.');
        }

        // Solo borrar al usuario si no lo está usando ningún otro profesional activo
        if ($user) {
            $otherUsersCount = Professional::where('user_id', $user->id)->where('id', '!=', $professional->id)->count();
            if ($otherUsersCount === 0) {
                $user->delete();
            }
        }

        $professional->delete();
        return redirect()->route('admin.professionals.index')->with('success', 'Profesional y su cuenta de acceso eliminados correctamente.');
    }
}
