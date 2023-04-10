<?php

namespace App\Exports;
use App\Models\Client;
use App\Models\Driver;
use App\Models\Equipment;
use App\Models\Provider;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;
use Maatwebsite\Excel\Concerns\FromCollection;

class RegistersExport implements FromCollection
{
    private string $type;
    private int $company_id;
    private array $fileds;
    private Client $client;
    private Equipment $equipment;
    private Vehicle $vehicle;
    private Driver $driver;
    private Provider $provider;

    public function __construct(string $type, int $company_id, array $fileds)
    {
        $this->type         = $type;
        $this->company_id   = $company_id;
        $this->fileds       = $fileds;

        $this->client       = new Client;
        $this->driver       = new Driver;
        $this->equipment    = new Equipment;
        $this->provider     = new Provider;
        $this->vehicle      = new Vehicle;
    }

    /**
     * @return Collection
     */
    public function collection(): Collection
    {
        $result = match ($this->type) {
            'client' => $this->client->getClients($this->company_id, null, null, null, array(), $this->fileds)->toArray(),
            'driver' => $this->driver->getDrivers($this->company_id, null, null, null, array(), $this->fileds)->toArray(),
            'equipment' => $this->equipment->getEquipments($this->company_id, null, null, null, array(), false, $this->fileds)->toArray(),
            'provider' => $this->provider->getProviders($this->company_id, null, null, null, array(), $this->fileds)->toArray(),
            'vehicle' => $this->vehicle->getVehicles($this->company_id, null, null, null, array(), $this->fileds)->toArray(),
            default => array(),
        };

        $result = array_map(function($register) {
            foreach ($register as $field_key => $field_value) {
                if (strtotime($field_value) !== false) {
                    $register[$field_key] = dateInternationalToDateBrazil($field_value) ?? $field_value;
                }
                if (likeText('%cpf%', $field_key) || likeText('%cnpj%', $field_key)) {
                    $register[$field_key] = formatCPF_CNPJ($field_value) ?? $field_value;
                }
                if (likeText('%phone%', $field_key)) {
                    $register[$field_key] = formatPhone($field_value, '') ?? $field_value;
                }
            }

            return $register;
        }, $result);

        array_unshift(
            $result,
            array_map(function ($field) {
                return Lang::get("field.$field");
            }, $this->fileds)
        );

        return new Collection($result);
    }
}
