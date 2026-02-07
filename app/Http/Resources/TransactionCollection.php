<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TransactionCollection extends ResourceCollection
{
    /**
     * The resource that this resource collects.
     */
    public $collects = TransactionResource::class;

    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'totalCount' => $this->resource->total(),
            'page' => $this->resource->currentPage(),
            'size' => $this->resource->perPage(),
            'lastPage' => $this->resource->lastPage(),
        ];
    }
}
