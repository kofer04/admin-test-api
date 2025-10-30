<?php

namespace App\Http\Resources\Reports;

use Illuminate\Http\Resources\Json\JsonResource;

class JobBookingsChartResource extends JsonResource
{
    public function toArray($request): array
    {
        // Return flat array directly - no transformation needed
        return $this->resource;
    }
}
