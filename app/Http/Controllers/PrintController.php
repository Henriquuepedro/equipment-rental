<?php

namespace App\Http\Controllers;

use App\Models\BillToPayPayment;
use App\Models\Budget;
use App\Models\BudgetEquipment;
use App\Models\BudgetPayment;
use App\Models\Client;
use App\Models\Company;
use App\Models\Driver;
use App\Models\Equipment;
use App\Models\FormPayment;
use App\Models\Provider;
use App\Models\Rental;
use App\Models\RentalEquipment;
use App\Models\RentalPayment;
use App\Models\Vehicle;
use Barryvdh\DomPDF\PDF;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class PrintController extends Controller
{
    private $pdf;
    private $rental_equipment;
    private $rental;
    private $client;
    private $provider;
    private $company;
    private $rental_payment;
    private $budget;
    private $budget_equipment;
    private $budget_payment;
    private $driver;
    private $vehicle;
    private $equipment;
    private $form_payment;
    private $bill_to_pay_payment;

    public function __construct(PDF $pdf)
    {
        $this->pdf                  = $pdf;
        $this->rental_equipment     = new RentalEquipment;
        $this->rental               = new Rental;
        $this->client               = new Client;
        $this->provider             = new Provider;
        $this->company              = new Company;
        $this->rental_payment       = new RentalPayment;
        $this->budget               = new Budget;
        $this->budget_equipment     = new BudgetEquipment;
        $this->budget_payment       = new BudgetPayment;
        $this->driver               = new Driver;
        $this->vehicle              = new Vehicle;
        $this->equipment            = new Equipment;
        $this->form_payment         = new FormPayment;
        $this->bill_to_pay_payment  = new BillToPayPayment;

        $this->pdf->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);

        define("DOMPDF_ENABLE_REMOTE", false);
    }

    public function rental(int $rental)
    {
        $contentRecibo = $this->getDataFormatBudgetRental($rental, false);
        if (!$contentRecibo) {
            return redirect()->route('rental.index');
        }

        $contentRecibo['company']->logo = getImageCompanyBase64($contentRecibo['company']);

        $pdf = $this->pdf->loadView('print.rental', $contentRecibo);
        return $pdf->stream();
    }

    public function budget(int $budget)
    {
        $contentRecibo = $this->getDataFormatBudgetRental($budget, true);
        if (!$contentRecibo) {
            return redirect()->route('budget.index');
        }

        $contentRecibo['company']->logo = getImageCompanyBase64($contentRecibo['company']);

        $pdf = $this->pdf->loadView('print.rental', $contentRecibo);
        return $pdf->stream();
    }

    private function getDataFormatBudgetRental(int $code, bool $budget)
    {
        $company_id = Auth::user()->__get('company_id');

        if ($budget) {
            $rentalBudget = $this->budget->getBudget($code, $company_id);
        } else {
            $rentalBudget = $this->rental->getRental($company_id, $code);
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
        $company_id             = hasAdminMaster() ? $request->input('company') : $request->user()->company_id;
        $type_report            = $request->input('type_report');
        $client                 = $request->input('client');
        $driver                 = $request->input('driver');
        $vehicle                = $request->input('vehicle');
        $equipment              = $request->input('equipment');
        $status                 = $request->input('status');
        $state                  = $request->input('state');
        $city                   = $request->input('city');
        $date_filter            = $request->input('date_filter');
        $interval_dates         = explode(' - ', $request->input('intervalDates'));
        $data_filter_view_pdf   = array();

        if (empty($company_id)) {
            return redirect()->route('report.rental')
                ->withErrors("Selecione uma empresa.");
        }

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
            $filters['rentals.client_id'] = ['=', $client];
            $data_filter_view_pdf['Cliente'] = $client_data->name;
        }

        if (!empty($driver)) {
            $driver_data = $this->driver->getDriver($driver, $company_id);
            $filters['_driver'] = $driver;
            $data_filter_view_pdf['Motorista'] = $driver_data->name;
        }

        if (!empty($vehicle)) {
            $vehicle_data = $this->vehicle->getVehicle($vehicle, $company_id);
            $filters['_vehicle'] = $vehicle;
            $data_filter_view_pdf['Veículo'] = $vehicle_data->name;
        }

        if (!empty($equipment)) {
            $equipment_data = $this->equipment->getEquipment($equipment, $company_id);
            $filters['rental_equipments.equipment_id'] = ['=', $equipment];
            $data_filter_view_pdf['Equipamento'] = $equipment_data->name ?? "Caçamba {$equipment_data->volume}m³";
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
            $filters['rentals.address_state'] = ['=', $state];
            $data_filter_view_pdf['Estado'] = $state;
        }
        if (!empty($city)) {
            $filters['rentals.address_city'] = ['=', $city];
            $data_filter_view_pdf['Cidade'] = $city;
        }

        $rentals = $this->rental->getRentalsToReportWithFilters($company_id, $filters, $type_report === 'synthetic');
        if (!$rentals) {
            return redirect()->route('report.rental')
                ->withErrors("Nenhum registro encontrado para o filtro aplicado!");
        }

        $company_data = $this->company->getCompany($company_id);
        $contentPrint = [
            'company'               => $company_data,
            'logo_company'          => getImageCompanyBase64($company_data),
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

    public function reportBill(Request $request): Response|RedirectResponse
    {
        $company_id             = hasAdminMaster() ? $request->input('company') : $request->user()->company_id;
        $type_report            = $request->input('type_report');
        $client                 = $request->input('client');
        $provider               = $request->input('provider');
        $bill_type              = $request->input('bill_type');
        $form_payment           = $request->input('form_payment');
        $date_filter            = $request->input('date_filter');
        $bill_status            = $request->input('bill_status');
        $order_by_field         = $request->input('order_by_field');
        $order_by_direction     = $request->input('order_by_direction');
        $interval_dates         = explode(' - ', $request->input('intervalDates'));
        $data_filter_view_pdf   = array();

        if (empty($company_id)) {
            return redirect()->route('report.bill')
                ->withErrors("Selecione uma empresa.");
        }

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
            case 'due':
                $date_filter_str = 'Vencimento';
                break;
            case 'pay':
                $date_filter_str = 'Pagamento';
                break;
            default:
                $date_filter_str = '';
        }

        // Valida se foi enviado 'desc' ou 'asc' pelo usuário.
        if (!in_array($order_by_direction, array('desc', 'asc'))) {
            return redirect()->route('report.bill')
                ->withErrors("Ordenação do relatório incorreta.");
        }

        // Recupera o campo correspondente para ordenar.
        switch ($order_by_field) {
            case 'rental_bill_to_pay':
                $order_by_field = $bill_type === 'receive' ? 'rentals.id' : 'bill_to_pays.id';
                break;
            case 'client_provider':
                $order_by_field = $bill_type === 'receive' ? 'clients.name' : 'providers.name';
                break;
            case 'due_date':
                $order_by_field = $bill_type === 'receive' ? 'rental_payments.due_date' : 'bill_to_pay_payments.due_date';
                break;
            default:
                return redirect()->route('report.bill')
                    ->withErrors("Ordenação do relatório incorreta.");
        }

        $data_filter_view_pdf["Data de $date_filter_str"] = "de $interval_dates[0] até $interval_dates[1]";

        $index_filter_status = $index_filter_bill_status = $bill_type === 'receive' ? 'rental_payments.payment_id' : 'bill_to_pay_payments.payment_id';
        $filters[$index_filter_status] = ['=', null];

        // Se não foi pago, adiciono um diferente de null. IS NOT NULL.
        if ($bill_status === 'paid') {
            $filters[$index_filter_status] = ['!=', null];
        }

        $status_str = '';
        switch ($bill_status) {
            case 'paid':
                $status_str = 'Pago';
                break;
            case 'no_paid':
                $status_str = 'Não Pago';
                break;
        }
        $data_filter_view_pdf['Situação do Lançamento'] = $status_str;

        if ($bill_type === 'receive' && !empty($client)) {
            $client_data = $this->client->getClient($client, $company_id);
            $filters['rentals.client_id'] = ['=', $client];
            $data_filter_view_pdf['Cliente'] = $client_data->name;
        }

        if ($bill_type === 'pay' && !empty($provider)) {
            $provider_data = $this->provider->getProvider($provider, $company_id);
            $filters['bill_to_pays.provider_id'] = ['=', $provider];
            $data_filter_view_pdf['Fornecedor'] = $provider_data->name;
        }

        if ($bill_status === 'paid' && !empty($form_payment)) {
            // Destrói o filtro caso exista como diferente de nulo.
            unset($filters["$index_filter_status"]);
            $form_payment_data = $this->form_payment->getById($form_payment);
            $filters[$index_filter_bill_status] = ['=', $form_payment];
            $data_filter_view_pdf['Forma de Pagamento'] = $form_payment_data->name;
        }

        if ($bill_type === 'receive') {
            $bills = $this->rental_payment->getBillsToReportWithFilters($company_id, $filters, $type_report === 'synthetic', array($order_by_field, $order_by_direction));
        } else {
            $bills = $this->bill_to_pay_payment->getBillsToReportWithFilters($company_id, $filters, $type_report === 'synthetic', array($order_by_field, $order_by_direction));
        }

        if (!$bills) {
            return redirect()->route('report.bill')
                ->withErrors("Nenhum registro encontrado para o filtro aplicado!");
        }

        $company_data = $this->company->getCompany($company_id);
        $contentPrint = [
            'company'               => $company_data,
            'logo_company'          => getImageCompanyBase64($company_data),
            'bills'                 => $bills,
            'data_filter_view_pdf'  => $data_filter_view_pdf,
            'type_report'           => $type_report,
            'bill_type'             => $bill_type,
            'bill_status'           => $bill_status
        ];

        /*$company = $contentPrint['company'];
        $logo_company = $contentPrint['logo_company'];

        return view('print.report.bill', compact('company', 'logo_company', 'bills', 'data_filter_view_pdf', 'type_report', 'bill_type', 'bill_status'));*/

        $pdf = $this->pdf->loadView('print.report.bill', $contentPrint);
        return $pdf->stream();
    }
}
