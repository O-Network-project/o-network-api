<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Classes\Invitation\InvitationRepository;

class UserController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(User::class, 'user');
    }

    /**
     * Override the default mapping of the resource policies methods to add our
     * custom showOrganizationUsers and showProfilePicture methods, and also to
     * remove the create one.
     * This create action must be available for unauthenticated user, and so not
     * being checked by the policies system. A policy requires an authentication
     * to work, or it will systematically return a 403 error.
     * (the resourceAbilityMap() method comes from the AuthorizesRequests trait, imported in
     * the Controller parent class).
     *
     * @return array
     */
    protected function resourceAbilityMap()
    {
        $resourceAbilityMap = array_filter(parent::resourceAbilityMap(), function ($ability) {
            return $ability !== 'create';
        });

        return array_merge($resourceAbilityMap, [
            'showOrganizationUsers' => 'viewAnyFromOrganization',
            'showProfilePicture' => 'view'
        ]);
    }

    /**
     * Override the default list of the policy methods that cannot receive an
     * instantiated model to add our custom showOrganizationUsers one (the
     * resourceMethodsWithoutModels() method comes from the AuthorizesRequests
     * trait, imported in the Controller parent class).
     *
     * @return array
     */
    protected function resourceMethodsWithoutModels()
    {
        return array_merge(parent::resourceMethodsWithoutModels(), [
            'showOrganizationUsers'
        ]);
    }

    /**
     * Return all the users of the database. But in this app MVP, no user
     * with any role can access that full list, it's blocked by the UserPolicy.
     * This method is only here to avoid an error when requesting the /users URI
     * with the GET verb.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new UserCollection(User::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreUserRequest $request, InvitationRepository $invitationRepository)
    {
        $user = new User();
        $userData = $request->validated();
        $invitationToken = $request->input('invitationToken');
        $invitation = null;

        if ($invitationToken !== null) {
            $invitation = $invitationRepository->find($invitationToken);

            if ($invitation === null) {
                // If there is no invitation with such token when treating this
                // request, 2 possibilities:
                // - (hacking scenario) direct POST request with a token that
                //   never existed
                // - (normal scenario) the invitation was still valid when
                //   displaying the form, but not when sending it
                // The normal scenario matters the most, so the 410 Gone status
                // code is more appropriate than the 404 Not Found.
                abort(Response::HTTP_GONE, "L'invitation a expiré. Veuillez contacter l'administrateur pour en demander une nouvelle.");
            }

            $userData['email'] = $invitation->getEmail();
            $userData['organization_id'] = $invitation->getOrganizationId();
        }

        $user->fill($userData);

        if ($request->hasFile('profilePicture')) {
            $user->profile_picture = $this->storeProfilePicture($request->file('profilePicture'));
        }

        try {
            $isOrganizationEmpty = User::where('organization_id', $user->organization_id)->count() === 0;

            // The first user of an organization is considered as the admin
            if ($isOrganizationEmpty) {
                $user->role_id = 2;
            }

            $user->save();
        }
        catch (\Exception $error) {
            // If an error occurs after the storage of the profile picture but
            // before saving the user in the database, the picture shouldn't be
            // kept in the filesystem to avoid orphan files.
            $this->deleteProfilePicture($user);

            throw $error;
        }

        if ($invitation !== null) {
            // There is no limit on the number of invitations an administrator
            // can send to a single user, so when the account is finally
            // created, all related invitations must be deleted.
            $allUserInvitations = $invitationRepository->findByEmail($user->email);

            foreach ($allUserInvitations as $invitation) {
                $invitationRepository->delete($invitation);
            }
        }

        return new UserResource($user);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        return new UserResource($user);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function showOrganizationUsers(Organization $organization)
    {
        return new UserCollection($organization->users);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $inputs = $request->validated();

        $profilePictureDeleted = array_key_exists('profile_picture', $inputs)
            && $inputs['profile_picture'] === null;

        // If the user asked to remove its profile picture or he/shed sent a new
        // one, the old picture must be deleted
        if ($profilePictureDeleted || $request->hasFile('profilePicture')) {
            $this->deleteProfilePicture($user);
        }

        if ($request->hasFile('profilePicture')) {
            $inputs['profile_picture'] = $this->storeProfilePicture($request->file('profilePicture'));
        }

        try {
            $user->update($inputs);
        }
        catch (\Exception $error) {
            // If an error occurs when updating the path of the profile picture
            // in the database, the picture shouldn't be kept in the filesystem
            // to avoid orphan files.
            $this->deleteProfilePicture($user);

            throw $error;
        }

        return new UserResource($user);
    }

    /**
     * Store the profile picture in the /storage/app/public/profile-pictures
     * folder and returns the generated file name.
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @return string
     */
    protected function storeProfilePicture(UploadedFile $file)
    {
        // Generating the file name outside the store() method allows to get it
        // without the parent folder name.
        // "file-name.jpg" instead of "profile-pictures/file-name.jpg"
        $fileName = $file->hashName();
        $file->store('profile-pictures', ['disk' => 'public']);
        return $fileName;
    }

    /**
     * Delete the profile picture of a user in the
     * /storage/app/public/profile-pictures folder. Returns true when the
     * suppression succeeded when the user has no profile picture to delete,
     * else false.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    protected function deleteProfilePicture(User $user)
    {
        if ($user->profile_picture === null) {
            return true;
        }

        return Storage::disk('public')
            ->delete("/profile-pictures/$user->profile_picture");
    }
}
