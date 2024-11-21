<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class DisposalPlaceUpdatePost extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return hasPermission(join('', array_slice(explode('\\', __CLASS__), -1)));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'type_person'   => 'required|size:2',
            'name'   => [
                'required',
                function ($attribute, $value, $fail) {
                    $exists = DB::table('disposal_places')
                        ->where(['name' => $this->get('name'), 'company_id' => $this->user()->company_id])
                        ->whereNotIn('id', [$this->get('disposal_place_id')])
                        ->count();

                    if ($exists) {
                        $fail($this->get('type_person') == 'pf' ? 'Nome do local de descarte já está em uso' : 'Razão Social do local de descarte já está em uso');
                    }
                }
            ],
            'phone_1'       => 'min:13|max:14|required',
            'phone_2'       => 'min:13|max:14|nullable',
            'email'         => 'email:rfc,dns|required',
            'cpf_cnpj'      => [
                'required',
                function ($attribute, $value, $fail) {
                    $cpf_cnpj = filter_var(onlyNumbers($this->get('cpf_cnpj')), FILTER_SANITIZE_NUMBER_INT);

                    if (!empty($cpf_cnpj)) {
                        $exists = DB::table('disposal_places')
                            ->where(['cpf_cnpj' => $cpf_cnpj, 'company_id' => $this->user()->company_id])
                            ->whereNotIn('id', [$this->get('disposal_place_id')])
                            ->count();

                        if ($exists) {
                            $fail($this->get('type_person') == 'pf' ? 'CPF do local de descarte já está em uso' : 'CNPJ do local de descarte já está em uso');
                        }
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
    public function messages(): array
    {
        return [
            'type_person.size'  => 'Tipo de pessoa mal informado, selecione entre Pessoa Física ou Pesso Jurídica',
            'name.required'     => $this->get('type_person') == 'pf' ? 'O Nome é obrigatório' : 'A Razão Social é obrigatório',
            'phone_1.min'       => 'O telefone principal deve conter o DDD e o número telefônico',
            'phone_1.max'       => 'O telefone principal deve conter o DDD e o número telefônico',
            'phone_2.min'       => 'O telefone secundário deve conter o DDD e o número telefônico',
            'phone_2.max'       => 'O telefone secundário deve conter o DDD e o número telefônico',
            'email.email'       => 'O endereço de e-mail deve ser um e-mail válido'
        ];
    }
}
