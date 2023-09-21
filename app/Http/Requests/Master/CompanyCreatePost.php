<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class CompanyCreatePost extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return hasAdminMaster();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name'          => 'required',
            'email'         => 'required|email:rfc,dns',
            'phone_1'       => 'required|min:13|max:14',
            'phone_2'       => 'min:13|max:14|nullable',
            'profile_logo'  => 'mimes:png,jpeg,jpg,gif|max:2048',
            'cpf_cnpj'      => [
                'required',
                function ($attribute, $value, $fail) {
                    $exists = DB::table('companies')->where('cpf_cnpj', onlyNumbers($this->get('cpf_cnpj')))->count();
                    if ($exists) {
                        if (strlen(onlyNumbers($this->get('cpf_cnpj'))) === 14) {
                            $fail('CNPJ já está em uso');
                        } else {
                            $fail('CPF já está em uso');
                        }
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
    public function messages(): array
    {
        return [
            'name.required'     => 'Digite o nome/razão social da empresa.',
            'email.required'    => 'O campo de e-mail comercial é um campo obrigatório.',
            'email.email'       => 'Informe um e-mail comercial válido.',
            'phone_1.required'  => 'O campo telefone primário é um campo obrigatório.',
            'phone_1.min'       => 'O campo telefone primário deve ser um telefone válido.',
            'phone_1.max'       => 'O campo telefone primário deve ser um telefone válido.',
            'phone_2.min'       => 'O campo telefone secundário deve ser um telefone válido.',
            'profile_logo.mimes'=> 'A extensão da logo não é permitida, envie JPG, JPEG ou PNG.',
            'profile_logo.max'  => 'O tamanho da logo excede o limite máximo, envie a logo com até 2mb .'
        ];
    }
}
