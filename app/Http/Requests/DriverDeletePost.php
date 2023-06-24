<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class DriverDeletePost extends FormRequest
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
            'driver_id' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) {
                    $exists = DB::table('rental_equipments')
                        ->where(['driver_suggestion' => $value, 'company_id' => $this->user()->company_id])
                        ->count();

                    if ($exists > 0) {
                        $fail('Motorista está em uso, não será possível excluir!');
                    } else {
                        $exists = DB::table('vehicles')
                            ->where(['driver_id' => $value, 'company_id' => $this->user()->company_id])
                            ->count();

                        if ($exists > 0) {
                            $fail('Motorista está em uso no cadastro de veículo, não será possível excluir!');
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
            'driver_id.*' => 'Não foi possível localizar o motorista!'
        ];
    }

    /**
     * Get the proper failed validation response for the request.
     *
     * @param array $errors
     * @return JsonResponse|RedirectResponse
     */
    public function response(array $errors): JsonResponse|RedirectResponse
    {
        if (isAjax()) {
            return response()->json(['errors' => $errors]);
        }

        return $this->redirector->to($this->getRedirectUrl())
            ->withInput($this->except($this->dontFlash))
            ->withErrors($errors, $this->errorBag);
    }
}
