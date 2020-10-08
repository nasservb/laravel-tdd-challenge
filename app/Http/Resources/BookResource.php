<?php

declare (strict_types=1);


namespace App\Http\Resources;


use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'isbn' => $this->isbn,
            'title' => $this->title,
            'description' => $this->description,
            'authors' => AuthorResource::collection($this->authors),
            'review'=>
            [
                'avg' => (int) round($this->reviews->avg('review')),
                'count' => (int) $this->reviews->count(),
            ],
        ];
    }
}
