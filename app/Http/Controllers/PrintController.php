<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\BudgetEquipment;
use App\Models\BudgetPayment;
use App\Models\Client;
use App\Models\Company;
use App\Models\Driver;
use App\Models\Rental;
use App\Models\RentalEquipment;
use App\Models\RentalPayment;
use Barryvdh\DomPDF\PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

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
    private $driver;

    public function __construct(PDF $pdf)
    {
        $this->pdf = $pdf;
        $this->rental_equipment = new RentalEquipment();
        $this->rental = new Rental();
        $this->client = new Client();
        $this->company = new Company();
        $this->rental_payment = new RentalPayment();
        $this->budget = new Budget();
        $this->budget_equipment = new BudgetEquipment();
        $this->budget_payment = new BudgetPayment();
        $this->driver = new Driver();
        $this->pdf->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);

        define("DOMPDF_ENABLE_REMOTE", false);
    }

    public function rental(int $rental)
    {
        $contentRecibo = $this->getDataFormatBudgetRental($rental, false);
        if (!$contentRecibo) {
            return redirect()->route('rental.index');
        }

        $contentRecibo['company']->logo = $this->getImageCompanyBase64($contentRecibo['company']);

        $pdf = $this->pdf->loadView('print.rental', $contentRecibo);
        return $pdf->stream();
    }

    public function budget(int $budget)
    {
        $contentRecibo = $this->getDataFormatBudgetRental($budget, true);
        if (!$contentRecibo) {
            return redirect()->route('budget.index');
        }

        $contentRecibo['company']->logo = $this->getImageCompanyBase64($contentRecibo['company']);

        $pdf = $this->pdf->loadView('print.rental', $contentRecibo);
        return $pdf->stream();
    }

    public function getImageCompanyBase64(object $company): string
    {
        if ($company->logo) {
            $image = "assets/images/company/$company->id/$company->logo";
        } else {
            $image = "assets/images/company/company.png";
        }

        $extension = File::extension($image);

        $img_to_base64 = base64_encode(File::get($image));
        return "data:image/$extension;base64, $img_to_base64";
    }

    private function getDataFormatBudgetRental(int $code, bool $budget)
    {
        $company_id = Auth::user()->company_id;

        if ($budget) {
            $rentalBudget = $this->budget->getBudget($code, $company_id);
        } else {
            $rentalBudget = $this->rental->getRental($code, $company_id);
        }

        if (!$rentalBudget) {
            return false;
        }

        if ($budget) {
            $equipments = $this->budget_equipment->getEquipments($company_id, $rentalBudget->id);
        } else {
            $equipments = $this->rental_equipment->getEquipments($company_id, $rentalBudget->id);
        }

        $client     = $this->client->getClient($rentalBudget->client_id, $company_id);
        $company    = $this->company->getCompany($company_id);

        if ($budget) {
            $payments = $this->budget_payment->getPayments($company_id, $rentalBudget->id);
        } else {
            $payments = $this->rental_payment->getPayments($company_id, $rentalBudget->id);
        }

        $rentalBudget->address_zipcode  = formatZipcode($rentalBudget->address_zipcode);
        $company->cpf_cnpj              = formatCPF_CNPJ($company->cpf_cnpj);
        $company->cep                   = formatZipcode($company->cep);
        $client->cpf_cnpj               = formatCPF_CNPJ($client->cpf_cnpj);

        return [
            'company'    => $company,
            'rental'     => $rentalBudget,
            'client'     => $client,
            'equipments' => $equipments,
            'payments'   => $payments,
            'budget'     => $budget
        ];
    }

    public function reportRental(Request $request)
    {
        $company_id             = $request->user()->company_id;
        $type_report            = $request->input('type_report');
        $client                 = $request->input('client');
        $driver                 = $request->input('driver');
        $status                 = $request->input('status');
        $state                  = $request->input('state');
        $city                   = $request->input('city');
        $date_filter            = $request->input('date_filter');
        $interval_dates         = explode(' - ', $request->input('intervalDates'));
        $data_filter_view_pdf   = array();

        $date_start     = dateBrazilToDateInternational($interval_dates[0]);
        $date_end       = dateBrazilToDateInternational($interval_dates[1]);

        $filters = array(
            '_date_start'    => $date_start,
            '_date_end'      => $date_end,
            '_date_filter'   => $date_filter
        );

        switch ($date_filter) {
            case 'created':
                $date_filter_str = 'Lançamento';
                break;
            case 'delivered':
                $date_filter_str = 'Entregue';
                break;
            case 'withdrawn':
                $date_filter_str = 'Retirado';
                break;
            default:
                $date_filter_str = '';
        }

        $data_filter_view_pdf["Data de $date_filter_str"] = "de $interval_dates[0] até $interval_dates[1]";

        if (!empty($client)) {
            $client_data = $this->client->getClient($client, $company_id);
            $filters['rentals.client_id'] = $client;
            $data_filter_view_pdf['Cliente'] = $client_data->name;
        }
        if (!empty($driver)) {
            $driver_data = $this->driver->getDriver($driver, $company_id);
            $filters['_driver'] = $driver;
            $data_filter_view_pdf['Motorista'] = $driver_data->name;
        }
        if (!empty($status)) {
            $filters['_status'] = $status;
            switch ($status) {
                case 'deliver':
                    $status_str = 'Para Entregar';
                    break;
                case 'withdraw':
                    $status_str = 'Para Retirar';
                    break;
                case 'finished':
                    $status_str = 'Finalizada';
                    break;
                default:
                    $status_str = '';
            }
            $data_filter_view_pdf['Situação'] = $status_str;
        }
        if (!empty($state)) {
            $filters['rentals.address_state'] = $state;
            $data_filter_view_pdf['Estado'] = $state;
        }
        if (!empty($city)) {
            $filters['rentals.address_city'] = $city;
            $data_filter_view_pdf['Cidade'] = $city;
        }

        $rentals = $this->rental->getRentalsWithFilters($company_id, $filters, $type_report === 'synthetic');
        if (!$rentals) {
            return redirect()->route('report.rental')
                ->with('warning', "Nenhum registro encontrado para o filtro aplicado!");
        }

        $company_data = $this->company->getCompany($company_id);
        $contentPrint = [
            'company'               => $company_data,
            'logo_company'          => $this->getImageCompanyBase64($company_data),
            'rentals'               => $rentals,
            'data_filter_view_pdf'  => $data_filter_view_pdf,
            'type_report'           => $type_report
        ];

        /*$company = $contentPrint['company'];
        $logo_company = $contentPrint['logo_company'];

        return view('print.report.rental', compact('company', 'logo_company', 'rentals', 'data_filter_view_pdf', 'type_report'));*/

        $pdf = $this->pdf->loadView('print.report.rental', $contentPrint);
        return $pdf->stream();
    }
}
