<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\BudgetEquipment;
use App\Models\BudgetPayment;
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
    private $budget;
    private $budget_equipment;
    private $budget_payment;

    public function __construct(
        PDF $pdf,
        Client $client,
        Company $company,
        Rental $rental,
        RentalEquipment $rental_equipment,
        RentalPayment $rental_payment,
        Budget $budget,
        BudgetEquipment $budget_equipment,
        BudgetPayment $budget_payment
    )
    {
        $this->pdf = $pdf;
        $this->rental_equipment = $rental_equipment;
        $this->rental = $rental;
        $this->client = $client;
        $this->company = $company;
        $this->rental_payment = $rental_payment;
        $this->budget = $budget;
        $this->budget_equipment = $budget_equipment;
        $this->budget_payment = $budget_payment;
    }

    public function rental(int $rental)
    {
        $contentRecibo = $this->getDataFormatBudgetRental($rental, false);
        if (!$contentRecibo)
            return redirect()->route('rental.index');

        $pdf = $this->pdf->loadView('print.rental', $contentRecibo);
        return $pdf->stream();
    }

    public function budget(int $budget)
    {
        $contentRecibo = $this->getDataFormatBudgetRental($budget, true);
        if (!$contentRecibo)
            return redirect()->route('budget.index');

        $pdf = $this->pdf->loadView('print.rental', $contentRecibo);
        return $pdf->stream();
    }

    private function getDataFormatBudgetRental(int $code, bool $budget)
    {
        $company_id = Auth::user()->company_id;

        if ($budget)
            $rentalBudget = $this->budget->getBudget($code, $company_id);
        else
            $rentalBudget = $this->rental->getRental($code, $company_id);

        if (!$rentalBudget)
            return false;

        if ($budget)
            $equipments = $this->budget_equipment->getEquipments($company_id, $rentalBudget->id);
        else
            $equipments = $this->rental_equipment->getEquipments($company_id, $rentalBudget->id);

        $client = $this->client->getClient($rentalBudget->client_id, $company_id);
        $company = $this->company->getCompany($company_id);

        if ($budget)
            $payments = $this->budget_payment->getPayments($company_id, $rentalBudget->id);
        else
            $payments = $this->rental_payment->getPayments($company_id, $rentalBudget->id);

        $rentalBudget->address_zipcode = $rentalBudget->address_zipcode ? $this->mask($rentalBudget->address_zipcode, '##.###-###') : null;
        $company->cpf_cnpj = $this->formatCPF_CNPJ($company->cpf_cnpj);
        $company->cep = $company->cep ? $this->mask($company->cep, '##.###-###') : null;
        $client->cpf_cnpj = $this->formatCPF_CNPJ($client->cpf_cnpj);

        return [
            'company'    => $company,
            'rental'     => $rentalBudget,
            'client'     => $client,
            'equipments' => $equipments,
            'payments'   => $payments,
            'budget'     => $budget
        ];
    }
}
