<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class ProfileUpdatePost extends FormRequest
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
            'name'      => 'required',
            'phone'     => 'min:13|max:14|required',
            'password'  => 'nullable|confirmed|min:6',
            'email'     => [
                'nullable',
                function ($attribute, $value, $fail) {
                    $exists = DB::table('users')
                        ->where('email', $this->email)
                        ->whereNotIn('id', [$this->user_id])
                        ->count();
                    if ($exists) {
                        echo json_encode(['success' => false, 'data' => 'Endereço de e-mail já está em uso.']);
                        die;
                    }
                }
            ]
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
            'name.required'         => 'Digite o seu nome.',
            'phone.*'               => 'Digite um número de telefone válido.',
            'password.confirmed'    => 'As senhas não correspondem.',
            'password.min'          => 'A nova senha precisa ter no mínimo 6 caracteres'
        ];
    }
}
