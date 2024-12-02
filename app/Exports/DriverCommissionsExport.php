<?php

namespace App\Exports;
use App\Models\Client;
use App\Models\Driver;
use App\Models\Equipment;
use App\Models\Provider;
use App\Models\Rental;
use App\Models\RentalEquipment;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;
use Maatwebsite\Excel\Concerns\FromCollection;

class DriverCommissionsExport implements FromCollection
{
    private int $company_id;
    private int $driver_id;
    private string $date_start;
    private string $date_end;
    private RentalEquipment $rental_equipment;

    public function __construct(int $company_id, int $driver_id, string $date_start, string $date_end)
    {
        $this->rental_equipment = new RentalEquipment();
        $this->company_id       = $company_id;
        $this->driver_id        = $driver_id;
        $this->date_start       = $date_start;
        $this->date_end         = $date_end;
    }

    /**
     * @return Collection
     */
    public function collection(): Collection
    {
        $rental_equipments = $this->rental_equipment->getComissionByDriverAndDate($this->company_id, $this->date_start, $this->date_end, $this->driver_id);

        return new Collection($rental_equipments);
    }
}
