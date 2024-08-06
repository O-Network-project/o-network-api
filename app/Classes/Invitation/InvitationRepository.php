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
     * Get an invitation in Redis from its token, as an Invitation instance.
     */
    public function find(string $token): ?Invitation
    {
        $key = self::PREFIX.$token;

        /** @var array{organizationId:int,email:string} */
        $invitation = json_decode(Redis::get($key), true);

        if ($invitation === null) {
            return null;
        }

        return new Invitation(
            $token,
            $invitation['organizationId'],
            $invitation['email'],
            RedisHelper::getKeyExpirationDate($key)
        );
    }

    /**
     * Get all invitations in Redis, as an array of Invitation instances.
     *
     * @return Invitation[]
     */
    public function findAll(): array
    {
        $keys = Redis::keys(self::PREFIX . '*');

        return array_map(function ($key) {
            // The token is the part after the ":" in the key
            $token = explode(':', $key)[1];

            return $this->find($token);
        }, $keys);
    }

    /**
     * Get all invitations for a specific email in Redis, as an array of
     * Invitation instances.
     * The performances are not ideal, as all the invitations must be retrieved
     * from Redis and instantiated before being filtered. For scalability, the
     * Redis query must be optimized using a new key format which contains the
     * email, to be able to use the wildcard (the asterisk -> *).
     *
     * @return Invitation[]
     */
    public function findByEmail(string $email): array
    {
        return array_filter($this->findAll(), function (Invitation $invitation) use ($email) {
            return $invitation->getEmail() === $email;
        });
    }

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
