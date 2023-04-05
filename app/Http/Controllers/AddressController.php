<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    private $address;

    public function __construct(Address $address)
    {
        $this->address = $address;
    }

    public function getAddresses(Request $request)
    {
        $company_id  = $request->user()->company_id;
        $client_id   = $request->client_id;
        $addressData = [];

        $addresses = $this->address->getAddressClient($company_id, $client_id);

        foreach ($addresses as $address) {
            array_push($addressData, ['id' => $address->id, 'name' => $address->name_address]);
        }

        return response()->json(['data' => $addressData]);
    }

    public function getAddress(Request $request)
    {
        $company_id  = $request->user()->company_id;
        $client_id   = $request->client_id;
        $address_id  = $request->address_id;

        $addresses = $this->address->getAddress($company_id, $client_id, $address_id);

        return response()->json($addresses);
    }
}
