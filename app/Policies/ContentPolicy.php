<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;

/**
 * Parent policy of the app contents: Post, Comment and Reaction.
 * Used to mutualize some methods.
 */
abstract class ContentPolicy
{
    use HandlesAuthorization;

    /**
     * Return the class name (without the namespace) of the provided model.
     * Helps to compose generic error messages.
     *
     * @param Model $model
     * @return string
     */
    private static function getModelClassName(Model $model): string
    {
        return (new \ReflectionClass($model))->getShortName();
    }

    /**
     * Return true if the current user is from the same organization than the
     * provided model, else false. The model can be an organization itself.
     * Can work because all of the intended models (Post, Comment and Reaction)
     * have a direct access to their parent organization.
     *
     * @param User $user
     * @param Model $model
     * @return boolean
     */
    private static function sameOrganization(User $user, Model $model)
    {
        return $model instanceof Organization ?
            $model->id === $user->organization_id :
            $model->organization->id === $user->organization_id;
    }

    /**
     * Return an allowed response if the current user is from the same
     * organization than the provided model, or an error message if it's not the
     * case. The model can be an organization itself.
     *
     * @param User $user
     * @param Model $model
     * @return Response
     */
    protected static function sameOrganizationResponse(User $user, Model $model)
    {
        if (!self::sameOrganization($user, $model)) {
            $modelName = self::getModelClassName($model);

            return Response::deny($model instanceof Organization ?
                "The authenticated user doesn't belong to this organization" :
                "This ".strtolower($modelName)." doesn't belong to the authenticated user's organization"
            );
        }

        return Response::allow();
    }

    /**
     * Return true if the current user is the author of the provided model, else
     * false.
     *
     * @param User $user
     * @param Model $model
     * @return boolean
     */
    private static function sameAuthor(User $user, Model $model)
    {
        return $model->author_id === $user->id;
    }

    /**
     * Return an allowed response if the current user is the author of the
     * provided model, or an error message if it's not the case.
     *
     * @param User $user
     * @param Model $model
     * @return Response
     */
    protected static function sameAuthorResponse(User $user, Model $model)
    {
        if (!self::sameAuthor($user, $model)) {
            $modelName = self::getModelClassName($model);
            return Response::deny("The authenticated user is not the author of this ".strtolower($modelName));
        }

        return Response::allow();
    }

    /**
     * Return true if the current user is the author of the provided model or
     * the admin of the author's organization, else false.
     *
     * @param User $user
     * @param Model $model
     * @return boolean
     */
    private static function sameAuthorOrAdmin(User $user, Model $model)
    {
        return (
            self::sameAuthor($user, $model)
            || $user->isAdmin() && self::sameOrganization($user, $model)
        );
    }

    /**
     * Return an allowed response if the current user is the author of the
     * provided model or the admin of the author's organization, or an error
     * message if it's not the case.
     *
     * @param User $user
     * @param Model $model
     * @return Response/boolean
     */
    protected static function sameAuthorOrAdminResponse(User $user, Model $model)
    {
        if (!self::sameAuthorOrAdmin($user, $model)) {
            $modelName = self::getModelClassName($model);
            return Response::deny("The authenticated user is not the author of this ".strtolower($modelName)." or the administrator of the organization");
        }

        return Response::allow();
    }
}
