<?php

namespace App\Http\Controllers\Super;

use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SuperAdminPayloadController extends Controller
{
    private const FICHIERS = [
        'referentiel' => '01_REFERENTIEL.txt',
        'ecritures' => '02_ECRITURES_COMPTABLES.txt',
        'pieces' => '03_PIECES_JUSTIFICATIVES.txt',
        'liaison' => '04_LIAISON_COMPTA_APK.txt',
    ];

    public function index()
    {
        return view('superadmin.payloads');
    }

    public function telecharger(string $type): BinaryFileResponse
    {
        abort_unless(isset(self::FICHIERS[$type]), 404);
        $path = base_path('docs/integrations/' . self::FICHIERS[$type]);
        abort_unless(is_file($path), 404);

        return response()->download($path, self::FICHIERS[$type], [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
