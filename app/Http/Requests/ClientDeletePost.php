<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class ClientDeletePost extends FormRequest
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
            'client_id' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) {
                    $exists = DB::table('rentals')
                        ->where(['client_id' => $value, 'company_id' => $this->user()->company_id])
                        ->count();
                    if ($exists == 1) {
                        $fail('Cliente está sendo utilizado em alguma locação. Realize a troca do cliente na locação para continuar.');
                    } elseif ($exists > 1) {
                        $fail('Cliente está sendo utilizado em algumas locações. Realize a troca do cliente nas locações para continuar.');
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
            'client_id.*' => 'Não foi possível localizar o cliente!'
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
        if (isAjax()) return response()->json(['errors' => $errors]);

        return $this->redirector->to($this->getRedirectUrl())
            ->withInput($this->except($this->dontFlash))
            ->withErrors($errors, $this->errorBag);
    }
}
