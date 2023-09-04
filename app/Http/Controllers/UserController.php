<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(User::class, 'user');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // In that app MVP, no user with any role can access the list of all
        // users
        return response(null, 403);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreUserRequest $request)
    {
        $user = new User();
        $user->fill($request->validated());

        if ($request->hasFile('profilePicture')) {
            $user->profile_picture = $this->storeProfilePicture($request->file('profilePicture'));
        }

        $isOrganizationEmpty = User::where('organization_id', $request->get('organization_id'))->count() === 0;

        // The first user of an organization is considered as the admin
        if ($isOrganizationEmpty) {
            $user->role_id = 2;
        }

        $user->save();

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

        if ($request->hasFile('profilePicture')) {
            Storage::disk('public')->delete("/profiles-pictures/$user->profile_picture");
            $inputs['profile_picture'] = $this->storeProfilePicture($request->file('profilePicture'));
        }

        $user->update($inputs);
        return new UserResource($user);
    }

    /**
     * Store the profile picture in the /storage/app/public/profiles-pictures
     * folder and returns the generated file name.
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @return string
     */
    protected function storeProfilePicture(UploadedFile $file)
    {
        // Generating the file name outside the store() method allows to get it
        // without the parent folder name.
        // "file-name.jpg" instead of "profiles-pictures/file-name.jpg"
        $fileName = $file->hashName();
        $file->store('profiles-pictures', ['disk' => 'public']);
        return $fileName;
    }

    /**
     * Return the profile picture of the the provided user as a binary file.
     *
     * @param  \App\Models\User  $user
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function showProfilePicture(User $user)
    {
        if (!$user->profile_picture) {
            return response(null, 404);
        }

        $path = "/profiles-pictures/$user->profile_picture";

        if (!Storage::disk('public')->exists($path)) {
            return response(null, 404);
        }

        return response()->file(Storage::disk('public')->path($path));
    }
}
