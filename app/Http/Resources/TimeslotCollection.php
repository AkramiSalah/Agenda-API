<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TimeslotCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->collection->map(function ($timeslot) {
            return [
                "agenda" => $timeslot->agenda,
                "tag" => $timeslot->tag,
                "start_time" => $timeslot->start_time,
                "end_time" => $timeslot->end_time,
            ];
        })->toArray();
    }
}
