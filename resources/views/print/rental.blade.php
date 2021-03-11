<html>
    <head>
        <title>Recibo Locação {{ $rental->code }}</title>
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
            .table .info span{
                font-size: 9px;
                font-weight: bold;
                padding-left: 3px
            }
            .table .info p{
                font-size: 11px
            }
            .table .info p{
                text-align: right;
                padding-right: 3px
            }
            .table .info p.dadosLocacao{
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
                    <img src=' {{ $company->logo ? "../../assets/images/company/{$company->id}/{$company->logo}" : "../../assets/images/company/company.png" }}'/>
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
                    <h5 class='title'>RECIBO DE LOCAÇÃO</h5>
                </td>
            </tr>
            <tr>
                <td class='info'>
                    <span>Número Locação</span>
                    <p>{{ str_pad($rental->code, 5, 0, STR_PAD_LEFT) }}</p>
                </td>
            </tr>
            <tr>
                <td class='info'>
                    <span>Data de Emissão</span>
                    <p>{{ date('d/m/Y H:i', strtotime($rental->created_at)) }}</p>
                </td>
            </tr>
            <tr>
                <td class='info'>
                    <span>Valor Total</span>
                    <p>R$ {{ number_format($rental->net_value, 2, ',', '.') }}</p>
                </td>
            </tr>
        </table>
        <table class='table'>
            <tr>
                <td class='info title text-left' colspan='3' style='width: 100%'>
                    <h5 class='title'>DADOS DO CLIENTE</h5>
                </td>
            </tr>
            <tr>
                <td class='info' colspan='2' style='width: 70%'>
                    <span>Cliente</span>
                    <p class='dadosLocacao'>{{ $client->name }} @if($client->cpf_cnpj)( {{ $client->cpf_cnpj }} )@endif</p>
                </td>
                <td class='info' style='width: 30%'>
                    <span>Telefone</span>
                    <p class='dadosLocacao'>{!! $client->phone_1 ?? '&nbsp;' !!}</p>
                </td>
            </tr>
            <tr>
                <td class='info' colspan='2' style='width: 70%'>
                    <span>Endereço</span>
                    <p class='dadosLocacao'>{{ $rental->address_name }}, {{ $rental->address_number }}</p>
                </td>
                <td class='info' style='width: 30%'>
                    <span>CEP</span>
                    <p class='dadosLocacao'>{!! $rental->address_zipcode ?? '&nbsp;' !!}</p>
                </td>
            </tr>
            <tr>
                <td class='info' style='width: 50%'>
                    <span>Complemento</span>
                    <p class='dadosLocacao'>{!! $rental->address_complement ?? '&nbsp;' !!}</p>
                </td>
                <td class='info' style='width: 25%'>
                    <span>Bairro</span>
                    <p class='dadosLocacao'>{{ $rental->address_neigh }}</p>
                </td>
                <td class='info' style='width: 25%'>
                    <span>Município/UF</span>
                    <p class='dadosLocacao'>{{ $rental->address_city }}/{{ $rental->address_state }}</p>
                </td>
            </tr>
        </table>
        <table class='table'>
            <tr>
                <td class='info title text-left' style='width: 100%' colspan='6'>
                    <h5 class='title'>DADOS DO EQUIPAMENTO</h5>
                </td>
            </tr>
            @foreach($equipments as $equipment)
                <tr>
                    <td class='info' style='width: 35%'>
                        <span>Equipamento</span>
                        <p class='dadosLocacao'>{{ $equipment->name ?? 'Caçamba '.$equipment->volume.'m³' }} - {{ $equipment->reference }}</p>
                    </td>
                    <td class='info' style='width: 11%'>
                        <span>Valor Total</span>
                        <p class='dadosLocacao'>R$ {{ number_format($equipment->total_value, 2, ',', '.') }}</p>
                    </td>
                    <td class='info' style='width: 10%'>
                        <span>Quantidade</span>
                        <p class='dadosLocacao'>{{ $equipment->quantity }}</p>
                    </td>
                    <td class='info' style='width: 17%'>
                        <span>Data Entrega</span>
                        <p class='dadosLocacao'>{{ date('d/m/Y H:i', strtotime($equipment->expected_delivery_date)) }}</p>
                    </td>
                    <td class='info' style='width: 17%'>
                        <span>Data Retirada</span>
                        <p class='dadosLocacao'>{!! $equipment->expected_withdrawal_date ? date('d/m/Y H:i', strtotime($equipment->expected_withdrawal_date)) : '&nbsp;' !!}</p>
                    </td>
                    <td class='info' style='width: 10%'>
                        <span>Situação</span>
                        <p class='dadosLocacao'>Criado</p>
                    </td>
                </tr>
            @endforeach
        </table>
        <table class='table'>
            <tr>
                <td class='info title text-left' colspan='4' style='width: 100%'>
                    <h5 class='title'>DADOS DA ENTREGA</h5>
                </td>
            </tr>
            <tr>
                <td class='info' colspan='3' style='width: 75%'>
                    <span>Endereço</span>
                    <p class='dadosLocacao'>{{ $rental->address_name }}, {{ $rental->address_number }}</p>
                </td>
                <td class='info' style='width: 25%'>
                    <span>CEP</span>
                    <p class='dadosLocacao'>{!! $rental->address_zipcode ?? '&nbsp;' !!}</p>
                </td>
            </tr>
            <tr>
                <td class='info' style='width: 50%' colspan='2'>
                    <span>Complemento</span>
                    <p class='dadosLocacao'>{!! $rental->address_complement ?? '&nbsp;' !!}</p>
                </td>
                <td class='info' style='width: 25%'>
                    <span>Bairro</span>
                    <p class='dadosLocacao'>{{ $rental->address_neigh }}</p>
                </td>
                <td class='info' style='width: 25%'>
                    <span>Município/UF</span>
                    <p class='dadosLocacao'>{{ $rental->address_city }}/{{ $rental->address_state }}</p>
                </td>
            </tr>
        </table>
        @if (count($payments))
            <table class='table'>
                <tr>
                    <td class='info title text-left' colspan='4' style='width: 100%'>
                        <h5 class='title'>DADOS DE PAGAMENTO</h5>
                    </td>
                </tr>
                @foreach($payments as $payment)
                    <tr>
                        <td class='info' style='width: 25%'>
                            <strong>{{ $payment->parcel }}º Parcela</strong>
                        </td>
                        <td class='info' style='width: 25%'>
                            <span>Vencimento</span>
                            <p class='dadosLocacao'>{{ date('d/m/Y', strtotime($payment->due_date)) }}</p>
                        </td>
                        <td class='info' style='width: 25%'>
                            <span>Valor</span>
                            <p class='dadosLocacao'>R$ {{ number_format($payment->due_value, 2, ',', '.') }}</p>
                        </td>
                        <td class='info' style='width: 25%'>
                            <span>Situação</span>
                            <p class='dadosLocacao'>{{ $payment->payday ? 'Pago' : 'Em aberto' }}</p>
                        </td>
                    </tr>
                @endforeach
                <tr>
                    <td class='info' style='width: 25%'>
                        <span>Total Bruto</span>
                        <p class='dadosLocacao'>R$ {{ number_format($rental->gross_value, 2, ',', '.') }}</p>
                    </td>
                    <td class='info' style='width: 25%'>
                        <span>Desconto</span>
                        <p class='dadosLocacao'>R$ {{ number_format($rental->discount_value, 2, ',', '.') }}</p>
                    </td>
                    <td class='info' style='width: 25%'>
                        <span>Acréscimo</span>
                        <p class='dadosLocacao'>R$ {{ number_format($rental->extra_value, 2, ',', '.') }}</p>
                    </td>
                    <td class='info' style='width: 25%'>
                        <span>Total Líquido</span>
                        <p class='dadosLocacao'>R$ {{ number_format($rental->net_value, 2, ',', '.') }}</p>
                    </td>
                </tr>
            </table>
        @endif
        <table class='table'>
            <tr>
                <td class='info title text-left' style='width: 100%'>
                    <h5 class='title'>OBSERVAÇÃO</h5>
                </td>
            </tr>
            <tr>
                <td class='info obs text-left' style='width: 100%'>
                    {!! $rental->observation !!}
                </td>
            </tr>
        </table>
        <table class='table'>
            <tr>
                <td style='width:50%' class='text-center border-1'>
                    <div>{{ date('d/m/Y') }}</div>
                    <br><br><br><br>
                    <div>_______________________________________</div>
                    <br>
                    <div class='nomeAssinatura'>{{ mb_strimwidth($company->name, 0, 50) }}</div>
                </td>
                <td style='width:50%' class='text-center border-1'>
                    <div>{{ date('d/m/Y') }}</div>
                    <br><br><br><br>
                    <div>_______________________________________</div>
                    <br>
                    <div class='nomeAssinatura'>{{ $client->name }}</div>
                </td>
            </tr>
        </table>
    </body>
</html>
