<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExcelIaProjetFichier extends Model
{
    use HasFactory;

    protected $table = 'excel_ia_projet_fichiers';

    protected $fillable = [
        'projet_id',
        'nom',
        'chemin',
        'mime',
        'taille',
    ];

    public function projet()
    {
        return $this->belongsTo(ExcelIaProjet::class, 'projet_id');
    }

    public function scopeImages($query)
    {
        return $query->where('mime', 'like', 'image/%');
    }

    public function scopePdfs($query)
    {
        return $query->where('mime', 'application/pdf');
    }
}
