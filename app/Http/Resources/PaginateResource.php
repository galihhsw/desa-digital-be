<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

class PaginateResource extends JsonResource
{
    /**
     * create a new resource instance
     * 
     * @param mixed $resource
     * @return void
     */

    public function __construct($resource, public $resourceClass = null) 
    {
        parent::__construct($resource);
    }

    public function collect($resource)
    {
        return $this->resourceClass::collection($resource);
    }

    /**
     * transform resource into Array
     * 
     * @param \Illuminate\Http\Request $request
     * @return array |\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */

    #[Override]
    public function toArray($request)
    {
        return [
            'data' => $this->collect($this->items()),
            'meta' => [
                'current_page' => $this->currentPage(),
                'from' => $this->firstItem(),
                'last_page' => $this->lastPage(),
                'path' => $this->path(),
                'per_page' => $this->perPage(),
                'to' => $this->lastItem(),
                'total' => $this->total(),
            ],
        ];
    }
}