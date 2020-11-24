<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class UserCreatePost extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->hasAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name_modal'        => [
                'required',
                function ($attribute, $value, $fail) {
                    $exists = DB::table('users')->where(['name' => $value, 'company_id' => $this->user()->company_id])->count();
                    if ($exists)
                        return response()->json(['success' => false, 'message' => 'Nome informado do novo usuário já está em uso.']);
                }
            ],
            'email_modal'       => [
                'required',
                'email:rfc,dns',
                function ($attribute, $value, $fail) {
                    $exists = DB::table('users')->where('email', $value)->count();
                    if ($exists)
                        return response()->json(['success' => false, 'message' => 'E-mail informado do novo usuário já está em uso.']);
                }
            ],
            'phone_modal'       => 'min:13|max:14|required',
            'password_modal'    => 'required|confirmed|min:6'
        ];
    }
    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'name_modal.required'       => 'O Nome é obrigatório',
            'email_modal.required'      => 'O endereço de e-mail é obrigatório',
            'email_modal.email'         => 'O endereço de e-mail deve ser um e-mail válido',
            'phone_modal.min'           => 'O telefone deve conter o DDD e o número telefônico',
            'phone_modal.max'           => 'O telefone deve conter o DDD e o número telefônico',
            'password_modal.required'   => 'A senha é obrigatória',
            'password_modal.confirmed'  => 'As senhas não correspondem',
            'password_modal.min'        => 'A senha para acesso deve conter no mínimo 6 dígitos'
        ];
    }
}
