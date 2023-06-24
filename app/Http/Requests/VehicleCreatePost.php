<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class VehicleCreatePost extends FormRequest
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
            'name'   => [
                'required',
                function ($attribute, $value, $fail) {
                    $exists = DB::table('vehicles')
                        ->where(['name' => $this->get('name'), 'company_id' => $this->user()->company_id])
                        ->count();
                    if ($exists) $fail('Nome do veículo já está em uso');
                }
            ],
            'reference' => [
                function ($attribute, $value, $fail) {
                    if (!empty($this->get('reference'))) {
                        $exists = DB::table('vehicles')
                            ->where(['reference' => $this->get('reference'), 'company_id' => $this->user()->company_id])
                            ->count();
                        if ($exists) $fail('Referência do veículo já está em uso');
                    }
                }
            ],
            'board'     => [
                function ($attribute, $value, $fail) {
                    if (!empty($this->get('board'))) {
                        $exists = DB::table('vehicles')
                            ->where(['board' => $this->get('board'), 'company_id' => $this->user()->company_id])
                            ->count();
                        if ($exists) $fail('Placa do veículo já está em uso');
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
            'name.required'  => 'Nome do veículo é obrigatório',
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
