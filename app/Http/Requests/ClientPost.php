<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class ClientPost extends FormRequest
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
            'type_person'   => 'size:2',
            'name_client'   => [
                'required',
                function ($attribute, $value, $fail) {
                    $type = $this->type_person;

                    $exists = DB::table('clients')->where(['name' => $this->name, 'company_id' => $this->user()->company_id])->count();
                    if ($exists) {
                        $fail($type == 'pf' ? 'Nome do cliente já está em uso' : 'Razão Social do cliente já está em uso');
                    }
                }
            ],
            'phone_1'       => 'min:13|max:14|nullable',
            'phone_2'       => 'min:13|max:14|nullable',
            'email'         => 'email|nullable',
            'cpf_cnpj'      => [
                function ($attribute, $value, $fail) {
                    $cpf_cnpj = filter_var(preg_replace('~[\\\\/.-]~', '', $this->cpf_cnpj), FILTER_SANITIZE_NUMBER_INT);
                    $type = $this->type_person;

                    $exists = DB::table('clients')->where(['cpf_cnpj' => $cpf_cnpj, 'company_id' => $this->user()->company_id])->count();
                    if ($exists) {
                        $fail($type == 'pf' ? 'CPF do cliente já está em uso' : 'CNPJ do cliente já está em uso');
                    }
                }
            ],
        ];
    }
    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        $type = $this->type_person;

        return [
            'type_person.size'      => 'Tipo de pessoa mal informado, selecione entre Pessoa Física ou Pesso Jurídica',
            'name_client.required'  => $type == 'pf' ? 'O Nome é obrigatório' : 'A Razão Social é obrigatório',
            'phone_1.min'           => 'O telefone principal deve conter o DDD e o número telefônico',
            'phone_1.max'           => 'O telefone principal deve conter o DDD e o número telefônico',
            'phone_2.min'           => 'O telefone secundário deve conter o DDD e o número telefônico',
            'phone_2.max'           => 'O telefone secundário deve conter o DDD e o número telefônico',
            'email.email'           => 'O endereço de e-mail deve ser um e-mail válido'
        ];
    }
}
