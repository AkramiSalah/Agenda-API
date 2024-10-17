<?php

namespace App\Policies;

use App\Models\Timeslot;
use App\Models\User;
use Carbon\Carbon;
use Date;
use Illuminate\Auth\Access\Response;

class TimeslotPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Timeslot $timeslot): Response
    {
        return $user->id === $timeslot->user_id
            ? Response::allow()
            : Response::deny('You do not own this timeslot.');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Carbon $start_time, Carbon $end_time): Response
    {
        if ($start_time->isAfter($end_time)) {
            return Response::deny('Start time must be before end time.');
        }

        $collidingTimeslots = Timeslot::where('user_id', $user->id)
            ->where(function ($query) use ($start_time, $end_time) {
                $query->whereBetween('start_time', [$start_time, $end_time])
                    ->orWhereBetween('end_time', [$start_time, $end_time]);
            })
            ->exists();

        return $collidingTimeslots ? Response::deny('This timeslot collides with an existing one.') : Response::allow();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Timeslot $timeslot, Carbon $start_time, Carbon $end_time): Response
    {
        if ($start_time->isAfter($end_time)) {
            return Response::deny('Start time must be before end time.');
        }

        if ($user->id !== $timeslot->user_id) {
            return Response::deny('You do not own this timeslot.');
        }

        $collidingTimeslots = Timeslot::where('user_id', $user->id)
            ->where('id', '!=', $timeslot->id) // Exclude the current timeslot
            ->where(function ($query) use ($start_time, $end_time) {
                $query->whereBetween('start_time', [$start_time, $end_time])
                    ->orWhereBetween('end_time', [$start_time, $end_time]);
            })
            ->exists();

        return $collidingTimeslots ? Response::deny('This timeslot collides with an existing one.') : Response::allow();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Timeslot $timeslot): Response
    {
        return $user->id === $timeslot->user_id
            ? Response::allow()
            : Response::deny('You do not own this timeslot.');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Timeslot $timeslot): Response
    {
        return $user->id === $timeslot->user_id
            ? Response::allow()
            : Response::deny('You do not own this timeslot.');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Timeslot $timeslot): Response
    {
        return $user->id === $timeslot->user_id
            ? Response::allow()
            : Response::deny('You do not own this timeslot.');
    }
}
