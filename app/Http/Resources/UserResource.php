<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'name' => $this->name,
            'surname' => $this->surname,
            'job' => $this->job,
            'profilePicture' => $this->profile_picture ? route('profile_picture', ['user' => $this->id]) : null,
            'disabled' => $this->disabled,
            'organizationId' => $this->organization_id,
            'role' => $this->role
        ];
    }
}
