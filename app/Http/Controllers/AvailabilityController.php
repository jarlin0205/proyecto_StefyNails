<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $professionals = \App\Models\Professional::where('is_active', true)->get();
        
        // Determinar qué profesional ver
        $professionalId = $request->query('professional_id');
        
        if ($user->role === 'employee') {
            $professionalId = $user->professional->id ?? null;
        } elseif (!$professionalId && $professionals->count() > 0) {
            $professionalId = $professionals->first()->id;
        }

        $availabilities = $professionalId 
            ? \App\Models\Availability::where('professional_id', $professionalId)
                ->whereDate('date', '>=', now()->subMonths(1))
                ->orderBy('date', 'desc')
                ->get(['date', 'message'])
            : collect();
        
        return view('admin.availability.index', compact('availabilities', 'professionals', 'professionalId'));
    }

    public function show(Request $request)
    {
        $date = $request->query('date');
        $professionalId = $request->query('professional_id');
        
        if (auth()->user()->role === 'employee') {
            $professionalId = auth()->user()->professional->id ?? null;
        }

        $availability = \App\Models\Availability::whereDate('date', $date)
            ->where('professional_id', $professionalId)
            ->first();
        
        return response()->json([
            'exists' => !!$availability,
            'active_slots' => $availability ? $availability->active_slots : [],
            'message' => $availability ? $availability->message : null
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'active_slots' => 'present|array', 
            'message' => 'nullable|string',
            'professional_id' => 'required|exists:professionals,id'
        ]);

        $user = auth()->user();
        if ($user->role === 'employee' && $validated['professional_id'] != $user->professional->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $date = \Carbon\Carbon::parse($validated['date'])->format('Y-m-d');

        \App\Models\Availability::whereDate('date', $date)
            ->where('professional_id', $validated['professional_id'])
            ->delete();

        \App\Models\Availability::create([
            'date' => $date,
            'active_slots' => $validated['active_slots'],
            'message' => $validated['message'],
            'professional_id' => $validated['professional_id']
        ]);

        return response()->json(['success' => true]);
    }

    public function destroy(Request $request)
    {
        $date = $request->query('date');
        $professionalId = $request->query('professional_id');
        
        if(!$date || !$professionalId) return response()->json(['error' => 'Missing data'], 400);

        $user = auth()->user();
        if ($user->role === 'employee' && $professionalId != $user->professional->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        \App\Models\Availability::whereDate('date', $date)
            ->where('professional_id', $professionalId)
            ->delete();
        
        return response()->json(['success' => true]);
    }
}
