<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CustomerCollection extends ResourceCollection
{
    public $collects = CustomerResource::class;

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
