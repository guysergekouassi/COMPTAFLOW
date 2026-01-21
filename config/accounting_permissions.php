<?php

return [
    // Rôles
    'roles' => [
        'super_admin' => 'Super Administrateur de la plateforme', // Peut gérer tout, y compris les comptes Company
        'admin'       => 'Administrateur de Compagnie (Sous-Admin Comptabilité)', // Peut gérer les utilisateurs et les paramètres de sa Company
        'comptable'   => 'Comptable avec toutes les habilitations',
        'auditeur'    => 'Accès lecture seule aux rapports',
    ],

    // Habilitations (Permissions spécifiques)
    'permissions' => [
        'Pilotage' => [
            'compta.dashboard' => 'Tableau de bord',
            'admin.performance' => 'Tableau de bord Admin',
        ],
        'Configuration' => [
            'admin.config.hub' => 'Dossier de Configuration',
            'admin.config.plan_comptable' => 'Modèle de Plan',
            'admin.config.plan_tiers' => 'Modèle de Tiers',
            'admin.config.journals' => 'Structure des Journaux',
        ],
        'Gouvernance' => [
            'compta_accounts.index' => 'Gestion des Entités',
            'admin.companies.create' => 'Créer Entreprise',
            'user_management' => 'Équipe & Permissions',
            'admin.switch' => 'Switch Comptabilité',
        ],
        'Opérations' => [
            'admin.audit' => 'Archives & Audit',
            'admin.access' => 'Contrôle d\'Accès',
            'admin.tasks' => 'Assignation de Tâches',
        ],
        'Validation' => [
            'admin.approvals' => 'Approbations',
        ],
        'Paramétrage' => [
            'plan_comptable' => 'Plan Comptable',
            'plan_tiers' => 'Plan Tiers',
            'accounting_journals' => 'Codes Journaux',
            'postetresorerie.index' => 'Poste Trésorerie',
        ],
        'Traitement' => [
            'modal_saisie_direct' => 'Nouvelle Saisie',
            'accounting_entry_list' => 'Liste des écritures',
            'brouillons.index' => 'Brouillons',
            'exercice_comptable' => 'Exercice Comptable',
            'accounting_entry_real' => 'Rapprochement Bancaire',
            'gestion_tresorerie' => 'Gestion Trésorerie',
        ],
        'Rapports' => [
            'accounting_ledger' => 'Grand Livre',
            'accounting_ledger_tiers' => 'Grand Livre Tiers',
            'accounting_balance' => 'Balance',
            'Balance_Tiers' => 'Balance Tiers',
            'compte_exploitation' => 'Compte d\'Exploitation',
            'flux_tresorerie' => 'Flux de Trésorerie',
            'tableau_amortissements' => 'Tableau d\'Amortissements',
            'etat_tiers' => 'État des Tiers',
            'compte_resultat' => 'Compte de Résultat',
            'bilan' => 'Bilan',
            'etats_analytiques' => 'États Analytiques',
            'etats_previsionnels' => 'États Prévisionnels',
        ],
    ],

    // Mapping des rôles par défaut aux permissions
    // Utilisé lors de l'initialisation d'un nouvel utilisateur dans une Company.
    'role_permissions_map' => [
        'admin' => [
            'compta.dashboard', 'admin.performance', 'admin.config.hub', 'admin.config.plan_comptable',
            'admin.config.plan_tiers', 'admin.config.journals', 'compta_accounts.index',
            'admin.companies.create', 'user_management', 'admin.switch', 'admin.audit',
            'admin.access', 'admin.tasks', 'admin.approvals', 'plan_comptable', 'plan_tiers',
            'accounting_journals', 'postetresorerie.index', 'modal_saisie_direct',
            'accounting_entry_list', 'brouillons.index', 'exercice_comptable',
            'accounting_entry_real', 'gestion_tresorerie', 'accounting_ledger',
            'accounting_ledger_tiers', 'accounting_balance', 'Balance_Tiers',
            'compte_exploitation', 'flux_tresorerie', 'tableau_amortissements',
            'etat_tiers', 'compte_resultat', 'bilan', 'etats_analytiques', 'etats_previsionnels'
        ],
        'comptable' => [
            'compta.dashboard', 'plan_comptable', 'plan_tiers', 'accounting_journals',
            'postetresorerie.index', 'modal_saisie_direct', 'accounting_entry_list',
            'brouillons.index', 'exercice_comptable', 'accounting_entry_real',
            'gestion_tresorerie', 'accounting_ledger', 'accounting_ledger_tiers',
            'accounting_balance', 'Balance_Tiers', 'flux_tresorerie'
        ]
    ],
];
