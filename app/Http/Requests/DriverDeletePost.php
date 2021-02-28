<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class DriverDeletePost extends FormRequest
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
            'driver_id' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) {
                    $exists = DB::table('rental_equipments')
                        ->where(['driver_suggestion' => $value, 'company_id' => $this->user()->company_id])
                        ->count();
                    if ($exists == 1) {
                        $fail('Motorista está sendo utilizado em alguma locação. Realize a troca do motorista na locação para continuar.');
                    } elseif ($exists > 1) {
                        $fail('Motorista está sendo utilizado em algumas locações. Realize a troca do motorista nas locações para continuar.');
                    } else {
                        $exists = DB::table('vehicles')
                            ->where(['driver_id' => $value, 'company_id' => $this->user()->company_id])
                            ->count();
                        if ($exists == 1) {
                            $fail('Motorista está sendo utilizado em algum cadastro de veículo. Realize a troca do motorista no cadastro de veículo para continuar.');
                        } elseif ($exists > 1) {
                            $fail('Motorista está sendo utilizado em alguns cadastros de veículo. Realize a troca do motorista nos cadastros de veículo para continuar.');
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
            'driver_id.*' => 'Não foi possível localizar o motorista!'
        ];
    }

    /**
     * Get the proper failed validation response for the request.
     *
     * @param array $errors
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function response(array $errors)
    {
        if ($this->isAjax()) return response()->json(['errors' => $errors]);

        return $this->redirector->to($this->getRedirectUrl())
            ->withInput($this->except($this->dontFlash))
            ->withErrors($errors, $this->errorBag);
    }
}
