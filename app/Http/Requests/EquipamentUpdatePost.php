<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EquipamentUpdatePost extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->hasPermission(join('', array_slice(explode('\\', __CLASS__), -1)));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'type_equipament'   => ['required', Rule::in(['cacamba', 'others'])],
            'volume'            => ['required_if:type_equipament,cacamba', Rule::in(['Selecione ...',3,4,5,6,7,8,9,10])],
            'name'              => 'required_if:type_equipament,others',
            'reference'         => [
                'required',
                function ($attribute, $value, $fail) {
                    $exists = DB::table('equipaments')
                                ->where(['reference' => $this->reference, 'company_id' => $this->user()->company_id])
                                ->whereNotIn('id', [$this->equipament_id])
                                ->count();
                    if ($exists) {
                        $fail('Referência do equipamento já está em uso, informe outra.');
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
            'type_equipament.*'     => 'Tipo de equipamento mal informado, tente novamente.',
            'volume.required_if'    => 'Selecione um volume.',
            'volume.in'             => 'Selecione um volume.',
            'name.required_if'      => 'Digite o nome do equipamento',
            'reference.required'    => 'Digite a referência do equipamento'
        ];
    }
}
