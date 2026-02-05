<?php
foreach(App\Models\Company::all() as $c) {
    echo 'ID:'.$c->id.' Name:'.$c->company_name.' Parent:'.($c->parent_company_id??'None').' PlanCount:'.App\Models\PlanComptable::where('company_id', $c->id)->count().PHP_EOL;
}
