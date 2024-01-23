<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProfileUpdatePost extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name'      => 'required',
            'phone'     => 'min:13|max:14|required',
            'password'  => 'nullable|confirmed|min:6',
            'email'     => [
                'nullable',
                'unique:users,email,'.$this->input('user_id') ?? $this->input('update_user_id'),
//                function ($attribute, $value, $fail) {
//                    if (!empty($value)) {
//                        $exists = DB::table('users')
//                            ->where('email', $value)
//                            ->whereNotIn('id', [])
//                            ->count();
//                        if ($exists) {
//                            $fail('Endereço de e-mail já está em uso.');
//                        }
//                    }
//                }
            ],
            'style_template'    => [Rule::in([1,3])]
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
            'name.required'         => 'Digite o seu nome.',
            'email.unique'          => 'Endereço de e-mail já está em uso.',
            'phone.*'               => 'Digite um número de telefone válido.',
            'password.confirmed'    => 'As senhas não correspondem.',
            'password.min'          => 'A nova senha precisa ter no mínimo 6 caracteres'
        ];
    }
}
