<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Member invitation TTL
    |--------------------------------------------------------------------------
    |
    | Time left before a member invitation expires, in seconds. This value is
    | used directly as the TTL setting in the Redis SET command.
    |
    */

    'invitation_ttl' => 2592000 // 30 days

];
