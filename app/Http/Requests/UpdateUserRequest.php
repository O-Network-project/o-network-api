<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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

        if ($this->profilePicture) {
            $request['profile_picture'] = $this->profilePicture;
        }

        if ($this->newPassword) {
            $request['password'] = Hash::make($this->newPassword);
        }

        return $request;
    }
}
