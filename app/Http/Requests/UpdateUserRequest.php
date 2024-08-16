<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(Request $request)
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        /** @var User $updatedUser */
        $updatedUser = $request->route()->parameter('user');

        $isSameUser = $currentUser->id === $updatedUser->id;

        // If the updated user is not the authenticated one, he/she is
        // necessarily the organization's admin (check the UserPolicy::update()
        // method), no need to explicitly check it again.

        // The two below conditions could have been merged, but they are way
        // easier to understand when they are separated.

        // Admins can only update one and only field from other users: disabled
        if (
            !$isSameUser
            && (!$request->has('disabled') || count($request->post()) > 1)
        ) {
            return false;
        }

        // A user cannot disable itself, admin or not
        if ($isSameUser && $request->has('disabled')) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:50'],
            'surname' => ['sometimes', 'required', 'string', 'max:50'],
            'currentPassword' => ['required_with:newPassword', 'string', 'current_password'],
            'newPassword' => ['required_with:currentPassword', 'string', 'regex:/^(?=.*\d)(?=.*[!@#$%^?&*])(?=.*[a-zA-Z]).{8,}$/'],
            'job' => ['sometimes', 'required', 'string', 'max:255'],
            'profilePicture' => ['sometimes', 'nullable', 'file', 'image', 'dimensions:min_width=128,min_height=128'],
            'disabled' => ['sometimes', 'required', 'boolean'],
        ];
    }

    // Normally, the passedValidation method should be used for this purpose.
    // But its not working with the Request::validated() method controller-side,
    // only with the Request::all() one. Overriding the validated method was the
    // easiest way to keep this behavior.
    public function validated()
    {
        $request = parent::validated();

        // There is 3 possible scenarios with the profile picture: not provided,
        // null (which means "deleted") or string. So array_key_exists() is used
        // here for the following reasons:
        // - isset() returns false if the key is undefined or null
        // - with requests properties ($this->profilePicture for example) there
        //   is no difference when the property doesn't exist or is null: null
        //   is returned in any case
        // - array_key_exists() is perfect because it only checks the existence
        //   of the key, no matter the value
        if (array_key_exists('profilePicture', $request)) {
            $request['profile_picture'] = $this->profilePicture;
        }

        if ($this->newPassword) {
            $request['password'] = Hash::make($this->newPassword);
        }

        return $request;
    }
}
