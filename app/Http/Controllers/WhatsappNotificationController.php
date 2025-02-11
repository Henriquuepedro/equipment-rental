<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Integration;
use App\Models\IntegrationToStore;
use App\Models\Rental;
use App\Services\PrintService;
use App\Services\WhatsappService;
use Barryvdh\DomPDF\PDF;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class WhatsappNotificationController extends Controller
{
    private IntegrationToStore $integration_to_store;
    private Integration $integration;
    private Rental $rental;
    private Client $client;
    private WhatsappService $whatsapp_service;
    private PrintService $print_service;

    public function __construct(PDF $pdf)
    {
        $this->integration_to_store = new IntegrationToStore();
        $this->integration = new Integration();
        $this->rental = new Rental();
        $this->client = new Client();
        $this->whatsapp_service = new WhatsappService();
        $this->print_service = new PrintService($pdf);
    }

    public function rental(int $rental_id = null): JsonResponse
    {
        try {
            $company_id = Auth::user()->__get('company_id');

            $whatsapp_integration = $this->integration->getByName('whatsapp');

            $store_integration = $this->integration_to_store->getByCompanyAndIntegration($company_id, $whatsapp_integration->id);

            $rental = $this->rental->getRental($company_id, $rental_id);
            if (!$rental) {
                throw new Exception("Locação $rental_id não encontrada");
            }

            if (!$store_integration || !$store_integration->active) {
                throw new Exception("Integração com WhatsApp não configurada ou indisponível");
            }

            // Ler cliente da locação
            $client = $this->client->getClient($rental->client_id, $company_id);

            // Recuperar número
            $receiver_whatsapp_phone_1 = $client->receiver_whatsapp_phone_1;
            $receiver_whatsapp_phone_2 = $client->receiver_whatsapp_phone_2;

            $phone = $receiver_whatsapp_phone_1 ? $client->phone_1 : ($receiver_whatsapp_phone_2 ? $client->phone_2 : null);
            if (empty($phone)) {
                throw new Exception("Não foi encontrado um número de recebedor de mensagem do WhatsApp");
            }

            if (strlen($phone) !== 10 && strlen($phone) !== 11) {
                throw new Exception("Número de telefone do cliente deve estar preenchido com o DD e 8 ou 9 dígitos");
            }

            $real_phone = $this->whatsapp_service->getRealNumber($company_id, $phone);

            // Montar conteúdo para envio
            $content = "Olá, $client->name. Seu pedido com o código $rental->code foi gerado com sucesso!";
            $this->whatsapp_service->sendMessage($company_id, $real_phone, $content, 'string');

            // Enviar o recibo da locação
            $content = $this->print_service->rental($rental_id, true);
            $this->whatsapp_service->sendMessage($company_id, $real_phone, $content, 'MessageMedia', 'application/pdf', "Locação_$rental->code.pdf");

        } catch (Exception $exception) {
            return response()->json(array('success' => false, 'message' => $exception->getMessage()), 400);
        }

        return response()->json(array('success' => true, 'message' => 'Mensagem enviada com sucesso!'));
    }
}
