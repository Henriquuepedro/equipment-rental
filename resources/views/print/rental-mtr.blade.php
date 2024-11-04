<html>
    <head>
        <title>Recibo {{ $budget ? 'Orçamento' : 'Locação' }} {{ $rental->code }}</title>
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
                border: 1px solid #000;
                padding: 0px !important;
                height: 30px;
            }
            .table .info:not(.obs) span{
                font-size: 9px;
                font-weight: bold;
                padding-left: 3px
            }
            .table .info:not(.obs) p{
                font-size: 11px
            }
            .table .info:not(.obs) p{
                text-align: right;
                padding-right: 3px
            }
            .table .info:not(.obs) p.dadosLocacao{
                text-align: left;
                padding-right: 0px
            }
            .table .info.title{
                background-color: #ddd;
                text-align: center;
                font-weight: bold
            }
            .table .info.title-tophead{
                background-color: #ddd;
                text-align: center
            }
            .table .info.title-tophead h5{
                padding: 10px 0px 25px 0px;
                margin: 0px;
                height: 0px;
                font-size: 15px;
            }
            .table p{
                margin: 0px;
                font-weight: normal
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
            .no-border{
                border:0px !important
            }
            .border-1{
                border: 1px solid #000
            }
            .table .info p.dadosLocacao{
                padding-left: 3px;
            }
            .table .info.obs{
                padding: 5px !important
            }
            .table .info strong{
                padding-left: 3px;
            }
            .table .header{
                text-align: center;
                padding: 0px 5px
            }
            .info.title h5.title{
                padding-left: 5px;
                font-size: 11px;
                margin: 0px
            }
            .table .header.img{
                border-top: 1px solid #000;
                border-left: 1px solid #000
            }
            .table .header.img img{
                max-width: 110px;
                max-height: 110px
            }
            .table .header.dadosEmpresa{
                border-top: 1px solid #000;
                vertical-align: top
            }
            .table .header.dadosEmpresa .nomeEmpresa{
                padding-bottom: 10px;
                text-transform: uppercase;
                font-weight: bold;
                padding-top: 25px
            }
            .table .nomeAssinatura{
                text-transform: uppercase
            }
        </style>
    </head>
    <body>
        <table class='table'>
            <tr>
                <td class='header img' rowspan='4' style='width: 15%'>
                    <img src='{{$company->logo}}'/>
                </td>
                <td class='header dadosEmpresa' rowspan='4' style='width: 55%'>
                    <p class='nomeEmpresa'>{{ mb_strimwidth($company->name, 0, 50) }}</p>
                    {{ $company->address }}, {{ $company->number }}<br>
                    {{ $company->cep }}<br>
                    @if($company->complement) {{ $company->complement }}<br> @endif
                    {{ $company->neigh }} - {{ $company->city }}/{{ $company->state }}<br><br>
                    <strong>{{ $company->cpf_cnpj }}</strong>
                </td>
                <td class='info title' style='width: 30%'>
                    <h5 class='title'>Manifesto de Transporte de Resíduos (MTR)</h5>
                </td>
            </tr>
            <tr>
                <td class='info'>
                    <span>Número MTR</span>
                    <p>{{ formatCodeIndex($rental_mtr->id) }}</p>
                </td>
            </tr>
            <tr>
                <td class='info'>
                    <span>Número {{ $budget ? 'Orçamento' : 'Locação' }}</span>
                    <p>{{ formatCodeIndex($rental->code) }}</p>
                </td>
            </tr>
            <tr>
                <td class='info'>
                    <span>Data de Emissão</span>
                    <p>{{ date(DATETIME_BRAZIL_NO_SECONDS, strtotime($rental->created_at)) }}</p>
                </td>
            </tr>
        </table>
        <table class='table'>
            <tr>
                <td class='info title text-left' colspan='3' style='width: 100%'>
                    <h5 class='title'>DADOS DO GERADOR</h5>
                </td>
            </tr>
            <tr>
                <td class='info' style='width: 70%'>
                    <span>Razão Social</span>
                    <p class='dadosLocacao'>{{ $company->name }}</p>
                </td>
                <td class='info' style='width: 70%'>
                    <span>CPF/CNPJ</span>
                    <p class='dadosLocacao'>{{ $company->cpf_cnpj }}</p>
                </td>
                <td class='info' style='width: 30%'>
                    <span>Telefone</span>
                    <p class='dadosLocacao'>{!! formatPhone($company->phone_1 ?? $company->phone_2, '&nbsp;') !!}</p>
                </td>
            </tr>
            <tr>
                <td class='info' colspan='2' style='width: 70%'>
                    <span>Endereço</span>
                    <p class='dadosLocacao'>{{ $company->address }}, {{ $company->number }}</p>
                </td>
                <td class='info' style='width: 30%'>
                    <span>CEP</span>
                    <p class='dadosLocacao'>{!! $company->cep ?? '&nbsp;' !!}</p>
                </td>
            </tr>
            <tr>
                <td class='info' style='width: 50%'>
                    <span>Complemento</span>
                    <p class='dadosLocacao'>{!! $company->complement ?? '&nbsp;' !!}</p>
                </td>
                <td class='info' style='width: 25%'>
                    <span>Bairro</span>
                    <p class='dadosLocacao'>{{ $company->neigh }}</p>
                </td>
                <td class='info' style='width: 25%'>
                    <span>Município/UF</span>
                    <p class='dadosLocacao'>{{ $company->city }}/{{ $company->state }}</p>
                </td>
            </tr>
        </table>
        <table class='table'>
            <tr>
                <td class='info title text-left' colspan='3' style='width: 100%'>
                    <h5 class='title'>DADOS DO TRANSPORTADOR</h5>
                </td>
            </tr>
            <tr>
                <td class='info' style='width: 70%'>
                    <span>Cliente</span>
                    <p class='dadosLocacao'>{{ $driver->name }}</p>
                </td>
                <td class='info' style='width: 70%'>
                    <span>CPF/CNPJ</span>
                    <p class='dadosLocacao'>{{ formatCPF_CNPJ($driver->cpf) }}</p>
                </td>
                <td class='info' style='width: 30%'>
                    <span>Telefone</span>
                    <p class='dadosLocacao'>{!! formatPhone($driver->phone, '&nbsp;') !!}</p>
                </td>
            </tr>
            <tr>
                <td class='info' colspan='2' style='width: 70%'>
                    <span>Endereço</span>
                    <p class='dadosLocacao'>{{ $driver->address_name }}, {{ $driver->address_number }}</p>
                </td>
                <td class='info' style='width: 30%'>
                    <span>CEP</span>
                    <p class='dadosLocacao'>{!! formatZipcode($driver->address_zipcode, '&nbsp;') !!}</p>
                </td>
            </tr>
            <tr>
                <td class='info' style='width: 50%'>
                    <span>Complemento</span>
                    <p class='dadosLocacao'>{!! $driver->address_complement ?? '&nbsp;' !!}</p>
                </td>
                <td class='info' style='width: 25%'>
                    <span>Bairro</span>
                    <p class='dadosLocacao'>{{ $driver->address_neigh }}</p>
                </td>
                <td class='info' style='width: 25%'>
                    <span>Município/UF</span>
                    <p class='dadosLocacao'>{{ $driver->address_city }}/{{ $driver->address_state }}</p>
                </td>
            </tr>
        </table>
        <table class='table'>
            <tr>
                <td class='info title text-left' colspan='3' style='width: 100%'>
                    <h5 class='title'>DADOS DO DESTINATÁRIO</h5>
                </td>
            </tr>
            <tr>
                <td class='info' style='width: 70%'>
                    <span>Cliente</span>
                    <p class='dadosLocacao'>{{ $disposal_place->name }}</p>
                </td>
                <td class='info' style='width: 70%'>
                    <span>CPF/CNPJ</span>
                    <p class='dadosLocacao'>{{ formatCPF_CNPJ($disposal_place->cpf_cnpj) }}</p>
                </td>
                <td class='info' style='width: 30%'>
                    <span>Telefone</span>
                    <p class='dadosLocacao'>{!! formatPhone($disposal_place->phone_1 ?? $disposal_place->phone_2, '&nbsp;') !!}</p>
                </td>
            </tr>
            <tr>
                <td class='info' colspan='2' style='width: 70%'>
                    <span>Endereço</span>
                    <p class='dadosLocacao'>{{ $disposal_place->address_name }}, {{ $disposal_place->address_number }}</p>
                </td>
                <td class='info' style='width: 30%'>
                    <span>CEP</span>
                    <p class='dadosLocacao'>{!! formatZipcode($disposal_place->address_zipcode, '&nbsp;') !!}</p>
                </td>
            </tr>
            <tr>
                <td class='info' style='width: 50%'>
                    <span>Complemento</span>
                    <p class='dadosLocacao'>{!! $disposal_place->address_complement ?? '&nbsp;' !!}</p>
                </td>
                <td class='info' style='width: 25%'>
                    <span>Bairro</span>
                    <p class='dadosLocacao'>{{ $disposal_place->address_neigh }}</p>
                </td>
                <td class='info' style='width: 25%'>
                    <span>Município/UF</span>
                    <p class='dadosLocacao'>{{ $disposal_place->address_city }}/{{ $disposal_place->address_state }}</p>
                </td>
            </tr>
        </table>
        <table class='table'>
            <tr>
                <td class='info title text-left' style='width: 100%' colspan='5'>
                    <h5 class='title'>INFORMAÇÕES SOBRE OS RESÍDUOS</h5>
                </td>
            </tr>
            @foreach($equipments as $equipment)
                @php
                    $equipment_rental_mtr = getArrayByValueIn($equipments_rental_mtr, $equipment['id'], 'rental_equipment_id');
                @endphp
                <tr>
                    <td class='info' style='width: 35%'>
                        <span>Equipamento</span>
                        <p class='dadosLocacao'>{{ $equipment->name ?? 'Caçamba '.$equipment->volume.'m³' }} - {{ $equipment->reference }}</p>
                    </td>
                    <td class='info' style='width: 15%'>
                        <span>Resíduo</span>
                        <p class='dadosLocacao'>{{ $equipment_rental_mtr['residue']['name'] }}</p>
                    </td>
                    <td class='info' style='width: 15%'>
                        <span>Quantidade</span>
                        <p class='dadosLocacao'>{{ $equipment_rental_mtr['quantity'] }}</p>
                    </td>
                    <td class='info' style='width: 15%'>
                        <span>Classificação</span>
                        <p class='dadosLocacao'>{!! $equipment_rental_mtr['classification'] ?? '&nbsp;' !!}</p>
                    </td>
                    <td class='info' style='width: 20%'>
                        <span>Data da coleta</span>
                        <p class='dadosLocacao'>{{ $equipment_rental_mtr['date'] }}</p>
                    </td>
                </tr>
            @endforeach
        </table>
        <table class='table'>
            <tr>
                <td class='info title text-left' style='width: 100%'>
                    <h5 class='title'>OBSERVAÇÃO</h5>
                </td>
            </tr>
            <tr>
                <td class='info obs text-left' style='width: 100%'>
                    Declaro que as informações acima são verdadeiras e que os resíduos serão transportados e descartados de acordo com a legislação ambiental vigente.
                </td>
            </tr>
        </table>
        <table class='table'>
            <tr>
                <td style='width:50%' class='text-center border-1'>
                    <div>{{ date(DATE_BRAZIL) }}</div>
                    <br><br><br><br>
                    <div>_______________________________________</div>
                    <br>
                    <div class='nomeAssinatura'>{{ mb_strimwidth($company->name, 0, 50) }}</div>
                </td>
                <td style='width:50%' class='text-center border-1'>
                    <div>{{ date(DATE_BRAZIL) }}</div>
                    <br><br><br><br>
                    <div>_______________________________________</div>
                    <br>
                    <div class='nomeAssinatura'>{{ $driver->name }}</div>
                </td>
            </tr>
        </table>
        <table class='table'>
            <tr>
                <td style='width:100%' class='text-center border-1'>
                    <div>{{ date(DATE_BRAZIL) }}</div>
                    <br><br><br><br>
                    <div>______________________________________________________________________________</div>
                    <br>
                    <div class='nomeAssinatura'>{{ $disposal_place->name }}</div>
                </td>
            </tr>
        </table>
    </body>
</html>
