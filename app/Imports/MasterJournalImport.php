<?php

namespace App\Imports;

use App\Models\CodeJournal;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MasterJournalImport implements ToModel, WithHeadingRow
{
    protected $userId;
    protected $companyId;

    public function __construct()
    {
        $user = Auth::user();
        $this->userId = $user->id;
        $this->companyId = $user->company_id;
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        if (empty($row['code_journal']) || empty($row['intitule']) || empty($row['type'])) {
            return null;
        }

        $code = strtoupper($row['code_journal']);

        $exists = CodeJournal::where('company_id', $this->companyId)
            ->where('code_journal', $code)
            ->exists();

        if ($exists) {
            return null;
        }

        return new CodeJournal([
            'code_journal' => $code,
            'intitule'     => strtoupper($row['intitule']),
            'type'         => $row['type'],
            'user_id'      => $this->userId,
            'company_id'   => $this->companyId,
        ]);
    }
}
