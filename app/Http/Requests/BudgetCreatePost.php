<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;

class BudgetCreatePost extends FormRequest
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
            'client' => 'required|numeric',
            'cep' => 'regex:/^[0-9]{2}.[0-9]{3}-[0-9]{3}$/|max:10|nullable',
            'address' => 'required',
            'number' => 'required',
            'complement' => 'nullable',
            'reference' => 'nullable',
            'neigh' => 'required',
            'city' => 'required',
            'state' => 'required',
            'equipment_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    foreach ($value as $v) {
                        $reference = $this->{'reference_equipment_'.$v};
                        $qty = $this->{'stock_equipment_'.$v};

                        if ($qty <= 0) {
                            $fail("Deve ser informada uma quantidade para o equipamento ( {$reference} ).");
                            break;
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
    public function messages()
    {
        return [
            'client.*'          => 'Cliente mal informado.',
            'cep.*'             => 'CEP do cliente em um formato inválido.',
            'address.required'  => 'Endereço do cliente precisa ser informado.',
            'address.number'    => 'Número do endereço do cliente precisa ser informado.',
            'address.neigh'     => 'Bairro do cliente precisa ser informado.',
            'address.city'      => 'Cidade do cliente precisa ser informado.',
            'address.state'     => 'Estado do cliente precisa ser informado.',
            'equipment_id.*'    => 'Equipamento precisa ser informado'
        ];
    }

    /**
     * Get the proper failed validation response for the request.
     *
     * @param array $errors
     * @return JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function response(array $errors)
    {
        if (isAjax()) return response()->json(['errors' => $errors]);

        return $this->redirector->to($this->getRedirectUrl())
            ->withInput($this->except($this->dontFlash))
            ->withErrors($errors, $this->errorBag);
    }
}
