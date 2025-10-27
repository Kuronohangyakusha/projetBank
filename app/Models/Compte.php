<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Compte extends Model
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
        'client_id',
        'numeroCompte',
        'type',
        'devise',
        'statut',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Get the client that owns the compte.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the transactions for the compte.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get the transactions where this compte is the destination.
     */
    public function transactionsEntrantes(): HasMany
    {
        return $this->hasMany(Transaction::class, 'compte_destination_id');
    }

    /**
     * Set the numeroCompte attribute with automatic generation.
     */
    public function setNumeroCompteAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['numeroCompte'] = 'CP' . str_pad(rand(1, 99999999), 8, '0', STR_PAD_LEFT);
        } else {
            $this->attributes['numeroCompte'] = $value;
        }
    }

    /**
     * Get the calculated solde based on transactions.
     */
    public function getSoldeAttribute()
    {
        // Calculer le solde à partir des transactions
        $entrees = $this->transactions()
            ->where('type', 'depot')
            ->sum('montant');

        $sorties = $this->transactions()
            ->whereIn('type', ['retrait', 'virement'])
            ->sum('montant');

        $entreesVirement = $this->transactionsEntrantes()
            ->where('type', 'virement')
            ->sum('montant');

        return ($entrees + $entreesVirement) - $sorties;
    }

    /**
     * Scope to filter by numero
     */
    public function scopeNumero($query, $numero)
    {
        return $query->where('numeroCompte', $numero);
    }

    /**
     * Scope to filter by client telephone
     */
    public function scopeClient($query, $telephone)
    {
        return $query->whereHas('client', function ($q) use ($telephone) {
            $q->where('telephone', $telephone);
        });
    }

    /**
     * Global scope to exclude soft deleted records
     */
    protected static function booted()
    {
        static::addGlobalScope('notDeleted', function ($builder) {
            // Si on utilise soft deletes plus tard
            // $builder->whereNull('deleted_at');
        });
    }
}
