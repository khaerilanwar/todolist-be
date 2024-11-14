<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "title" => $this->title,
            "description" => $this->description,
            "completed" => $this->completed,
            "user_id" => $this->user_id,
            "due_date" => Carbon::parse($this->due_date)->isoFormat('DD MMMM YYYY HH:mm'),
            "created_at" => Carbon::parse($this->created_at)->isoFormat('DD MMMM YYYY HH:mm')
        ];

        // return parent::toArray($request);
    }
}
