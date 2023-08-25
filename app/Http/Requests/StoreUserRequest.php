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
            'email' => ['required', 'email', 'max:255', 'unique:users'],
            'name' => ['required', 'string', 'max:50'],
            'surname' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string', 'regex:/^(?=.*\d)(?=.*[!@#$%^?&*])(?=.*[a-zA-Z]).{8,}$/'],
            'job' => ['required', 'string', 'max:255'],
            'profilePicture' => ['nullable', 'file', 'image', 'dimensions:min_width=128,min_height=128'],
            'organizationId' => ['required', 'integer', 'exists:organizations,id']
        ];
    }

    // Normally, the passedValidation method should be used for this purpose.
    // But its not working with the Request::validated() method controller-side,
    // only with the Request::all() one. Overriding the validated method was the
    // easiest way to keep this behavior.
    public function validated()
    {
        $request = parent::validated();

        return array_merge($request, [
            'password' => Hash::make($this->password),
            'profile_picture' => $this->profilePicture,
            'organization_id' => (int) $this->organizationId
        ]);
    }
}
