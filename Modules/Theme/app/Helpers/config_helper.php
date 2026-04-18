<?php

function getSMByLanguage($country, $type): string
{
    $att = [
        'uk' => [
            'twitter' => 'https://twitter.com/buyglobaluk',
            'instagram' => 'https://www.instagram.com/buyglobalukraine/',
            'facebook' => 'https://www.facebook.com/profile.php?id=100095364703534',
            'linkedin' => 'https://www.linkedin.com/company/97896886/admin/feed/posts/',
            'tiktok' => 'https://www.tiktok.com/@buyglobaluk',
            'youtubeVideo' => 'https://www.youtube.com/embed/rCsu25J_awc?si=HkbCiiD47ubI5XGJ',
            'vk' => 'https://vk.com/public221544370',
        ],
        'ar' => [
            'twitter' => 'https://twitter.com/BuyGlobalArabic',
            'instagram' => 'https://www.instagram.com/buyglobalarabic/',
            'facebook' => 'https://www.facebook.com/profile.php?id=100094306455821',
            'linkedin' => 'https://www.linkedin.com/company/buyglobalar/',
            'tiktok' => 'https://www.tiktok.com/@buyglobalar',
            'youtubeVideo' => 'https://www.youtube.com/embed/W7XhnVJ64mw?si=PuRFZ0srVqpVl5Uf',
        ],
        'ru' => [
            'twitter' => 'https://twitter.com/buyglobalru',
            'instagram' => 'https://www.instagram.com/buyglobalru/',
            'facebook' => 'https://www.facebook.com/profile.php?id=100094646721299',
            'linkedin' => 'https://www.linkedin.com/company/buyglobalrussia/?viewAsMember=true',
            'tiktok' => 'https://www.tiktok.com/@buyglobal.russia',
            'youtubeVideo' => 'https://www.youtube.com/embed/YpxWHAcnE6E?si=k_KPVZuS8BQIhhdh',
        ],
        'en' => [
            'twitter' => 'https://twitter.com/BuyGlobalco',
            'instagram' => 'https://www.instagram.com/buyglobalco/',
            'facebook' => 'https://www.facebook.com/profile.php?id=100093341501983',
            'linkedin' => 'https://www.linkedin.com/company/96015073/admin/?feedType=following',
            'tiktok' => 'https://www.tiktok.com/@buyglobalco?lang=tr-TR',
            'youtubeVideo' => 'https://www.youtube.com/embed/iOzgnzvECqA?si=_Ed6dpByoVUdff5m',
        ],
        'tr' => [
            'twitter' => 'https://twitter.com/BuyGlobalco',
            'instagram' => 'https://www.instagram.com/buyglobalco/',
            'facebook' => 'https://www.facebook.com/profile.php?id=100093341501983',
            'linkedin' => 'https://www.linkedin.com/company/96015073/admin/?feedType=following',
            'tiktok' => 'https://www.tiktok.com/@buyglobalco?lang=tr-TR',
            'youtubeVideo' => 'https://www.tiktok.com/@buyglobalco?lang=tr-TR',
        ],
    ];

    return $att[$country][$type];
}
