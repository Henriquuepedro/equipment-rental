<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UserCreatePost extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return hasAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name'        => 'required',
            'email'       => [
                'required',
                'email:rfc,dns',
                function ($attribute, $value, $fail) {
                    $exists = DB::table('users')->where('email', $value)->count();
                    if ($exists) {
                        $fail('E-mail informado do novo usuário já está em uso.');
                    }
                }
            ],
            'phone'       => 'min:13|max:14|required',
            'password'    => 'required|confirmed|min:6',
            'type_user'   => ['required', Rule::in(['0', '1', '2'])]
        ];
    }
    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.required'       => 'O Nome é obrigatório',
            'email.required'      => 'O endereço de e-mail é obrigatório',
            'email.email'         => 'O endereço de e-mail deve ser um e-mail válido',
            'phone.min'           => 'O telefone deve conter o DDD e o número telefônico',
            'phone.max'           => 'O telefone deve conter o DDD e o número telefônico',
            'password.required'   => 'A senha é obrigatória',
            'password.confirmed'  => 'As senhas não correspondem',
            'password.min'        => 'A senha para acesso deve conter no mínimo 6 dígitos'
        ];
    }
}
