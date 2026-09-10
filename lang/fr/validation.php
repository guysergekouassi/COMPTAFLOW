<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Messages de validation
    |--------------------------------------------------------------------------
    |
    | Sans ce fichier, Laravel affiche la clé brute du message
    | (« validation.required ») dans tous les formulaires de l'application.
    |
    */

    'accepted'             => 'Le champ :attribute doit être accepté.',
    'accepted_if'          => 'Le champ :attribute doit être accepté quand :other vaut :value.',
    'active_url'           => "Le champ :attribute n'est pas une URL valide.",
    'after'                => 'Le champ :attribute doit être une date postérieure au :date.',
    'after_or_equal'       => 'Le champ :attribute doit être une date postérieure ou égale au :date.',
    'alpha'                => 'Le champ :attribute ne peut contenir que des lettres.',
    'alpha_dash'           => 'Le champ :attribute ne peut contenir que des lettres, des chiffres, des tirets et des underscores.',
    'alpha_num'            => 'Le champ :attribute ne peut contenir que des lettres et des chiffres.',
    'any_of'               => "Le champ :attribute n'est pas valide.",
    'array'                => 'Le champ :attribute doit être un tableau.',
    'ascii'                => 'Le champ :attribute ne peut contenir que des caractères alphanumériques et des symboles simples.',
    'before'               => 'Le champ :attribute doit être une date antérieure au :date.',
    'before_or_equal'      => 'Le champ :attribute doit être une date antérieure ou égale au :date.',

    'between' => [
        'array'   => 'Le champ :attribute doit contenir entre :min et :max éléments.',
        'file'    => 'Le fichier :attribute doit peser entre :min et :max kilo-octets.',
        'numeric' => 'Le champ :attribute doit être compris entre :min et :max.',
        'string'  => 'Le champ :attribute doit contenir entre :min et :max caractères.',
    ],

    'boolean'              => 'Le champ :attribute doit être vrai ou faux.',
    'can'                  => 'Le champ :attribute contient une valeur non autorisée.',
    'confirmed'            => 'La confirmation du champ :attribute ne correspond pas.',
    'contains'             => 'Le champ :attribute ne contient pas une valeur obligatoire.',
    'current_password'     => 'Le mot de passe est incorrect.',
    'date'                 => "Le champ :attribute n'est pas une date valide.",
    'date_equals'          => 'Le champ :attribute doit être une date égale à :date.',
    'date_format'          => 'Le champ :attribute ne correspond pas au format :format.',
    'decimal'              => 'Le champ :attribute doit avoir :decimal décimales.',
    'declined'             => 'Le champ :attribute doit être refusé.',
    'declined_if'          => 'Le champ :attribute doit être refusé quand :other vaut :value.',
    'different'            => 'Les champs :attribute et :other doivent être différents.',
    'digits'               => 'Le champ :attribute doit contenir :digits chiffres.',
    'digits_between'       => 'Le champ :attribute doit contenir entre :min et :max chiffres.',
    'dimensions'           => "La taille de l'image :attribute n'est pas conforme.",
    'distinct'             => 'Le champ :attribute a une valeur en double.',
    'doesnt_contain'       => 'Le champ :attribute ne doit contenir aucune des valeurs suivantes : :values.',
    'doesnt_end_with'      => 'Le champ :attribute ne doit pas se terminer par : :values.',
    'doesnt_start_with'    => 'Le champ :attribute ne doit pas commencer par : :values.',
    'email'                => 'Le champ :attribute doit être une adresse e-mail valide.',
    'encoding'             => "Le champ :attribute doit utiliser l'encodage :encoding.",
    'ends_with'            => 'Le champ :attribute doit se terminer par une des valeurs suivantes : :values.',
    'enum'                 => 'La valeur sélectionnée pour :attribute est invalide.',
    'exists'               => 'La valeur sélectionnée pour :attribute est invalide.',
    'extensions'           => 'Le fichier :attribute doit avoir une des extensions suivantes : :values.',
    'file'                 => 'Le champ :attribute doit être un fichier.',
    'filled'               => 'Le champ :attribute doit avoir une valeur.',

    'gt' => [
        'array'   => 'Le champ :attribute doit contenir plus de :value éléments.',
        'file'    => 'Le fichier :attribute doit peser plus de :value kilo-octets.',
        'numeric' => 'Le champ :attribute doit être supérieur à :value.',
        'string'  => 'Le champ :attribute doit contenir plus de :value caractères.',
    ],

    'gte' => [
        'array'   => 'Le champ :attribute doit contenir au moins :value éléments.',
        'file'    => 'Le fichier :attribute doit peser au moins :value kilo-octets.',
        'numeric' => 'Le champ :attribute doit être supérieur ou égal à :value.',
        'string'  => 'Le champ :attribute doit contenir au moins :value caractères.',
    ],

    'hex_color'            => 'Le champ :attribute doit être une couleur hexadécimale valide.',
    'image'                => 'Le champ :attribute doit être une image.',
    'in'                   => 'La valeur sélectionnée pour :attribute est invalide.',
    'in_array'             => "Le champ :attribute n'existe pas dans :other.",
    'in_array_keys'        => 'Le champ :attribute doit contenir au moins une des clés suivantes : :values.',
    'integer'              => 'Le champ :attribute doit être un nombre entier.',
    'ip'                   => 'Le champ :attribute doit être une adresse IP valide.',
    'ipv4'                 => 'Le champ :attribute doit être une adresse IPv4 valide.',
    'ipv6'                 => 'Le champ :attribute doit être une adresse IPv6 valide.',
    'json'                 => 'Le champ :attribute doit être un document JSON valide.',
    'list'                 => 'Le champ :attribute doit être une liste.',
    'lowercase'            => 'Le champ :attribute doit être en minuscules.',

    'lt' => [
        'array'   => 'Le champ :attribute doit contenir moins de :value éléments.',
        'file'    => 'Le fichier :attribute doit peser moins de :value kilo-octets.',
        'numeric' => 'Le champ :attribute doit être inférieur à :value.',
        'string'  => 'Le champ :attribute doit contenir moins de :value caractères.',
    ],

    'lte' => [
        'array'   => 'Le champ :attribute doit contenir au plus :value éléments.',
        'file'    => 'Le fichier :attribute doit peser au plus :value kilo-octets.',
        'numeric' => 'Le champ :attribute doit être inférieur ou égal à :value.',
        'string'  => 'Le champ :attribute doit contenir au plus :value caractères.',
    ],

    'mac_address'          => 'Le champ :attribute doit être une adresse MAC valide.',

    'max' => [
        'array'   => 'Le champ :attribute ne peut pas contenir plus de :max éléments.',
        'file'    => 'Le fichier :attribute ne peut pas peser plus de :max kilo-octets.',
        'numeric' => 'Le champ :attribute ne peut pas être supérieur à :max.',
        'string'  => 'Le champ :attribute ne peut pas contenir plus de :max caractères.',
    ],

    'max_digits'           => 'Le champ :attribute ne peut pas contenir plus de :max chiffres.',
    'mimes'                => 'Le champ :attribute doit être un fichier de type : :values.',
    'mimetypes'            => 'Le champ :attribute doit être un fichier de type : :values.',

    'min' => [
        'array'   => 'Le champ :attribute doit contenir au moins :min éléments.',
        'file'    => 'Le fichier :attribute doit peser au moins :min kilo-octets.',
        'numeric' => 'Le champ :attribute doit être au moins :min.',
        'string'  => 'Le champ :attribute doit contenir au moins :min caractères.',
    ],

    'min_digits'           => 'Le champ :attribute doit contenir au moins :min chiffres.',
    'missing'              => 'Le champ :attribute doit être absent.',
    'missing_if'           => 'Le champ :attribute doit être absent quand :other vaut :value.',
    'missing_unless'       => 'Le champ :attribute doit être absent sauf si :other vaut :value.',
    'missing_with'         => 'Le champ :attribute doit être absent quand :values est présent.',
    'missing_with_all'     => 'Le champ :attribute doit être absent quand :values sont présents.',
    'multiple_of'          => 'Le champ :attribute doit être un multiple de :value.',
    'not_in'               => 'La valeur sélectionnée pour :attribute est invalide.',
    'not_regex'            => "Le format du champ :attribute n'est pas valide.",
    'numeric'              => 'Le champ :attribute doit être un nombre.',
    'password' => [
        'letters'       => 'Le champ :attribute doit contenir au moins une lettre.',
        'mixed'         => 'Le champ :attribute doit contenir au moins une majuscule et une minuscule.',
        'numbers'       => 'Le champ :attribute doit contenir au moins un chiffre.',
        'symbols'       => 'Le champ :attribute doit contenir au moins un symbole.',
        'uncompromised' => 'Le champ :attribute est apparu dans une fuite de données. Veuillez en choisir un autre.',
    ],
    'present'              => 'Le champ :attribute doit être présent.',
    'present_if'           => 'Le champ :attribute doit être présent quand :other vaut :value.',
    'present_unless'       => 'Le champ :attribute doit être présent sauf si :other vaut :value.',
    'present_with'         => 'Le champ :attribute doit être présent quand :values est présent.',
    'present_with_all'     => 'Le champ :attribute doit être présent quand :values sont présents.',
    'prohibited'           => 'Le champ :attribute est interdit.',
    'prohibited_if'        => 'Le champ :attribute est interdit quand :other vaut :value.',
    'prohibited_if_accepted' => 'Le champ :attribute est interdit quand :other est accepté.',
    'prohibited_if_declined' => 'Le champ :attribute est interdit quand :other est refusé.',
    'prohibited_unless'    => 'Le champ :attribute est interdit sauf si :other fait partie de :values.',
    'prohibits'            => 'Le champ :attribute interdit la présence de :other.',
    'regex'                => "Le format du champ :attribute n'est pas valide.",
    'required'             => 'Le champ :attribute est obligatoire.',
    'required_array_keys'  => 'Le champ :attribute doit contenir les clés suivantes : :values.',
    'required_if'          => 'Le champ :attribute est obligatoire quand :other vaut :value.',
    'required_if_accepted' => 'Le champ :attribute est obligatoire quand :other est accepté.',
    'required_if_declined' => 'Le champ :attribute est obligatoire quand :other est refusé.',
    'required_unless'      => 'Le champ :attribute est obligatoire sauf si :other fait partie de :values.',
    'required_with'        => 'Le champ :attribute est obligatoire quand :values est présent.',
    'required_with_all'    => 'Le champ :attribute est obligatoire quand :values sont présents.',
    'required_without'     => "Le champ :attribute est obligatoire quand :values n'est pas présent.",
    'required_without_all' => "Le champ :attribute est obligatoire quand aucun de :values n'est présent.",
    'same'                 => 'Les champs :attribute et :other doivent être identiques.',

    'size' => [
        'array'   => 'Le champ :attribute doit contenir :size éléments.',
        'file'    => 'Le fichier :attribute doit peser :size kilo-octets.',
        'numeric' => 'Le champ :attribute doit être égal à :size.',
        'string'  => 'Le champ :attribute doit contenir :size caractères.',
    ],

    'starts_with'          => 'Le champ :attribute doit commencer par une des valeurs suivantes : :values.',
    'string'               => 'Le champ :attribute doit être une chaîne de caractères.',
    'timezone'             => 'Le champ :attribute doit être un fuseau horaire valide.',
    'unique'               => 'La valeur du champ :attribute est déjà utilisée.',
    'uploaded'             => 'Le téléversement du fichier :attribute a échoué.',
    'uppercase'            => 'Le champ :attribute doit être en majuscules.',
    'url'                  => "Le format de l'URL :attribute n'est pas valide.",
    'ulid'                 => 'Le champ :attribute doit être un ULID valide.',
    'uuid'                 => 'Le champ :attribute doit être un UUID valide.',

    /*
    |--------------------------------------------------------------------------
    | Messages personnalisés
    |--------------------------------------------------------------------------
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'message-personnalisé',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Libellés des champs
    |--------------------------------------------------------------------------
    |
    | Pour que le message parle du « Nom de l'entreprise » plutôt que
    | de « company_name ».
    |
    */

    'attributes' => [
        // Entreprise
        'company_name'          => "nom de l'entreprise",
        'company_code'          => "code d'accès de l'entreprise",
        'juridique_form'        => 'forme juridique',
        'activity'              => 'activité',
        'social_capital'        => 'capital social',
        'adresse'               => 'adresse',
        'siege_social'          => 'siège social',
        'commune'               => 'commune',
        'quartier'              => 'quartier',
        'city'                  => 'ville',
        'code_postal'           => 'boîte postale',
        'country'               => 'pays',
        'phone_number'          => 'numéro de téléphone',
        'email_adresse'         => 'adresse e-mail',
        'identification_TVA'    => 'identification TVA',
        'idu'                   => 'IDU',
        'ncc'                   => 'NCC',
        'rccm'                  => 'RCCM',
        'cnps'                  => 'CNPS',
        'compte_contribuable'   => 'compte contribuable',
        'regime'                => 'régime fiscal',
        'rattachement_dgi'      => 'rattachement DGI',
        'proprietaire_local'    => 'propriétaire du local',
        'reference_cadastrale'  => 'référence cadastrale',
        'expert_comptable_nom'  => "nom de l'expert-comptable",
        'expert_comptable_ncc'  => "NCC de l'expert-comptable",
        'logo'                  => 'logo',
        'sticker_solde_alerte'  => "seuil d'alerte stickers",

        // Utilisateur
        'name'                  => 'nom',
        'last_name'             => 'nom de famille',
        'first_name'            => 'prénom',
        'password'              => 'mot de passe',
        'password_confirmation' => 'confirmation du mot de passe',
        'role'                  => 'rôle',
        'pack'                  => 'offre souscrite',
        'admin_name'            => "nom de l'administrateur",
        'admin_last_name'       => "nom de famille de l'administrateur",
        'admin_email_adresse'   => "adresse e-mail de l'administrateur",
        'admin_password'        => "mot de passe de l'administrateur",

        // Comptabilité
        'date'                  => 'date',
        'date_debut'            => 'date de début',
        'date_fin'              => 'date de fin',
        'intitule'              => 'intitulé',
        'debit'                 => 'débit',
        'credit'                => 'crédit',
        'numero_de_compte'      => 'numéro de compte',
        'numero_de_tiers'       => 'numéro de tiers',
        'plan_comptable_id'     => 'compte général',
        'new_compte_id'         => 'compte de destination',
        'code_journal'          => 'code journal',
        'code_journal_id'       => 'journal',
        'reference_piece'       => 'référence de la pièce',
        'description_operation' => "libellé de l'opération",
        'exercices_comptables_id' => 'exercice comptable',
        'ids'                   => 'écritures sélectionnées',
    ],

];
