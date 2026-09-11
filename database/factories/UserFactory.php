<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 *
 * Cette fabrique etait restee le modele livre par Laravel : elle posait
 * `email`, `email_verified_at` et `remember_token`, trois colonnes que la table
 * `users` de cette application n'a pas, et taisait `last_name`, `role`,
 * `pack`, `is_online`, `is_active` et `is_blocked`, qui sont obligatoires.
 *
 * Consequence : `User::factory()` echouait a la premiere insertion —
 * « table users has no column named email ». Aucune epreuve ne pouvait donc
 * creer un utilisateur, ce qui est le point de depart de presque toutes.
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'          => fake()->firstName(),
            'last_name'     => fake()->lastName(),
            'email_adresse' => fake()->unique()->safeEmail(),
            'password'      => static::$password ??= Hash::make('password'),
            'role'          => 'admin',
            'pack'          => 'entreprise',
            'is_online'     => false,
            'is_active'     => true,
            'is_blocked'    => false,
        ];
    }

    /**
     * Un compte de plateforme, qui ne depend d'aucune entreprise.
     */
    public function superadmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role'       => 'super_admin',
            'company_id' => null,
        ]);
    }

    /**
     * Un compte ferme : il existe, il ne se connecte pas.
     */
    public function bloque(string $motif = 'Compte suspendu'): static
    {
        return $this->state(fn (array $attributes) => [
            'is_blocked'   => true,
            'block_reason' => $motif,
            'blocked_at'   => now(),
        ]);
    }
}
