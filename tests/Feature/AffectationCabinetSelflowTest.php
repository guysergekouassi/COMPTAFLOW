<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * 24/11/2026 : Selflow liste les cabinets et y affecte le dossier qu'il a
 * ouvert, au cabinet (son espace) ou à un membre précis du cabinet.
 */
class AffectationCabinetSelflowTest extends TestCase
{
    private const SECRET = 'secret-serveur-de-test';

    protected function setUp(): void
    {
        parent::setUp();
        config(['external_sync.external_sync_secret' => self::SECRET]);

        foreach (['company_user', 'cabinet_user', 'cabinets', 'companies', 'users'] as $t) {
            Schema::dropIfExists($t);
        }
        Schema::create('users', function (Blueprint $t) {
            $t->id(); $t->string('name')->nullable(); $t->string('last_name')->nullable(); $t->string('email_adresse')->nullable();
            $t->unsignedBigInteger('company_id')->nullable(); $t->string('role')->nullable(); $t->string('password')->nullable(); $t->timestamps();
        });
        Schema::create('companies', function (Blueprint $t) {
            $t->id(); $t->string('company_name')->nullable(); $t->unsignedBigInteger('user_id')->nullable();
            $t->unsignedBigInteger('cabinet_id')->nullable(); $t->unsignedBigInteger('selflow_company_id')->nullable(); $t->timestamps();
        });
        Schema::create('cabinets', function (Blueprint $t) {
            $t->id(); $t->string('nom'); $t->string('code')->nullable(); $t->unsignedBigInteger('user_id'); $t->boolean('is_active')->default(true); $t->timestamps();
        });
        Schema::create('cabinet_user', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('cabinet_id'); $t->unsignedBigInteger('user_id'); $t->string('role')->default('collaborateur'); $t->timestamps();
        });
        Schema::create('company_user', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('company_id'); $t->unsignedBigInteger('user_id'); $t->string('role')->default('comptable'); $t->timestamps();
        });

        DB::table('users')->insert([
            ['id' => 40, 'name' => 'Yao', 'last_name' => 'Koffi', 'email_adresse' => 'yao@cabinet.ci'],
            ['id' => 41, 'name' => 'Ble', 'last_name' => 'Ines', 'email_adresse' => 'ines@cabinet.ci'],
            ['id' => 50, 'name' => 'Client', 'last_name' => 'SAS', 'email_adresse' => 'client@sast.ci'],
        ]);
        DB::table('cabinets')->insert(['id' => 7, 'nom' => 'Cabinet Yao', 'code' => 'CAB-YAO-ABC123', 'user_id' => 40, 'is_active' => true]);
        DB::table('cabinet_user')->insert(['cabinet_id' => 7, 'user_id' => 41, 'role' => 'collaborateur']);
        DB::table('companies')->insert(['id' => 42, 'company_name' => "SAS'T", 'user_id' => 50, 'selflow_company_id' => 91]);
    }

    public function test_la_liste_des_cabinets_exige_le_secret(): void
    {
        $this->postJson('/api/external/cabinets', ['secret' => 'faux'])->assertStatus(401);
    }

    public function test_la_liste_donne_le_gerant_et_les_collaborateurs(): void
    {
        $r = $this->postJson('/api/external/cabinets', ['secret' => self::SECRET])->assertOk();

        $this->assertSame('Cabinet Yao', $r->json('cabinets.0.nom'));
        $ids = collect($r->json('cabinets.0.membres'))->pluck('id')->all();
        $this->assertEqualsCanonicalizing([40, 41], $ids);
    }

    public function test_affecter_a_l_espace_du_cabinet(): void
    {
        $this->postJson('/api/external/companies/affecter', ['secret' => self::SECRET, 'selflow_company_id' => 91, 'cabinet_id' => 7])
            ->assertOk()->assertJson(['success' => true]);

        $this->assertSame(7, (int) DB::table('companies')->where('id', 42)->value('cabinet_id'));
        $this->assertSame(0, DB::table('company_user')->count());
    }

    public function test_affecter_a_un_membre_le_range_aussi_dans_le_cabinet(): void
    {
        $this->postJson('/api/external/companies/affecter', ['secret' => self::SECRET, 'selflow_company_id' => 91, 'cabinet_id' => 7, 'user_id' => 41])
            ->assertOk();

        $this->assertSame(7, (int) DB::table('companies')->where('id', 42)->value('cabinet_id'));
        $this->assertTrue(DB::table('company_user')->where(['company_id' => 42, 'user_id' => 41, 'role' => 'admin'])->exists());
    }

    public function test_un_non_membre_est_refuse(): void
    {
        $this->postJson('/api/external/companies/affecter', ['secret' => self::SECRET, 'selflow_company_id' => 91, 'cabinet_id' => 7, 'user_id' => 50])
            ->assertStatus(422);

        $this->assertNull(DB::table('companies')->where('id', 42)->value('cabinet_id'));
    }
}
