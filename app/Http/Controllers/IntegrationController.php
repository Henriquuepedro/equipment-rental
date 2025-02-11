<?php

namespace App\Http\Controllers;

use App\Models\Integration;
use App\Models\IntegrationToStore;
use App\Services\WhatsappService;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class IntegrationController extends Controller
{
    private WhatsappService $whatsapp_service;

    public function __construct()
    {
        $this->whatsapp_service = new WhatsappService();
    }

    public function createIntegration(): JsonResponse
    {
        $company_id = Auth::user()->__get('company_id');

        try {
            $qr_code = $this->whatsapp_service->createIntegration($company_id);
            return response()->json(array('success' => true, 'message' => true, 'qr_code' => $qr_code));
        } catch (Exception $exception) {
            return response()->json(array('success' => false, 'message' => $exception->getMessage()));
        }
    }

    public function checkConnection(): JsonResponse
    {
        $company_id = Auth::user()->__get('company_id');

        try {
            $this->whatsapp_service->checkConnection($company_id);
            return response()->json(array('success' => true, 'message' => 'Sessão iniciada com sucesso!'));
        } catch (Exception $exception) {
            return response()->json(array('success' => false, 'message' => $exception->getMessage()));
        }
    }

    public function terminateConnection(): JsonResponse
    {
        $company_id = Auth::user()->__get('company_id');

        try {
            $this->whatsapp_service->checkConnection($company_id);
            return response()->json(array('success' => true, 'message' => 'Sessão encerrada com sucesso!'));
        } catch (Exception $exception) {
            return response()->json(array('success' => false, 'message' => $exception->getMessage()));
        }
    }
}
