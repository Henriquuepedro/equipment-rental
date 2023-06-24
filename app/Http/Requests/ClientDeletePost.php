<?php

namespace App\Http\Requests;

use App\Models\Rental;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ClientDeletePost extends FormRequest
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
            'client_id' => [
                'required',
                'numeric',
                function ($attribute, $value, Closure $fail) {
                    if (DB::table('rentals')->where('client_id', $value)->count()) {
                        $fail('Cliente está em uso em locação, não será possível excluir!');
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
            'client_id.required' => 'Não foi possível localizar o cliente!',
            'client_id.numeric'  => 'Não foi possível localizar o cliente!'
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
