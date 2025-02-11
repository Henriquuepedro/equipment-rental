<?php

namespace App\Http\Controllers;

use App\Models\BillToPayPayment;
use App\Models\Budget;
use App\Models\BudgetEquipment;
use App\Models\BudgetPayment;
use App\Models\Client;
use App\Models\Company;
use App\Models\DisposalPlace;
use App\Models\Driver;
use App\Models\Equipment;
use App\Models\EquipmentRentalMtr;
use App\Models\FormPayment;
use App\Models\Provider;
use App\Models\Rental;
use App\Models\RentalEquipment;
use App\Models\RentalMtr;
use App\Models\RentalPayment;
use App\Models\Residue;
use App\Models\Vehicle;
use App\Services\PrintService;
use Barryvdh\DomPDF\PDF;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class PrintController extends Controller
{
    private PrintService $print_service;

    public function __construct(PDF $pdf)
    {
        $this->print_service = new PrintService($pdf);
    }

    public function rental(int $rental, bool $return_base64 = false): Response|RedirectResponse|string
    {
        return $this->print_service->rental($rental, $return_base64);
    }

    public function budget(int $budget, bool $return_base64 = false): Response|RedirectResponse
    {
        return $this->print_service->budget($budget, $return_base64);
    }

    public function reportRental(Request $request): Response|RedirectResponse
    {
        return $this->print_service->reportRental($request);
    }

    public function reportBill(Request $request): Response|RedirectResponse
    {
        return $this->print_service->reportBill($request);
    }

    public function rentalMtr(int $rental_mtr_id = null): Response|RedirectResponse
    {
        return $this->print_service->rentalMtr($rental_mtr_id);
    }
}
