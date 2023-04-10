<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class VehicleUpdatePost extends FormRequest
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
            'name'   => [
                'required',
                function ($attribute, $value, $fail) {
                    $exists = DB::table('vehicles')
                        ->where(['name' => $this->name, 'company_id' => $this->user()->company_id])
                        ->whereNotIn('id', [$this->vehicle_id])
                        ->count();
                    if ($exists) $fail('Nome do veículo já está em uso');
                }
            ],
            'reference' => [
                function ($attribute, $value, $fail) {
                    $exists = DB::table('vehicles')
                        ->where(['reference' => $this->reference, 'company_id' => $this->user()->company_id])
                        ->whereNotIn('id', [$this->vehicle_id])
                        ->count();
                    if ($exists) $fail('Referência do veículo já está em uso');
                }
            ],
            'board'     => [
                function ($attribute, $value, $fail) {
                    $exists = DB::table('vehicles')
                        ->where(['board' => $this->board, 'company_id' => $this->user()->company_id])
                        ->whereNotIn('id', [$this->vehicle_id])
                        ->count();
                    if ($exists) $fail('Placa do veículo já está em uso');
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
            'name.required'  => 'Nome do veículo é obrigatório',
        ];
    }
}
