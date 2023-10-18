<?php

namespace App\Classes\Invitation;

use App\Classes\Helpers\RedisHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Redis;

/**
 * Repository for the Invitation class.
 */
class InvitationRepository
{
    /**
     * Prefix of all invitations keys.
     * Example: invitation:3af82ba2-54f0-483b-b054-8cdec88f7053
     * But keep in mind every Redis keys in Laravel are prefixed again with the
     * setting database.redis.options.prefix from the configuration files.
     */
    private const PREFIX = 'invitation:';

    /**
     * Create an invitation in Redis for the provided email in the current user
     * organization and return it as an Invitation instance. Generate the token
     * as a UUID.
     */
    public function create(string $email): Invitation
    {
        $token = Str::uuid();
        $key = self::PREFIX.$token;
        $organizationId = Auth::user()->organization_id;

        Redis::set(
            $key,
            json_encode(['organizationId' => $organizationId, 'email' => $email]),
            'ex', config('business.invitation_ttl')
        );

        return new Invitation(
            $token,
            $organizationId,
            $email,
            RedisHelper::getKeyExpirationDate($key)
        );
    }

    /**
     * Delete an invitation in Redis. Of course, if the email had been sent, it
     * won't be deleted from the user mail inbox :)
     */
    public function delete(Invitation $invitation): void
    {
        Redis::del(self::PREFIX.$invitation->getToken());
    }
}
