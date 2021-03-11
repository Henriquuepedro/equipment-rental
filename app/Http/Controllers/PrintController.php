<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Company;
use App\Models\Rental;
use App\Models\RentalEquipment;
use App\Models\RentalPayment;
use Barryvdh\DomPDF\PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PrintController extends Controller
{
    private $pdf;
    private $rental_equipment;
    private $rental;
    private $client;
    private $company;
    private $rental_payment;

    public function __construct(PDF $pdf, RentalEquipment $rental_equipment, Rental $rental, Client $client, Company $company, RentalPayment $rental_payment)
    {
        $this->pdf = $pdf;
        $this->rental_equipment = $rental_equipment;
        $this->rental = $rental;
        $this->client = $client;
        $this->company = $company;
        $this->rental_payment = $rental_payment;
    }

    public function rental(int $rental)
    {
        $company_id = Auth::user()->company_id;

        $rental = $this->rental->getRental($rental, $company_id);

        if (!$rental)
            return redirect()->route('rental.index');

        $equipments = $this->rental_equipment->getEquipments($company_id, $rental->id);
        $client = $this->client->getClient($rental->client_id, $company_id);
        $company = $this->company->getCompany($company_id);
        $payments = $this->rental_payment->getPayments($company_id, $rental->id);
//        dd($rental);

        $rental->address_zipcode = $rental->address_zipcode ? $this->mask($rental->address_zipcode, '##.###-###') : null;
        $company->cpf_cnpj = $this->formatCPF_CNPJ($company->cpf_cnpj);
        $company->cep = $company->cep ? $this->mask($company->cep, '##.###-###') : null;
        $client->cpf_cnpj = $this->formatCPF_CNPJ($client->cpf_cnpj);

        $contentRecibo = [
            'company'    => $company,
            'rental'     => $rental,
            'client'     => $client,
            'equipments' => $equipments,
            'payments'   => $payments
        ];

        $pdf = $this->pdf->loadView('print.rental', $contentRecibo);
        //return $pdf->download('rental.pdf');
        return $pdf->stream();
    }

    public function budget(int $budget)
    {
        return 'Em construção...';
    }
}
