<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'numeroCompte' => $this->numeroCompte,
            'titulaire' => $this->client->titulaire ?? null,
            'type' => $this->type,
            'solde' => (float) $this->solde,
            'devise' => $this->devise,
            'dateCreation' => $this->created_at->toISOString(),
            'statut' => $this->statut,
            'motifBlocage' => $this->when($this->statut === 'bloque', $this->metadata['motifBlocage'] ?? null),
            'metadata' => [
                'derniereModification' => $this->updated_at->toISOString(),
                'version' => 1
            ]
        ];
    }
}
