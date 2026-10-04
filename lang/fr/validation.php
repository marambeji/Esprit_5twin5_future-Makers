<?php

return [
    'required' => 'Le champ :attribute est obligatoire.',
    'string' => 'Le champ :attribute doit être un texte.',
    'email' => 'Le champ :attribute doit être une adresse e-mail valide.',
    'unique' => 'Cette valeur du champ :attribute est déjà utilisée.',
    'confirmed' => 'La confirmation du champ :attribute ne correspond pas.',
    'boolean' => 'Le champ :attribute doit être vrai ou faux.',
    'integer' => 'Le champ :attribute doit être un nombre entier.',
    'numeric' => 'Le champ :attribute doit être un nombre.',
    'exists' => 'La valeur sélectionnée pour :attribute est invalide.',
    'in' => 'La valeur sélectionnée pour :attribute est invalide.',
    'image' => 'Le champ :attribute doit être une image.',
    'mimes' => 'Le champ :attribute doit être un fichier de type :values.',
    'uploaded' => 'Le téléversement du champ :attribute a échoué.',
    'decimal' => 'Le champ :attribute doit contenir :decimal décimales.',
    'min' => [
        'string' => 'Le champ :attribute doit contenir au moins :min caractères.',
        'numeric' => 'Le champ :attribute doit être supérieur ou égal à :min.',
        'file' => 'Le fichier :attribute doit peser au moins :min Ko.',
        'array' => 'Le champ :attribute doit contenir au moins :min éléments.',
    ],
    'max' => [
        'string' => 'Le champ :attribute ne doit pas dépasser :max caractères.',
        'numeric' => 'Le champ :attribute doit être inférieur ou égal à :max.',
        'file' => 'Le fichier :attribute ne doit pas dépasser :max Ko.',
        'array' => 'Le champ :attribute ne doit pas contenir plus de :max éléments.',
    ],
    'attributes' => [
        'name' => 'nom', 'email' => 'adresse e-mail', 'password' => 'mot de passe',
        'password_confirmation' => 'confirmation du mot de passe', 'remember' => 'se souvenir de moi',
        'role' => 'rôle', 'description' => 'description', 'price' => 'prix', 'origin' => 'origine',
        'category_id' => 'catégorie', 'category' => 'catégorie', 'image' => 'image',
    ],
];
