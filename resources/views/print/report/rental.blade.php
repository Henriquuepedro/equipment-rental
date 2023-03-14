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
                    @foreach($data_filter_view_pdf as $filter_key => $filter_value)
                        <p style="margin: 0"><strong>{{ $filter_key }}:</strong> {{ $filter_value }}</p>
                    @endforeach
                </td>
            </tr>
        </table>

        @if ($type_report === 'analytic')
            @include('print.report.rental.analytic')
        @elseif ($type_report === 'synthetic')
            @include('print.report.rental.synthetic')

        @endif

    </body>
</html>
