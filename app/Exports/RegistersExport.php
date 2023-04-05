<?php

namespace App\Exports;
use App\Models\Client;
use App\Models\Driver;
use App\Models\Equipment;
use App\Models\Provider;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Support\Collection;
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
        $this->equipment    = new Equipment;
        $this->vehicle      = new Vehicle;
        $this->driver       = new Driver;
        $this->provider     = new Provider;
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        $result = match ($this->type) {
            'client' => $this->client->getClients($this->company_id, null, null, null, array(), $this->fileds)->toArray(),
            'equipment' => $this->equipment->getEquipments($this->company_id, null, null, null, array(), $this->fileds)->toArray(),
            default => array(),
        };

        array_unshift($result, $this->fileds);

        return new Collection($result);
    }
}
