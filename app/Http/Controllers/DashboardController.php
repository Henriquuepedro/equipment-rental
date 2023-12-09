<?php

namespace App\Http\Controllers;

use App\Models\BillToPayPayment;
use App\Models\Equipment;
use App\Models\Provider;
use App\Models\Rental;
use App\Models\RentalPayment;
use App\Models\Vehicle;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    private Client $client;
    private Vehicle $vehicle;
    private Equipment $equipment;
    private Rental $rental;
    private Provider $provider;
    private RentalPayment $rental_payment;
    private BillToPayPayment $bill_to_pay_payment;

    public function __construct()
    {
        $this->client               = new Client();
        $this->vehicle              = new Vehicle();
        $this->equipment            = new Equipment();
        $this->rental               = new Rental();
        $this->provider             = new Provider();
        $this->rental_payment       = new RentalPayment();
        $this->bill_to_pay_payment  = new BillToPayPayment();
    }

    public function dashboard(): Factory|View|Application
    {
        $company_id = Auth::user()->__get('company_id');
        $indicator = array(
            'clients'       => $this->client->getCountClientsActive($company_id),
            'vehicles'      => $this->vehicle->getCountVehicles($company_id),
            'equipments'    => $this->equipment->getCountEquipments($company_id),
            'providers'     => $this->provider->getCountProvidersActive($company_id)
        );

        return view('dashboard.dashboard', compact('indicator'));
    }

    public function getBillingOpenLate(): JsonResponse
    {
        $company_id = Auth::user()->__get('company_id');
        $receive = array('total_value' => 0, 'total_count' => 0);
        $pay     = array('total_value' => 0, 'total_count' => 0);

        if (hasPermission('BillsToReceiveView')) {
            $receive = $this->rental_payment->getBillLate($company_id);
        }
        if (hasPermission('BillsToPayView')) {
            $pay = $this->bill_to_pay_payment->getBillLate($company_id);
        }

        return response()->json(array(
            'receive'   => $receive,
            'pay'       => $pay
        ));
    }

    public function getBillsForMonths(int $months): JsonResponse
    {
        if (!hasPermission('BillsToReceiveView')) {
            return response()->json();
        }

        $company_id = Auth::user()->__get('company_id');
        $response_months = array();

        for ($month = $months; $month > 0; $month--) {
            $year_month = date('Y-m', strtotime(subDate(dateNowInternational(), null, ($month - 1))));
            $exp_year_month = explode('-', $year_month);

            $response_months[SHORT_MONTH_NAME_PT[$exp_year_month[1]] . '/' . substr($exp_year_month[0], 2, 4)] = array(
                'receive' => $this->rental_payment->getBillsForMonth($company_id, $exp_year_month[0], $exp_year_month[1]),
                'pay' => $this->bill_to_pay_payment->getBillsForMonth($company_id, $exp_year_month[0], $exp_year_month[1])
            );
        }

        return response()->json($response_months);
    }
}
