<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        .label-col { text-align: left; }
        .section-header { font-weight: bold; background-color: #f0f0f0; }
        .total-row { font-weight: bold; background-color: #e0e0e0; }
        .main-total { font-weight: bold; background-color: #000; color: #fff; }
        .detail-row { font-style: italic; color: #555; }
        th { background-color: #eee; font-weight: bold; border: 1px solid #000; }
        td { border: 1px solid #ccc; }
    </style>
</head>
<body>
@php
    // ── Comparatif N-1 & restriction de période (filtres de téléchargement) ──
    $dataN1     = $dataN1     ?? null;
    $exerciceN1 = $exerciceN1 ?? null;
    $cmp        = !is_null($dataN1);
    $detailed   = $detailed   ?? false;

    $monthFrom = $monthFrom ?? 1;
    $monthTo   = $monthTo   ?? count($data['months']);

    $sliceMonths = function ($months) use ($monthFrom, $monthTo) {
        $out = [];
        foreach (array_values($months ?? []) as $i => $m) {
            if ($i + 1 >= $monthFrom && $i + 1 <= $monthTo) {
                $out[$i] = $m;
            }
        }
        return $out ?: ($months ?? []);
    };

    $visibleMonths = $sliceMonths($data['months']);
@endphp
    @include('reporting.excel.partials.monthly_resultat_matrix', ['data' => $data, 'exercice' => $exercice, 'visibleMonths' => $visibleMonths, 'detailed' => $detailed])

    @if($cmp)
        <table><tr><td></td></tr></table>
        @include('reporting.excel.partials.monthly_resultat_matrix', ['data' => $dataN1, 'exercice' => $exerciceN1 ?? $exercice, 'visibleMonths' => $sliceMonths($dataN1['months'] ?? []), 'detailed' => $detailed])
    @endif
</body>
</html>
