<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class RentalCreatePost extends FormRequest
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
            'type_rental' => 'required|boolean',
            'client' => 'required|numeric',
            'cep' => 'regex:/^[0-9]{2}.[0-9]{3}-[0-9]{3}$/|max:10|nullable',
            'address' => 'required',
            'number' => 'required',
            'complement' => 'nullable',
            'reference' => 'nullable',
            'neigh' => 'required',
            'city' => 'required',
            'state' => 'required',
            'date_delivery' => 'required|date_format:"d/m/Y H:i"',
            'date_withdrawal' => 'required_without:not_use_date_withdrawal',
            'equipment_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    foreach ($value as $v) {
                        $reference = $this->{'reference_equipment_'.$v};
                        $qty = $this->{'stock_equipment_'.$v};
                        $use_date_diff = $this->{'use_date_diff_equip_'.$v};
                        $date_delivery = $this->{'date_delivery_equipment_'.$v};
                        $date_withdrawal = $this->{'date_withdrawal_equipment_'.$v};
                        $not_use_date_withdrawal = $this->{'not_use_date_withdrawal_equip_'.$v};

                        if ($qty <= 0) {
                            $fail("Deve ser informada uma quantidade para o equipamento ( {$reference} ).");
                            break;
                        }
                        if ($use_date_diff) {
                            if (!$date_delivery || strlen($date_delivery) != 16) {
                                $fail("Deve ser informada uma data prevista de entrega para o equipamento ( {$reference} ).");
                                break;
                            }
                            if (!$not_use_date_withdrawal && (!$date_withdrawal || strlen($date_withdrawal) != 16)) {
                                $fail("Deve ser informada uma data prevista de retirada para o equipamento ( {$reference} ).");
                                break;
                            }
                            if (!$not_use_date_withdrawal) {
                                $date_delivery = \DateTime::createFromFormat('d/m/Y H:i', $date_delivery)->getTimestamp();
                                $date_withdrawal = \DateTime::createFromFormat('d/m/Y H:i', $date_withdrawal)->getTimestamp();

                                if ($date_delivery >= $date_withdrawal) {
                                    $fail("A data de retirada deve ser maior que a de entrega no equipamento ( {$reference} ).");
                                    break;
                                }
                            }
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
            'type_rental.*'     => 'Tipo de locação mal inforada.',
            'client.*'          => 'Cliente mal informado.',
            'cep.*'             => 'CEP do cliente em um formato inválido.',
            'address.required'  => 'Endereço do cliente precisa ser informado.',
            'address.number'    => 'Número do endereço do cliente precisa ser informado.',
            'address.neigh'     => 'Bairro do cliente precisa ser informado.',
            'address.city'      => 'Cidade do cliente precisa ser informado.',
            'address.state'     => 'Estado do cliente precisa ser informado.',
            'date_delivery.*'   => 'Data de entrega precisa ser informada corretamente',
            'date_withdrawal.*' => 'Data de retirada precisa ser informada corretamente',
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
