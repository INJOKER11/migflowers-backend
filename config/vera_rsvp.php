<?php

return [
    // Independent from the shop's Telegram configuration. Disabled until configured.
    'enabled' => env('VERA_RSVP_ENABLED', false),
    'bot_token' => env('VERA_RSVP_BOT_TOKEN'),
    'chat_id' => env('VERA_RSVP_CHAT_ID'),
    'require_invitation_key' => env('VERA_RSVP_REQUIRE_INVITATION_KEY', false),
    'invitation_key' => env('VERA_RSVP_INVITATION_KEY'),
    'allowed_origins' => array_values(array_filter(array_map(
        'trim', explode(',', (string) env('VERA_RSVP_ALLOWED_ORIGINS', ''))
    ))),
    // File cache needs no database migration. Use a shared Redis store on multiple servers.
    'cache_store' => env('VERA_RSVP_CACHE_STORE', 'file'),
];
