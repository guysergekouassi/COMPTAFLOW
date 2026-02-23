<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IaLog extends Model
{
    protected $fillable = [
        'company_id',
        'user_id',
        'image_hash',
        'image_nom',
        'prompt_tokens',
        'response_tokens',
        'json_brut',
        'json_final',
        'status',
        'erreur_message',
        'taux_correction',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
