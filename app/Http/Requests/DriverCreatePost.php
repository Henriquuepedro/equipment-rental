<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DriverCreatePost extends FormRequest
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
            'name'      => [
                'required',
                function ($attribute, $value, $fail) {
                    $exists = DB::table('drivers')->where(['name' => $this->get('name'), 'company_id' => $this->user()->company_id])->count();
                    if ($exists) {
                        $fail('Nome do motorista já está em uso');
                    }
                }
            ],
            'email'     => 'email:rfc,dns|nullable',
            'phone'     => 'min:13|max:14|nullable',
            'cpf'       => [
                function ($attribute, $value, $fail) {
                    $cpf = filter_var(onlyNumbers($this->get('cpf')), FILTER_SANITIZE_NUMBER_INT);
                    if (!empty($cpf)) {
                        $exists = DB::table('drivers')->where(['cpf' => $cpf, 'company_id' => $this->user()->company_id])->count();
                        if ($exists) {
                            $fail('CPF do motorista já está em uso');
                        }
                    }
                }
            ],
            'rg'        => 'numeric|nullable',
            'cnh'       => 'numeric|nullable',
            'cnh_exp'   => 'date|nullable'
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
            'name.required' => 'O Nome é obrigatório',
            'email.email'   => 'O endereço de e-mail deve ser um e-mail válido',
            'phone.min'     => 'O telefone deve conter o DDD e o número telefônico',
            'phone.max'     => 'O telefone deve conter o DDD e o número telefônico',
            'rg.numeric'    => 'O RG deve conter apenas números',
            'cnh.numeric'   => 'O RG deve conter apenas números',
            'cnh_exp.date'  => 'A data de expiração da CNH deve ser uma data válida',
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
