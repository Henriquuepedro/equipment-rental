<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Plan;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected string $redirectTo = RouteServiceProvider::HOME;

    private Plan $plan;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
        $this->plan = new Plan();
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        $data['cpf_cnpj'] = onlyNumbers($data['cpf_cnpj']);
        $validator_cpf_cnpj = $data['type_person'] === 'pj' ? 'cnpj' : 'cpf';

        return Validator::make($data, [
            'type_person'   => ['size:2'],
            'name'          => ['required', 'string', 'max:255'],
            'cpf_cnpj'      => ['required', $validator_cpf_cnpj, 'unique:companies'],
            'phone_1'       => ['required', 'celular_com_ddd'],
            'contact'       => ['required'],
            'email'         => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password'      => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'type_person.*' => 'Informe Pessoa Física ou Jurídica.',
            'name.*' => $data['type_person'] === 'pj' ? 'Razão Social deve ser informada e conter até 255 caracteres.' : 'Nome Completo deve ser informada e conter até 255 caracteres.',
            'phone_1.*' => 'Telefone informado é inválido.',
            'contact.*' => 'Nome do Contato deve ser informado.',
            'email.required' => 'E-mail deve ser informado.',
            'email.email' => 'E-mail informado é inválido.',
            'email.unique' => 'E-mail informado já está em uso. Caso não se lembre da senha, clique em esqueci minha senha.',
            'password.required' => 'Senha deve ser informado.',
            'password.min' => 'Senha deve conter 8 caracteres no mínimo.',
            'password.confirmed' => 'Senhas devem ser iguais.',
            'cpf_cnpj.cnpj' => 'CNPJ informado é inválido.',
            'cpf_cnpj.cpf' => 'CPF informado é inválido.'
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
        $plan = $this->plan->getPlanAtLowerPrice();

        $company = Company::create([
            'name'                  => $data['name'],
            'fantasy'               => $data['fantasy'],
            'type_person'           => $data['type_person'],
            'cpf_cnpj'              => onlyNumbers($data['cpf_cnpj']),
            'email'                 => $data['email'],
            'phone_1'               => onlyNumbers($data['phone_1']),
            'phone_2'               => onlyNumbers($data['phone_2']),
            'contact'               => $data['contact'],
            'plan_id'               => $plan->id,
            'plan_expiration_date'  => sumDate(dateNowInternational(), null, null, 15)
        ]);

        return User::create([
            'name'          => $data['contact'],
            'email'         => $data['email'],
            'phone'         => onlyNumbers($data['phone_1']),
            'password'      => Hash::make($data['password']),
            'company_id'    => $company->id,
            'active'        => 1,
            'permission'    => '[]',
            'style_template'=> User::$TYPE_USER['black'],
            'type_user'     => User::$TYPE_USER['admin'],
        ]);
    }
}
