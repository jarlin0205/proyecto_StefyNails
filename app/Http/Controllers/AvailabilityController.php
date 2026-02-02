<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
    public function index()
    {
        // Show only future or recent (last 30 days?) custom days. Let's do today onwards + recent history.
        // User asked for "really the days I customized", implies they see too many?
        // Let's show all for now but sorted. Or better: All future + recent history.
        $availabilities = \App\Models\Availability::whereDate('date', '>=', now()->subMonths(1)) // ample history
            ->orderBy('date', 'desc')
            ->get(['date', 'message']);
        
        return view('admin.availability.index', compact('availabilities'));
    }

    public function show(Request $request)
    {
        $date = $request->query('date');
        $availability = \App\Models\Availability::whereDate('date', $date)->first();
        
        return response()->json([
            'exists' => !!$availability,
            'active_slots' => $availability ? $availability->active_slots : [], // If null, means full day? No, default is empty if no record
            'message' => $availability ? $availability->message : null
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'active_slots' => 'present|array', 
            'message' => 'nullable|string'
        ]);

        $date = \Carbon\Carbon::parse($validated['date'])->format('Y-m-d');

        // Delete any existing configuration for this date (cleans up duplicates)
        \App\Models\Availability::whereDate('date', $date)->delete();

        \App\Models\Availability::create([
            'date' => $date,
            'active_slots' => $validated['active_slots'],
            'message' => $validated['message']
        ]);

        return response()->json(['success' => true]);
    }

    public function destroy(Request $request)
    {
        $date = $request->query('date');
        if(!$date) return response()->json(['error' => 'Date required'], 400);

        \App\Models\Availability::whereDate('date', $date)->delete();
        
        return response()->json(['success' => true]);
    }
}
