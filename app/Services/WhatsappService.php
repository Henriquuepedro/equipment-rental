<?php

namespace App\Services;

use App\Models\Integration;
use App\Models\IntegrationToStore;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class WhatsappService
{
    private IntegrationToStore $integration_to_store;
    private Integration $integration;
    private Client $client;
    private string $whatsapp_session_id;

    public function __construct()
    {
        $this->integration_to_store = new IntegrationToStore();
        $this->integration = new Integration();
        $this->client = new Client(array(
            'base_uri' => env('WHATSAPP_BASE_URL'),
            'headers' => array(
                'x-api-key' => env('WHATSAPP_API_KEY')
            )
        ));
    }

    /**
     * @param int $company_id
     * @throws Exception
     */
    public function checkConnection(int $company_id)
    {
        $this->whatsapp_session_id = env('WHATSAPP_PREFIX_KEY') . $company_id;

        try {
            $request = $this->client->get("session/status/$this->whatsapp_session_id");
        } catch (GuzzleException $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode());
        }
        $content_get_status = json_decode($request->getBody()->getContents());

        if (!$content_get_status->success) {
            // Gerar uma nova sessão
            if ($content_get_status->message == 'session_not_found') {
                try {
                    $request = $this->client->get("session/start/$this->whatsapp_session_id");
                } catch (GuzzleException $exception) {
                    throw new Exception($exception->getMessage(), $exception->getCode());
                }
                $content_get_start = json_decode($request->getBody()->getContents());

                if (!$content_get_start->success) {
                    throw new Exception($content_get_start->message, 400);
                }
            }

            throw new Exception($content_get_status->message, 400);
        }
    }

    /**
     * @param int $company_id
     * @throws Exception
     */
    public function terminateConnection(int $company_id)
    {
        $this->whatsapp_session_id = env('WHATSAPP_PREFIX_KEY') . $company_id;

        try {
            $request = $this->client->get("session/terminate/$this->whatsapp_session_id");
        } catch (GuzzleException $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode());
        }
        $content_get_status = json_decode($request->getBody()->getContents());

        if (!$content_get_status->success) {
            throw new Exception($content_get_status->message, 400);
        }

        $whatsapp_integration = $this->integration->getByName('whatsapp');
        $this->integration_to_store->edit(array('active' => false), $whatsapp_integration->id, $company_id);
    }

    /**
     * @param int $company_id
     * @return string
     * @throws Exception
     */
    public function createIntegration(int $company_id): string
    {
        $this->whatsapp_session_id = env('WHATSAPP_PREFIX_KEY') . $company_id;

        try {
            $request = $this->client->get("session/status/$this->whatsapp_session_id");
        } catch (GuzzleException $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode());
        }
        $content_get_status = json_decode($request->getBody()->getContents());

        // Gerar uma nova sessão
        if (!$content_get_status->success && $content_get_status->message == 'session_not_found') {
            try {
                $request = $this->client->get("session/start/$this->whatsapp_session_id");
            } catch (GuzzleException $exception) {
                throw new Exception($exception->getMessage(), $exception->getCode());
            }
            $content_get_start = json_decode($request->getBody()->getContents());

            if (!$content_get_start->success) {
                throw new Exception($content_get_start->message, 400);
            }
        }

        try {
            $request = $this->client->get("session/qr/$this->whatsapp_session_id");
        } catch (GuzzleException $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode());
        }

        $content_get_qr = json_decode($request->getBody()->getContents());

        if (!$content_get_qr->success) {
            if ($content_get_qr->message == 'qr code not ready or already scanned') {
                throw new Exception("Seu QR Code está sendo gerado, aguarde mais alguns segundos e clique em Iniciar Integração novamente.", 400);
            }
            throw new Exception($content_get_qr->message, 400);
        }

        $whatsapp_integration = $this->integration->getByName('whatsapp');

        if ($this->integration_to_store->getByCompanyAndIntegration($company_id, $whatsapp_integration->id)) {
            $this->integration_to_store->edit(array('active' => true), $whatsapp_integration->id, $company_id);
        } else {
            $this->integration_to_store->insert(array(
                'company_id' => $company_id,
                'integration_id' => $this->whatsapp_session_id,
                'active' => true
            ));
        }

        return $content_get_qr->qr;
    }

    /**
     * @param int $company_id
     * @param string $phone
     * @return string
     * @throws Exception
     */
    public function getRealNumber(int $company_id, string $phone): string
    {
        $this->whatsapp_session_id = env('WHATSAPP_PREFIX_KEY') . $company_id;

        try {
            $number = "55$phone";
            $options = array(
                'json' => array(
                    "number" => $number
                )
            );
            $request = $this->client->post("client/getNumberId/$this->whatsapp_session_id", $options);
        } catch (GuzzleException $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode());
        }
        $content_get_number = json_decode($request->getBody()->getContents());

        if (!$content_get_number->success) {
            throw new Exception($content_get_number->message, 400);
        }

        if (empty($content_get_number->result)) {
            throw new Exception("Número informado ($number), não encontrado no WhatsApp.", 400);
        }

        return $content_get_number->result->_serialized;
    }

    /**
     * @param int $company_id
     * @param string $phone
     * @param string $message
     * @param string $content_type
     * @param string|null $type_media
     * @param string|null $filename
     * @throws Exception
     */
    public function sendMessage(int $company_id, string $phone, string $message, string $content_type, string $type_media = null, string $filename = null)
    {
        $this->whatsapp_session_id = env('WHATSAPP_PREFIX_KEY') . $company_id;

        try {
            $json = match ($content_type) {
                'string' => array(
                    "chatId" => $phone,
                    "contentType" => $content_type,
                    "content" => $message
                ),
                'MessageMedia' => array(
                    "chatId" => $phone,
                    "contentType" => $content_type,
                    "content" => array(
                        "mimetype" => $type_media,
                        "data" => $message,
                        "filename" => $filename
                    )
                ),
                default => throw new Exception("Tipo de arquivo $content_type não encontrado.", 400),
            };


            $options = array(
                'json' => $json
            );
            $request = $this->client->post("client/sendMessage/$this->whatsapp_session_id", $options);
        } catch (GuzzleException $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode());
        }
        $content_send_message = json_decode($request->getBody()->getContents());

        if (!$content_send_message->success) {
            throw new Exception($content_send_message->message, 400);
        }
    }
}
