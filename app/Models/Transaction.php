<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    /**
     * The data type of the primary key.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'compte_id',
        'type',
        'montant',
        'devise',
        'description',
        'compte_destination_id',
        'statut',
        'metadata',
        'date_execution',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'montant' => 'decimal:2',
        'metadata' => 'array',
        'date_execution' => 'datetime',
    ];

    /**
     * Get the compte that owns the transaction.
     */
    public function compte(): BelongsTo
    {
        return $this->belongsTo(Compte::class);
    }

    /**
     * Get the destination compte for this transaction (for transfers).
     */
    public function compteDestination(): BelongsTo
    {
        return $this->belongsTo(Compte::class, 'compte_destination_id');
    }
}
