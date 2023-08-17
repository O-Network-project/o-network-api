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
        // Email, password and organization_id are fillable fields of the User
        // entity, but they are not editable once set. So the "prohibited" rule
        // is necessary to avoir unauthorized modification.
        return [
            'email' => ['prohibited'],
            'name' => ['sometimes', 'required', 'string', 'max:50'],
            'surname' => ['sometimes', 'required', 'string', 'max:50'],
            'password' => ['prohibited'],
            'currentPassword' => ['required_with:newPassword', 'string'],
            'newPassword' => ['required_with:currentPassword', 'string', 'regex:/^(?=.*\d)(?=.*[!@#$%^?&*])(?=.*[a-zA-Z]).{8,}$/'],
            'job' => ['sometimes', 'required', 'string', 'max:255'],
            'profilePicture' => ['sometimes', 'nullable', 'file', 'image', 'dimensions:min_width=128,min_height=128'],
            'organizationId' => ['prohibited'],
            'organization_id' => ['prohibited'],
            'disabled' => ['sometimes', 'required', 'boolean'],
        ];
    }

    protected function passedValidation()
    {
        if ($this->profilePicture) {
            $this->merge(['profile_picture' => $this->profilePicture]);
        }

        if ($this->newPassword) {
            $this->merge(['password' => Hash::make($this->newPassword)]);
        }
    }
}
