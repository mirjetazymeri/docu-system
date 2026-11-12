<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
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
        switch ($this->method()) {
            case 'GET':
            case 'DELETE':
                return [];

            case 'POST':
                return [
                    'first_name' => 'required|min:3',
                    'last_name' => 'required|min:3',
                    'email' => [
                        'required',
                        'email',
                        Rule::unique('users', 'email')
                            ->whereNull('deleted_at'),
                    ],
                    'password' => 'required|between:3,32',
                    'password_confirm' => 'required|same:password',
                    'pic_file' => 'mimes:jpg,jpeg,bmp,png,gif|max:10000',
                    'role' => 'required|exists:roles,id',
                    'access_level_ids' => 'nullable|array',
                    'access_level_ids.*' => 'integer|exists:access_levels,id',
                ];

            case 'PUT':
            case 'PATCH':
                return [
                    'first_name' => 'required|min:3',
                    'last_name' => 'required|min:3',
                    'email' => 'required|unique:users,email,' . $this->user->id,
                    'password_confirm' => 'sometimes|same:password',
                    'pic_file' => 'image|mimes:jpg,jpeg,bmp,png|max:10000',
                ];

            default:
                break;
        }
    }
}
