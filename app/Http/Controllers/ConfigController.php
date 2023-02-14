<?php

namespace App\Http\Controllers;

use App\Models\Config;
use Illuminate\Http\Request;

class ConfigController extends Controller
{
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function updateConfig(Request $request)
    {
        if (!hasAdmin()) {
            return redirect()->route('dashboard');
        }

        $company_id = $request->user()->company_id;

        $dataConfigCompany   = $this->config->getConfigColumnAndValue($company_id);
        $configCompanyColumn = $dataConfigCompany['column'];
        $arrUpdate = [];

        foreach ($configCompanyColumn as $configIndex) {
            if (in_array($configIndex, ['id', 'company_id', 'user_update', 'created_at', 'updated_at'])) {
                continue;
            }

            $arrUpdate[$configIndex] = (bool)$request->input($configIndex);
        }

        $updateConfig = $this->config->edit($arrUpdate, $company_id);

        if ($updateConfig) {
            return redirect()->route('config.index')
                ->with('success', "Configurações de empresa atualizada com sucesso!");
        }

        return redirect()->to(route('config.index').'#config')
            ->withErrors(['Não foi possível atualizar as configurações de empresa, tente novamente!'])
            ->withInput();
    }
}
