<?php

namespace App\Mail;

use App\Models\Company;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Le lien d'activation du compte administrateur d'un dossier provisionné.
 *
 * `WelcomeEmail` transportait le mot de passe choisi ailleurs — il arrivait
 * donc en clair dans une boîte de réception, où il reste indéfiniment. Ici
 * aucun mot de passe ne circule : le message porte un lien à usage unique, et
 * le titulaire choisit lui-même son mot de passe au bout.
 */
class LienActivationMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public Company $company;
    public string $lien;

    public function __construct(User $user, Company $company, string $jeton)
    {
        $this->user    = $user;
        $this->company = $company;
        $this->lien    = route('activation.formulaire', ['jeton' => $jeton]);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'ComptaFlow — activez votre compte',
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.activation');
    }
}
