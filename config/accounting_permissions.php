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
        // Général
        'compta.dashboard',

        // Paramétrage
        'plan_comptable',
        'plan_tiers',
        'accounting_journals',
        'indextresorerie',

        // Traitement
        'modal_saisie_direct',
        'exercice_comptable',
        'accounting_entry_real', // Rapprochement
        'gestion_tresorerie',
        // Autres traitements (commentés dans votre Blade, mais inclus ici pour être complet)
        // 'gestion_comptes',
        // 'gestion_tiers',
        // 'gestion_analytique',
        // 'gestion_immobilisations',
        // 'gestion_stocks',
        // 'gestion_reportings',

        // Rapports comptables
        'accounting_ledger',
        'accounting_ledger_tiers',
        'accounting_balance',
        'accounting_balance_tiers',
        'flux_tresorerie', // Explicitement routé
        // Rapports non routés (simples placeholders dans le menu)
        // 'compte_exploitation',
        // 'tableau_amortissements',
        // 'etat_tiers',
        // 'compte_resultat',
        // 'bilan',
        // 'etats_analytiques',
        // 'etats_previsionnels',

        // Paramètres de l'entreprise (pour le Sous-Admin/Admin de la Company)
        'user_management',      // Gestion des utilisateurs de cette Company
        'compagny_information', // Modification des informations de la Company
    ],

    // Mapping des rôles par défaut aux permissions
    // Utilisé lors de l'initialisation d'un nouvel utilisateur dans une Company.
    'role_permissions_map' => [
        'admin' => [
            'compta.dashboard', 'plan_comptable', 'plan_tiers', 'accounting_journals',
            'indextresorerie', 'modal_saisie_direct', 'exercice_comptable',
            'accounting_entry_real', 'gestion_tresorerie', 'accounting_ledger',
            'accounting_ledger_tiers', 'accounting_balance', 'accounting_balance_tiers',
            'flux_tresorerie', 'user_management', 'compagny_information'
            // Ajoutez d'autres permissions ici pour le rôle 'admin' de la compagnie.
        ],
        // Vous pouvez ajouter d'autres mappings ici (ex: 'comptable', 'auditeur')
    ],
];
