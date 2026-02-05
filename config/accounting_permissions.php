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
        'Pilotage (Super Admin)' => [
            'superadmin.dashboard' => 'Tableau de bord SuperAdmin',
        ],
        'Gouvernance (Super Admin)' => [
            'superadmin.entities' => 'Gestion des Entités',
            'superadmin.companies.create' => 'Créer Entreprise',
            'superadmin.accounting.create' => 'Créer Comptabilité',
            'superadmin.users' => 'Gestion Utilisateurs',
            'superadmin.users.create' => 'Créer Utilisateur',
            'superadmin.admins.create' => 'Créer Administrateur',
            'superadmin.switch' => 'Switch Entreprise',
        ],
        'Opérations (Super Admin)' => [
            'superadmin.activities' => 'Suivi des Activités',
            'admin.tasks.index' => 'Assigner Tâche',
            'superadmin.access' => 'Contrôle d\'Accès',
        ],
        'Analyses (Super Admin)' => [
            'superadmin.reports' => 'Rapports Performance',
        ],
        'Pilotage' => [
            'admin.performance' => 'Tableau de bord Admin',
            'compta.dashboard' => 'Tableau de bord',
            'notifications.index' => 'Notifications',
        ],
        'Configuration Entreprise' => [
            'admin.config.hub' => 'Dossier de Configuration',
            'admin.config.plan_comptable' => 'Modèle de Plan',
            'admin.config.plan_tiers' => 'Modèle de Tiers',
            'admin.config.journals' => 'Modèle des Journaux',
            'admin.config.tresorerie_posts' => 'Postes de Trésorerie',
        ],
        'Importation' => [
            'admin.config.external_import' => 'Importation de données',
            'admin.import.hub' => 'Tunnel d\'Importation',
        ],
        'Fusion & Démarrage' => [
            'admin.fusion.index' => 'Fusion Données Mère',
        ],
        'Exportation' => [
            'admin.export.hub' => 'Exportation de données',
        ],
        'Gouvernance' => [
            'compta_accounts.index' => 'Gestion des Entités',
            'admin.companies.create' => 'Créer Entreprise',
            'user_management' => 'Équipe & Permissions',
            'admin.habilitations.index' => 'Modification Habilitation',
            'admin.switch' => 'Switch Comptabilité',
            'compta.create' => 'Créer Comptabilité',
            'admin.admins.create' => 'Créer Administrateur',
            'admin.secondary_admins.create' => 'Créer Admin Sécondaire',
            'admin.users.create' => 'Créer Utilisateur',
        ],
        'Opérations' => [
            'admin.audit' => 'Traçabilité & Activités',
            'admin.access' => 'Contrôle d\'Accès',
        ],
        'Gestion des Tâches' => [
            'admin.tasks.index' => 'Assigner Tâche',
            'tasks.assign' => 'Assigner Tâche (Alternatif)',
            'tasks.view_daily' => 'Tâches Quotidiennes',
        ],
        'Validation' => [
            'admin.approvals' => 'Approbations',
        ],
        'Paramétrage' => [
            'plan_comptable' => 'Plan comptable',
            'plan_tiers' => 'Plan tiers',
            'accounting_journals' => 'Journaux',
            'postetresorerie.index' => 'Poste Trésorerie',
        ],
        'Traitement' => [
            'modal_saisie_direct' => 'Nouvelle saisie',
            'accounting_entry_list' => 'Liste des écritures',
            'ecriture.rejected' => 'Écritures rejetées',
            'brouillons.index' => 'Brouillons',
            'exercice_comptable' => 'Exercice comptable',
            'gestion_tresorerie' => 'Gestion Trésorerie',
            'immobilisations.index' => 'Gestion Immobilisations',
        ],
        'ETATS FINANCIERS' => [
            'bilan' => 'Bilan Actif/Passif',
            'compte_resultat' => 'Compte de Résultat',
        ],
        'Rapports' => [
            'accounting_ledger' => 'Grand livre',
            'accounting_balance' => 'Balance',
        ],
    ],

    // Mapping des rôles par défaut aux permissions
    // Utilisé lors de l'initialisation d'un nouvel utilisateur dans une Company.
    'role_permissions_map' => [
        'admin' => [
            'compta.dashboard', 'admin.performance', 'notifications.index', 'admin.config.hub', 'admin.config.plan_comptable',
            'admin.config.plan_tiers', 'admin.config.journals', 'admin.config.tresorerie_posts', 'admin.config.external_import', 'admin.import.hub',
            'admin.export.hub', 'admin.fusion.index',
            'compta_accounts.index',
            'admin.companies.create', 'user_management', 'admin.habilitations.index', 'admin.switch', 'admin.admins.create',
            'admin.secondary_admins.create', 'admin.users.create', 'admin.audit',
            'admin.access', 'tasks.assign', 'tasks.view_daily', 'admin.approvals', 'plan_comptable', 'plan_tiers',
            'accounting_journals', 'postetresorerie.index', 'modal_saisie_direct',
            'accounting_entry_list', 'ecriture.rejected', 'brouillons.index', 'exercice_comptable', 'gestion_tresorerie',
            'accounting_entry_real', 'accounting_ledger',
            'accounting_ledger_tiers', 'accounting_balance', 'Balance_Tiers',
            'compte_exploitation', 'flux_tresorerie', 'tableau_amortissements',
            'etat_tiers', 'compte_resultat', 'bilan', 'etats_analytiques', 'etats_previsionnels', 'immobilisations.index'
        ],
        'comptable' => [
            'compta.dashboard', 'plan_comptable', 'plan_tiers', 'accounting_journals',
            'postetresorerie.index', 'modal_saisie_direct', 'accounting_entry_list', 'ecriture.rejected',
            'brouillons.index', 'accounting_entry_real',
            'gestion_tresorerie', 'accounting_ledger', 'accounting_ledger_tiers',
            'accounting_balance', 'Balance_Tiers', 'flux_tresorerie', 'tasks.view_daily', 'immobilisations.index'
        ]
    ],
];
