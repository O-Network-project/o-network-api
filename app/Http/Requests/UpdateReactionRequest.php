<?php

namespace App\Http\Requests;

use App\Models\ReactionType;
use Illuminate\Foundation\Http\FormRequest;

class UpdateReactionRequest extends FormRequest
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
            'type' => ['required', 'string', 'exists:reactions_types,tag'],
        ];
    }

    protected function passedValidation()
    {
        $this->merge([
            'type_id' => ReactionType::where('tag', $this->type)->first()->id
        ]);
    }
}
