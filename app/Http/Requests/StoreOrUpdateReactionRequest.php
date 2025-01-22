<?php

namespace App\Http\Requests;

use App\Models\ReactionType;
use Illuminate\Foundation\Http\FormRequest;

class StoreOrUpdateReactionRequest extends FormRequest
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
            'type' => ['required', 'string', 'exists:reaction_types,name'],
        ];
    }

    // Normally, the passedValidation method should be used for this purpose.
    // But its not working with the Request::validated() method controller-side,
    // only with the Request::all() one. Overriding the validated method was the
    // easiest way to keep this behavior.
    public function validated()
    {
        $request = parent::validated();

        $request['type_id'] = ReactionType::where('name', $this->type)->first()->id;
        return $request;
    }
}
