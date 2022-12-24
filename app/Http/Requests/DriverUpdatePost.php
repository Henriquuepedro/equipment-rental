<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class DriverUpdatePost extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return hasPermission(join('', array_slice(explode('\\', __CLASS__), -1)));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name'      => [
                'required',
                function ($attribute, $value, $fail) {
                    $exists = DB::table('drivers')
                        ->where(['name' => $this->name, 'company_id' => $this->user()->company_id])
                        ->whereNotIn('id', [$this->driver_id])
                        ->count();
                    if ($exists) {
                        $fail('Nome do motorista já está em uso');
                    }
                }
            ],
            'email'     => 'email:rfc,dns|nullable',
            'phone'     => 'min:13|max:14|nullable',
            'cpf'       => [
                function ($attribute, $value, $fail) {
                    $cpf = filter_var(preg_replace('~[\\\\/.-]~', '', $this->cpf), FILTER_SANITIZE_NUMBER_INT);
                    $exists = DB::table('drivers')
                        ->where(['cpf' => $cpf, 'company_id' => $this->user()->company_id])
                        ->whereNotIn('id', [$this->driver_id])
                        ->count();
                    if ($exists && !empty($cpf)) {
                        $fail('CPF do motorista já está em uso');
                    }
                }
            ],
            'rg'        => 'numeric|nullable',
            'cnh'       => 'numeric|nullable',
            'cnh_exp'   => 'date|nullable'
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
            'name.required' => 'O Nome é obrigatório',
            'email.email'   => 'O endereço de e-mail deve ser um e-mail válido',
            'phone.min'     => 'O telefone deve conter o DDD e o número telefônico',
            'phone.max'     => 'O telefone deve conter o DDD e o número telefônico',
            'rg.numeric'    => 'O RG deve conter apenas números',
            'cnh.numeric'   => 'O RG deve conter apenas números',
            'cnh_exp.date'  => 'A data de expiraçao da CNH deve ser uma data válida',
        ];
    }
}
