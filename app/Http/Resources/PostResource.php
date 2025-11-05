<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
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
            'text' => $this->text,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
            'author' => new UserResource($this->author),
            'currentUserReaction' => new ReactionResource($this->currentUserReaction),
            'reactionTypeCounts' => $this->getReactionTypeCounts(),
            'commentsCount' => $this->comments->count()
        ];
    }

    protected function getReactionTypeCounts()
    {
        return $this->reactions()
            ->join('reaction_types', 'reactions.type_id', 'reaction_types.id')
            ->groupBy('name')
            ->select('name')
            ->selectRaw('COUNT(*) as count')
            ->pluck('count', 'name')
            ->toArray();
    }
}
