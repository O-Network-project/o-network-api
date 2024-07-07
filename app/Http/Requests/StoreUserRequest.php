<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
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
            'invitationToken' => ['filled', 'prohibits:email,organizationId', 'regex:/^[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}$/'],
            'email' => ['required_without:invitationToken', 'email', 'max:255', 'unique:users'],
            'name' => ['required', 'string', 'max:50'],
            'surname' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string', 'regex:/^(?=.*\d)(?=.*[!@#$%^?&*])(?=.*[a-zA-Z]).{8,}$/'],
            'job' => ['required', 'string', 'max:255'],
            'profilePicture' => ['nullable', 'file', 'image', 'dimensions:min_width=128,min_height=128'],
            'organizationId' => ['required_without:invitationToken', 'integer', 'exists:organizations,id']
        ];
    }

    // Normally, the passedValidation method should be used for this purpose.
    // But its not working with the Request::validated() method controller-side,
    // only with the Request::all() one. Overriding the validated method was the
    // easiest way to keep this behavior.
    public function validated()
    {
        $request = parent::validated();

        $request['password'] = Hash::make($this->password);

        if (array_key_exists('profilePicture', $request)) {
            $request['profile_picture'] = $this->profilePicture;
        }

        // In the case of an invitation, the organization ID is not sent by the
        // client. Sp this condition avoids getting 0 as an ID, due to the integer
        // typecasting.
        if (array_key_exists('organizationId', $request)) {
            $request['organization_id'] = (int) $this->organizationId;
        }

        return $request;
    }
}
