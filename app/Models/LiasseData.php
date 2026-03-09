<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiasseData extends Model
{
    use \App\Traits\BelongsToTenant;

    protected $table = 'liasse_data';

    protected $fillable = [
        'company_id',
        'exercice_id',
        'page_code',
        'field_code',
        'value',
    ];

    /**
     * L'exercice comptable associé.
     */
    public function exercice()
    {
        return $this->belongsTo(ExerciceComptable::class, 'exercice_id');
    }
}
