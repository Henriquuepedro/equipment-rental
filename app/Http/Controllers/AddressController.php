<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    private Address $address;

    public function __construct()
    {
        $this->address = new Address();
    }

    public function getAddresses(int $client_id = null): JsonResponse
    {
        if (is_null($client_id)) {
            return response()->json();
        }

        $company_id = Auth::user()->__get('company_id');
        $addressData = [];

        $addresses = $this->address->getAddressClient($company_id, $client_id);

        foreach ($addresses as $address) {
            $addressData[] = ['id' => $address->id, 'name' => $address->name_address];
        }

        return response()->json(['data' => $addressData]);
    }

    public function getAddress(int $client_id = null, int $address_id = null): JsonResponse
    {
        if (is_null($client_id) || is_null($address_id)) {
            return response()->json();
        }

        $company_id = Auth::user()->__get('company_id');

        $addresses = $this->address->getAddress($company_id, $client_id, $address_id);

        return response()->json($addresses);
    }
}
