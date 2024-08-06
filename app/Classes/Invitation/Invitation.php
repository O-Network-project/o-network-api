<?php

namespace App\Classes\Invitation;

use App\Http\Resources\OrganizationResource;
use App\Models\Organization;
use Carbon\Carbon;
use JsonSerializable;

/**
 * Custom model for member invitation; it's a model but not an Eloquent one.
 * An invitation is identified by a UUID token and is related to the email of
 * the invited user. Invitations are stored in Redis, for the easy use of the
 * expiration date.
 * Don't instantiate this class manually, use the dedicated
 * InvitationRepository.
 */
class Invitation implements JsonSerializable
{
    /**
     * UUID V4 token which identify the invitation. Stored in the key itself,
     * right after the prefix.
     */
    private string $token;

    /**
     * ID of the organization where the user has been invited to. Stored in the
     * value of the key.
     */
    private int $organizationId;

    /**
     * Organization model where the user has been invited to. Only used as a
     * cache for the getOrganization() method.
     */
    private ?Organization $organization = null;

    /**
     * Email of the invited user. Stored in the value of the key.
     */
    private string $email;

    /**
     * Expiration date of the invitation. Calculated from the TTL of the key.
     */
    private Carbon $expiresAt;



    /**
     * Dont' use the constructor directly, use the dedicated
     * InvitationRepository instead.
     */
    public function __construct(string $token, int $organizationId, string $email, Carbon $expiresAt)
    {
        $this->token = $token;
        $this->email = $email;
        $this->organizationId = $organizationId;
        $this->expiresAt = $expiresAt;
    }

    /**
     * Get the token which identify the invitation
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * Get ID of the organization where the user has been invited to
     */
    public function getOrganizationId()
    {
        return $this->organizationId;
    }

    /**
     * Get the organization where the user has been invited to from the
     * database. The result is cached for further use.
     */
    public function getOrganization()
    {
        if (!$this->organization) {
            $this->organization = Organization::find($this->organizationId);
        }

        return $this->organization;
    }

    /**
     * Get the email related to the invitation
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Get the the expiration date of the invitation
     */
    public function getExpiresAt(): Carbon
    {
        return $this->expiresAt;
    }

    /**
     * Return the invitation data as an array, to make it compatible with the
     * JsonSerializable interface
     */
    public function jsonSerialize(): array
    {
        return [
            'token' => $this->getToken(),
            'organization' => new OrganizationResource($this->getOrganization()),
            'email' => $this->getEmail(),
            'expiresAt' => $this->getExpiresAt()->toDateTimeString()
        ];
    }
}
