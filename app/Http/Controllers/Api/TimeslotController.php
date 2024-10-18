<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateTimeslotRequest;
use App\Http\Requests\UpdateTimeslotRequest;
use App\Models\Timeslot;
use App\Policies\TimeslotPolicy;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TimeslotController extends Controller
{
    use AuthorizesRequests;

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

        return response()->json($timeslots->isEmpty()
            ? ['message' => "All time slots are free."]
            : $timeslots->toArray(), 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateTimeslotRequest $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorised'], 401);
        }

        $start_time = Carbon::parse($request->start_time);
        $end_time = Carbon::parse($request->end_time);

        $this->authorize('create', [Timeslot::class, $start_time, $end_time]);

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

        $this->authorize('view', $timeslot);

        return response()->json($timeslot, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTimeslotRequest $request, string $id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(["message" => "Unauthorised"], 401);
        }

        $timeslot = Timeslot::where('id', $id)->where('user_id', $user->id)->first();

        if (!$timeslot) {
            return response()->json(['message' => "Timeslot with id: $id was not found or does not belong to you."], 404);
        }

        $start_time = Carbon::parse($request->start_time);
        $end_time = Carbon::parse($request->end_time);
        $this->authorize('update', [$timeslot, $start_time, $end_time]);

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

        $this->authorize('delete', $timeslot);
        $timeslot->delete();

        return response()->json(['message' => 'Timeslot was successfully deleted'], 200);
    }

    public function handleBatchRequests(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorised'], 401);
        }

        $responses = [];

        foreach ($request->requests as $req) {
            try {
                switch ($req['action']) {
                    case 'create':
                        $timeSlotRequest = new CreateTimeslotRequest($req['data']);
                        $response = $this->store($timeSlotRequest);
                        $responses[] = ['action' => 'create', 'response' => $response->getData()];
                        break;

                    case 'update':
                        $timeSlotRequest = new UpdateTimeslotRequest($req['data']);
                        $response = $this->update($timeSlotRequest, $req['id']);
                        $responses[] = ['action' => 'update', 'response' => $response->getData()];
                        break;

                    case 'delete':
                        $response = $this->destroy($req['id']);
                        $responses[] = ['action' => 'delete', 'response' => $response->getData()];
                        break;

                    default:
                        $responses[] = ['status' => 'error', 'message' => "Invalid action: {$req['action']}."];
                        break;
                }
            } catch (\Exception $e) {
                // Log the error for internal tracking
                \Log::error($e->getMessage());
                $responses[] = ['status' => 'error', 'message' => 'An error occurred while processing the request.'];
            }
        }

        return response()->json($responses, 200);
    }
}
