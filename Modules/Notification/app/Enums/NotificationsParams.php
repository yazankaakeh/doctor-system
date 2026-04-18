<?php

namespace Modules\Notification\Enums;

enum NotificationsParams: string
{
    // SMTP
    case MAIL_HOST = 'MAIL_HOST';
    case MAIL_PORT = 'MAIL_PORT';
    case MAIL_USERNAME = 'MAIL_USERNAME';
    case MAIL_PASSWORD = 'MAIL_PASSWORD';
    case MAIL_FROM_ADDRESS = 'MAIL_FROM_ADDRESS';
    case MAIL_FROM_NAME = 'MAIL_FROM_NAME';
    case ADMIN_MAIL_ADDRESS = 'ADMIN_MAIL_ADDRESS';
    case ADMIN_MAIL_NAME = 'ADMIN_MAIL_NAME';

    // WhatsApp
    case WA_TOKEN = 'WA_TOKEN';

    // FireBase
    case FB_API_KEY = 'FB_API_KEY';
    case FB_AUTH_DOMAIN = 'FB_AUTH_DOMAIN';
    case FB_PROJECT_ID = 'FB_PROJECT_ID';
    case FB_STORAGE_BUCKET = 'FB_STORAGE_BUCKET';
    case FB_MESSAGING_SENDER_ID = 'FB_MESSAGING_SENDER_ID';
    case FB_APP_ID = 'FB_APP_ID';
    case FB_MEASUREMENT_ID = 'FB_MEASUREMENT_ID';
    case FCM_SERVER_KEY = 'FCM_SERVER_KEY';
}
