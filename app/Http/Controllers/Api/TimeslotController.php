<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Timeslot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PHPUnit\Framework\Constraint\IsEmpty;

class TimeslotController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorised'], 401);
        }

        $timeslots = Timeslot::where("user_id", $user->id)->get();
        if ($timeslots->isEmpty()) {
            return response()->json(['message' => "All time slots are free."], 200);
        }

        return response()->json($timeslots->toArray(), 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorised'], 401);
        }

        $request->validate([
            "agenda" => "required|string",
            "tag" => "string|nullable",
            "start_time" => "required|date",
            "end_time" => "required|date|after:start_time",
        ], [
            'agenda.required' => 'An agenda is required.',
            'end_time.after' => 'End time must be after start time.',
        ]);

        $timeslot = Timeslot::create([
            "user_id" => $user->id,
            "agenda" => $request->agenda,
            "tag" => $request->tag,
            "start_time" => $request->start_time,
            "end_time" => $request->end_time
        ]);

        return response()->json($timeslot, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorised'], 401);
        }

        $timeslot = Timeslot::where('id', $id)->where('user_id', $user->id)->first();

        if (!$timeslot) {
            return response()->json(['message' => "Timeslot with id: $id was not found or does not belong to you."], 404);
        }

        return response()->json($timeslot, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(["message" => "Unauthorised"], 401);
        }

        $timeslot = Timeslot::where('id', $id)->where('user_id', $user->id)->first();

        if (!$timeslot) {
            return response()->json(['message' => "Timeslot with id: $id was not found or does not belong to you."], 404);
        }

        $request->validate([
            "agenda" => "required|string",
            "tag" => "string|nullable",
            "start_time" => "required|date",
            "end_time" => "required|date|after:start_time",
        ]);

        $timeslot->update([
            "agenda" => $request->agenda,
            "tag" => $request->tag,
            "start_time" => $request->start_time,
            "end_time" => $request->end_time
        ]);

        return response()->json($timeslot, 200);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(["message" => "Unauthorised"], 401);
        }

        $timeslot = Timeslot::where('id', $id)->where('user_id', $user->id)->first();

        if (!$timeslot) {
            return response()->json(['message' => "Timeslot with id: $id was not found or does not belong to you."], 404);
        }

        $timeslot->delete();
        return response()->json(['message' => 'Timeslot was successfully deleted'], 200);
    }
}
