<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ProviderCreatePost extends FormRequest
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
            'type_person'   => 'size:2',
            'name'   => [
                'required',
                function ($attribute, $value, $fail) {
                    $exists = DB::table('providers')->where(['name' => $this->get('name'), 'company_id' => $this->user()->company_id])->count();
                    if ($exists) {
                        $fail($this->get('type_person') == 'pf' ? 'Nome do fornecedor já está em uso' : 'Razão Social do fornecedor já está em uso');
                    }
                }
            ],
            'phone_1'       => 'min:13|max:14|nullable',
            'phone_2'       => 'min:13|max:14|nullable',
            'email'         => 'email:rfc,dns|nullable',
            'cpf_cnpj'      => [
                function ($attribute, $value, $fail) {
                    $cpf_cnpj = filter_var(onlyNumbers($this->get('cpf_cnpj')), FILTER_SANITIZE_NUMBER_INT);

                    if (!empty($cpf_cnpj)) {
                        $exists = DB::table('providers')->where(['cpf_cnpj' => $cpf_cnpj, 'company_id' => $this->user()->company_id])->count();
                        if ($exists) {
                            $fail($this->get('type_person') == 'pf' ? 'CPF do fornecedor já está em uso' : 'CNPJ do fornecedor já está em uso');
                        }
                    }
                }
            ],
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
            'type_person.size'  => 'Tipo de pessoa mal informado, selecione entre Pessoa Física ou Pesso Jurídica',
            'name.required'     => $this->get('type_person') == 'pf' ? 'O Nome é obrigatório' : 'A Razão Social é obrigatório',
            'phone_1.min'       => 'O telefone principal deve conter o DDD e o número telefônico',
            'phone_1.max'       => 'O telefone principal deve conter o DDD e o número telefônico',
            'phone_2.min'       => 'O telefone secundário deve conter o DDD e o número telefônico',
            'phone_2.max'       => 'O telefone secundário deve conter o DDD e o número telefônico',
            'email.email'       => 'O endereço de e-mail deve ser um e-mail válido'
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
