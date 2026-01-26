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
        'Super Admin - Pilotage' => [
            'superadmin.dashboard' => 'Tableau de bord Global',
        ],
        'Super Admin - Gouvernance' => [
            'superadmin.entities' => 'Gestion des Entités',
            'superadmin.companies.create' => 'Créer Entreprise',
            'superadmin.accounting.create' => 'Créer Comptabilité',
            'superadmin.users' => 'Gestion Utilisateurs',
            'superadmin.users.create' => 'Créer Utilisateur SuperAdmin',
            'superadmin.admins.create' => 'Créer Administrateur SuperAdmin',
            'superadmin.switch' => 'Switch Entreprise Global',
        ],
        'Super Admin - Opérations' => [
            'superadmin.activities' => 'Suivi des Activités',
            'superadmin.access' => 'Contrôle d\'Accès Global',
            'superadmin.access' => 'Contrôle d\'Accès Global',
        ],
        'Super Admin - Analyses' => [
            'superadmin.reports' => 'Rapports Performance Globaux',
        ],
        'Pilotage' => [
            'compta.dashboard' => 'Tableau de bord',
            'admin.performance' => 'Tableau de bord Admin',
        ],
        'Configuration/Importation' => [
            'admin.config.hub' => 'Dossier de Configuration (Hub)',
            'admin.config.update_settings' => 'Modifier Paramètres Structurels (Chiffres/Système)',
            'admin.config.load_standard_journals' => 'Initialiser Journaux Standards',
            'admin.config.plan_comptable' => 'Modèle de Plan',
            'admin.config.plan_tiers' => 'Modèle de Tiers',
            'admin.config.journals' => 'Structure des Journaux',
            'admin.config.external_import' => 'Tunnel d\'Importation Intelligent',
        ],
        'Exportation' => [
            'admin.config.export.view' => 'Accès au Module d\'Exportation',
            'admin.config.export.process' => 'Générer des Exports (Sage, FEC, Excel)',
        ],
        'Gouvernance' => [
            'compta_accounts.index' => 'Gestion des Entités',
            'admin.companies.create' => 'Créer Entreprise',
            'user_management' => 'Équipe & Permissions',
            'admin.habilitations.index' => 'Modification Habilitation', // NOUVEAU
            'admin.switch' => 'Switch Comptabilité',
            'compta.create' => 'Créer Comptabilité',
            'admin.admins.create' => 'Créer Administrateur',
            'admin.secondary_admins.create' => 'Créer Admin Sécondaire',
            'admin.users.create' => 'Créer Utilisateur',
        ],
        'Gestion des Tâches' => [ // NOUVEAU
            'tasks.assign' => 'Assigner Tâches',
            'tasks.view_daily' => 'Tâches Quotidiennes',
        ],
        'Opérations' => [
            'admin.audit' => 'Archives & Audit',
            'admin.access' => 'Contrôle d\'Accès',
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
            'ecriture.rejected' => 'Écritures Rejetées',
            'brouillons.index' => 'Brouillons',
            'exercice_comptable' => 'Exercice Comptable',
        ],
        'Rapports' => [
            'accounting_ledger' => 'Grand Livre',
            'accounting_balance' => 'Balance',
        ],
    ],

    // Mapping des rôles par défaut aux permissions
    // Utilisé lors de l'initialisation d'un nouvel utilisateur dans une Company.
    'role_permissions_map' => [
        'admin' => [
            'compta.dashboard', 'admin.performance', 'admin.config.hub', 'admin.config.plan_comptable',
            'admin.config.plan_tiers', 'admin.config.journals', 'admin.config.external_import',
            'admin.config.export.view', 'admin.config.export.process',
            'compta_accounts.index',
            'admin.companies.create', 'user_management', 'admin.habilitations.index', 'admin.switch', 'admin.admins.create',
            'admin.secondary_admins.create', 'admin.users.create', 'admin.audit',
            'admin.access', 'tasks.assign', 'tasks.view_daily', 'admin.approvals', 'plan_comptable', 'plan_tiers',
            'accounting_journals', 'postetresorerie.index', 'modal_saisie_direct',
            'accounting_entry_list', 'ecriture.rejected', 'brouillons.index', 'exercice_comptable',
            'accounting_entry_real', 'gestion_tresorerie', 'accounting_ledger',
            'accounting_ledger_tiers', 'accounting_balance', 'Balance_Tiers',
            'compte_exploitation', 'flux_tresorerie', 'tableau_amortissements',
            'etat_tiers', 'compte_resultat', 'bilan', 'etats_analytiques', 'etats_previsionnels'
        ],
        'comptable' => [
            'compta.dashboard', 'plan_comptable', 'plan_tiers', 'accounting_journals',
            'postetresorerie.index', 'modal_saisie_direct', 'accounting_entry_list', 'ecriture.rejected',
            'brouillons.index', 'accounting_entry_real',
            'gestion_tresorerie', 'accounting_ledger', 'accounting_ledger_tiers',
            'accounting_balance', 'Balance_Tiers', 'flux_tresorerie', 'tasks.view_daily'
        ]
    ],
];
