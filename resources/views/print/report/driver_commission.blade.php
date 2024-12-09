<html>
    <head>
        <title>Relatório de Locações</title>
        <style>
            @page {
                margin: 30px 25px;
            }

            body{
                font-family: 'Microsoft YaHei','Source Sans Pro', sans-serif;
                font-size:13px;

            }
            .table  {
                border-collapse:collapse;
                font-size: 11px;
                border: 0;
                width: 100%;
                margin: 0;
                cellspacing: 0;
            }
            .text-center{
                text-align: center !important
            }
            .text-right{
                text-align: right !important
            }
            .text-left{
                text-align: left !important
            }
            .table.header tr td.data-filter {
                border-top: 1px solid #000;
                border-right: 1px solid #000;
                text-align: left;
                padding-left: 30%;
            }
            .table.header tr .img {
                border-top: 1px solid #000;
                border-left: 1px solid #000;
                padding: 5px;
            }
            .table.header tr .img img{
                max-width: 110px;
                max-height: 110px
            }
            .table.header {
                border-bottom: 1px solid #000;
            }
            .table.content {
                margin-top: 10px
            }
            .table.equipment {
                padding: 1px 5px
            }
            .table.equipment-header td{
                font-weight: bold;
                padding: 0 5px
            }
            .table.content.analytic tbody tr.rental-line td {
                border-top: 1px solid #000;
            }
            .table.content.analytic tbody .table.equipment .eq-line td {
                border-top: 1px solid #999;
            }
            .table.content.analytic .equipment-header td,
            .table.content.analytic tbody .table.equipment .eq-line-no-border td,
            .table.content.analytic tbody .table.equipment .eq-line-one td {
                border-top: 1px solid #fff !important;
            }
            .table.footer {
                margin-top: 20px;
            }
        </style>
    </head>
    <body>
        <table class='table date'>
            <tr>
                <td class='text-right' style='width: 100%'>
                    {{ dateNowInternational(null, DATETIME_BRAZIL_NO_SECONDS) }}
                </td>
            </tr>
        </table>
        <table class='table header'>
            <tr>
                <td class='img' style='width: 15%'>
                    <img src='{{$logo_company}}' alt="{{ $company->name }}"/>
                </td>
                <td class='data-filter' style='width: 85%'>
                    <p style="margin: 0"><strong>Motorista:</strong> {{ $driver_name }}</p>
                    <p style="margin: 0"><strong>Comissão:</strong> {{ formatMoney($commission) }}%</p>
                    <p style="margin: 0"><strong>Data:</strong> {{ dateInternationalToDateBrazil($date_start) }} - {{ dateInternationalToDateBrazil($date_end) }}</p>
                </td>
            </tr>
        </table>

        <table class='table content'>
            <thead>
                <th class='text-left'>
                    Locação
                </th>
                <th class='text-left'>
                    Equipamento
                </th>
                <th class='text-left'>
                    Comissão
                </th>
                <th class='text-left'>
                    Data de entrega
                </th>
                <th class='text-left'>
                    Data de retirada
                </th>
            </thead>
            <tbody>
            @php
                $total_commission_value = 0;
            @endphp
            @foreach($data as $equipment)
                <tr>
                    <td class='info' style='width: 10%'>
                        {{ formatCodeIndex($equipment['code']) }}
                    </td>
                    <td class='info' style='width: 45%'>
                        {{ $equipment['name'] ?? 'Caçamba '.$equipment['volume'].'m³' }} - {{ $equipment['reference'] }} {!! $equipment['exchange_rental_equipment_id'] ? '<b>(TROCA)</b>' : '' !!}
                    </td>
                    <td class='info' style='width: 10%'>
                        @php
                            if ($equipment['actual_driver_delivery'] == $driver_id && $equipment['actual_driver_withdrawal'] == $driver_id) {
                                $equipment['total_value'] *= 2;
                            }
                            $commission_value = $equipment['total_value'] * ($commission / 100);
                            $total_commission_value += $commission_value
                        @endphp
                        {{ formatMoney($commission_value, 2, 'R$ ') }}
                    </td>
                    <td class='info' style='width: 15%'>
                        {{ $equipment['actual_driver_delivery'] == $driver_id ? dateInternationalToDateBrazil($equipment['actual_delivery_date'], DATETIME_BRAZIL_NO_SECONDS) : '' }}
                    </td>
                    <td class='info' style='width: 15%'>
                        {{ $equipment['actual_driver_withdrawal'] == $driver_id ? dateInternationalToDateBrazil($equipment['actual_withdrawal_date'], DATETIME_BRAZIL_NO_SECONDS) : '' }}
                    </td>
            @endforeach
            </tbody>
        </table>

        <table class='table footer'>
            <tr>
                <td class='text-right'>
                    <strong>Total de resultados:</strong> {{ count($data) }} {{ count($data) === 1 ? 'registro' : 'registros' }}
                </td>
            </tr>
            <tr>
                <td class='text-right'>
                    <strong>Totais dos lançamentos:</strong>
                    {{
                        formatMoney($total_commission_value, 2, 'R$ ')
                    }}
                </td>
            </tr>
        </table>

    </body>
</html>
