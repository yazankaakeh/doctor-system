<?php

return [
    'status' => [
        'pending' => 'Pending',
        'queued' => 'Queued',
        'sent' => 'Sent',
        'delivered' => 'Delivered',
        'read' => 'Read',
        'failed' => 'Failed',
    ],
    'type' => [
        'text' => 'Text',
        'image' => 'Image',
        'document' => 'Document',
        'audio' => 'Audio',
        'video' => 'Video',
        'template' => 'Template',
        'location' => 'Location',
        'sticker' => 'Sticker',
        'contact' => 'Contact',
    ],
    'direction' => [
        'inbound' => 'Received',
        'outbound' => 'Sent',
    ],
    'sender' => [
        'user' => 'Agent',
        'contact' => 'Contact',
        'system' => 'System',
    ],
];
