<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperationsTresoreries extends Model
{
    use HasFactory;

    protected $fillable = [
         'tresorerie_id',
        'date_operation',
        'libelle',
        'montant',
        'type_operation',
        'mode_paiement',
        'reference_piece',
        'plan_comptable_id',
        'flux_type_id',
        'code_journal_id',
        'user_id',
        'company_id',
    ];
}
