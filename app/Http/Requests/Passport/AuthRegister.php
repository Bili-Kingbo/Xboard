<?php

namespace App\Http\Requests\Passport;

use Illuminate\Foundation\Http\FormRequest;

class AuthRegister extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'email' => 'required|email:strict',
            'password' => 'required|min:8'
        ];

        if (config('app.internal_free_mode')) {
            $rules['group_id'] = 'required|integer|exists:v2_server_group,id';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'email.required' => __('Email can not be empty'),
            'email.email' => __('Email format is incorrect'),
            'password.required' => __('Password can not be empty'),
            'password.min' => __('Password must be greater than 8 digits'),
            'group_id.required' => __('Please select an identity group'),
            'group_id.integer' => __('Identity group format is incorrect'),
            'group_id.exists' => __('The selected identity group does not exist'),
        ];
    }
}
