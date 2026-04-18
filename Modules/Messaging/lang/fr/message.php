<?php

return [
    'status' => [
        'pending' => 'En attente',
        'queued' => 'En file d\'attente',
        'sent' => 'Envoyé',
        'delivered' => 'Livré',
        'read' => 'Lu',
        'failed' => 'Échoué',
    ],
    'type' => [
        'text' => 'Texte',
        'image' => 'Image',
        'document' => 'Document',
        'audio' => 'Audio',
        'video' => 'Vidéo',
        'template' => 'Modèle',
        'location' => 'Localisation',
        'sticker' => 'Autocollant',
        'contact' => 'Contact',
    ],
    'direction' => [
        'inbound' => 'Reçu',
        'outbound' => 'Envoyé',
    ],
    'sender' => [
        'user' => 'Agent',
        'contact' => 'Contact',
        'system' => 'Système',
    ],
];
