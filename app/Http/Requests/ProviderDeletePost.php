<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class ProviderDeletePost extends FormRequest
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
            'provider_id' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) {
                    $exists = DB::table('bills_to_pay')
                        ->where(['provider_id' => $value, 'company_id' => $this->user()->company_id])
                        ->count();
                    if ($exists == 1) {
                        $fail('Fornecedor está sendo utilizado em alguma locação. Realize a troca do fornecedor na locação para continuar.');
                    } elseif ($exists > 1) {
                        $fail('Fornecedor está sendo utilizado em algumas locações. Realize a troca do fornecedor nas locações para continuar.');
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
            'provider_id.*' => 'Não foi possível localizar o fornecedor!'
        ];
    }

    /**
     * Get the proper failed validation response for the request.
     *
     * @param array $errors
     * @return JsonResponse|RedirectResponse
     */
    public function response(array $errors)
    {
        if (isAjax()) {
            return response()->json(['errors' => $errors]);
        }

        return $this->redirector->to($this->getRedirectUrl())
            ->withInput($this->except($this->dontFlash))
            ->withErrors($errors, $this->errorBag);
    }
}
