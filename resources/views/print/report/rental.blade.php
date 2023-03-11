<html>
    <head>
        <title>Relatório de Locações</title>
        <style>
            body{
                font-family: 'Microsoft YaHei','Source Sans Pro', sans-serif;
                font-size:13px;

            }
            .table  {
                border-collapse:collapse;
                font-size: 11px;
                border: 0;
                width: 100%;
                margin: 0px;
                cellspacing: 0;
            }
            .table .info{
                padding: 0px !important;
                height: 30px;
            }
            .table .info span{
                font-size: 9px;
                font-weight: bold;
                padding-left: 3px
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

            .table.content {
                border-top: 1px solid #000;
            }

        </style>
    </head>
    <body>
        <table class='table header'>
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
                    @foreach($data_filter_view_pdf as $filter_key => $filter_value)
                        <p style="margin: 0"><strong>{{ $filter_key }}:</strong> {{ $filter_value }}</p>
                    @endforeach
                </td>
            </tr>
        </table>
        <table class='table content'>
            <thead>
                <th class='info title text-left'>
                    Locação
                </th>
                <th class='info title text-left'>
                    Cliente
                </th>
                <th class='info title text-left'>
                    Criado em
                </th>
                <th class='info title text-left'>
                    Valor
                </th>
            </thead>
            <tbody>
                @foreach($rentals as $rental)
                    <tr>
                        <td class='info' style='width: 10%'>
                            {{ formatCodeRental($rental->code) }}
                        </td>
                        <td class='info' style='width: 50%'>
                            {{ $rental->client_name }}
                        </td>
                        <td class='info' style='width: 20%'>
                            {{ formdatDateBrazil($rental->created_at, 'd/m/Y H:i') }}
                        </td>
                        <td class='info' style='width: 20%'>
                            {{ formatMoney($rental->net_value, 2, 'R$ ') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </body>
</html>
