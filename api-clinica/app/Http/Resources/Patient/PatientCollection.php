<?php

namespace App\Http\Resources\Patient;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PatientCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Retorna una colección de recursos de pacientes, cada uno transformado por la clase PatientResource
        return [
            "data" => PatientResource::collection($this->collection),
        ];
    }
}
