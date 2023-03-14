<table class='table content analytic'>
    <thead>
        <th class='text-left'>
            Locação
        </th>
        <th class='text-left'>
            Cliente
        </th>
        <th class='text-center'>
            <table class="table equipment-header">
                <tr style="width: 33%">
                    <td class="text-left">Equipamento</td>
                    <td class="text-center">Entregue em</td>
                    <td class="text-right">Retirado em</td>
                </tr>
            </table>
        </th>
        <th class='text-right'>
            Valor
        </th>
    </thead>
    <tbody>

        @php($arrRentals = array())
        @foreach($rentals as $rental)
            @php($arrRentals[$rental->id][] = $rental)
        @endforeach

        @foreach($arrRentals as $rental)
            <tr class="rental-line">
                <td style='width: 10%'>
                    {{ formatCodeRental($rental[0]->code) }}
                </td>
                <td style='width: 30%'>
                    {{ $rental[0]->client_name }}
                </td>
                <td style='width: 50%'>
                    <table class="table equipment">
                        @foreach($rental as $key => $equipment)
                            <tr style="width: 33%" class="@if(count($rental) != 1 && $key == 0) eq-line-one @elseif(count($rental) == 1) eq-line-no-border @else eq-line @endif">
                                <td class="text-left">{{ $equipment['name'] ?? "Caçamba {$equipment['volume']}m³" }}</td>
                                <td class="text-center">{{ formdatDateBrazil($equipment['actual_delivery_date'], DATETIME_BRAZIL_NO_SECONDS) ?? 'Não entregue' }}</td>
                                <td class="text-right">{{ formdatDateBrazil($equipment['actual_withdrawal_date'], DATETIME_BRAZIL_NO_SECONDS) ?? 'Não retirado' }}</td>
                            </tr>
                        @endforeach
                    </table>
                </td>
                <td class='text-right' style='width: 10%'>
                    {{ formatMoney($rental[0]->net_value, 2, 'R$ ') }}
                </td>
            </tr>
            <tr class="address-line">
                <td colspan='4'>
                    <strong>Endereço: </strong>
                    {{ "{$rental[0]->address_name}, {$rental[0]->address_number} - {$rental[0]->address_neigh}" }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
