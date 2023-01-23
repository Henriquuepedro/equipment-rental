<?php

use App\Models\Permission;

if (! function_exists('hasPermission')) {
    function hasPermission(string $permission): bool
    {
        if (hasAdmin()) {
            return true;
        }

        $permissions = empty(auth()->user()->permission) ? [] : json_decode(auth()->user()->permission);
        $permission = Permission::query()->where('name', $permission)->first();
        $permission = $permission ? $permission->id : 0;

        return in_array($permission, $permissions);
    }
}

if (! function_exists('hasAdmin')) {
    function hasAdmin(): bool
    {
        // 0 = user
        // 1 = admin
        // 2 = master
        return auth()->user()->type_user === 1 || hasAdminMaster();
    }
}

if (! function_exists('hasAdminMaster')) {
    function hasAdminMaster(): bool
    {
        // 0 = user
        // 1 = admin
        // 2 = master
        return auth()->user()->type_user === 2;
    }
}

if (! function_exists('isAjax')) {
    function isAjax(): bool
    {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
    }
}

if (! function_exists('transformMoneyBr_En')) {
    function transformMoneyBr_En(string $value = null): float
    {
        if (empty($value)) {
            return 0.00;
        }

        $value = str_replace('.', '', $value);
        $value = str_replace(',', '.', $value);
        $value = filter_var($value, FILTER_VALIDATE_FLOAT);

        return (float)$value;
    }
}

if (! function_exists('mask')) {
    function mask(string $val, string $mask): string
    {
        $maskared = '';
        $k = 0;
        for ($i = 0; $i <= strlen($mask) - 1; $i++) {
            if ($mask[$i] == '#') {
                if (isset($val[$k])) $maskared .= $val[$k++];
            } else {
                if (isset($mask[$i])) $maskared .= $mask[$i];
            }
        }
        return $maskared;
    }
}

if (! function_exists('likeText')) {
    /**
     * Consulta uma palavra dentro de um texto.
     *
     * @param   string  $needle     Valor a ser procurado
     * @param   string  $haystack   Valor real para comparação
     * @return  bool                Retorna o status da consulta
     */
    function likeText(string $needle, string $haystack): bool
    {
        $regex = '/' . str_replace('%', '.*?', $needle) . '/';

        return preg_match($regex, $haystack) > 0;
    }
}

if (! function_exists('formatCPF_CNPJ')) {
    /**
     * @param   string|null $value          CPF ou CNPJ
     * @param   string      $defaultEmpty   Valor padrão de retorno, caso cheguei em branco ou nulo
     * @return  string                      Retorno da formatação
     */
    function formatCPF_CNPJ(string $value = null, string $defaultEmpty = "Não Informado"): string
    {
        $format = '';

        if ($value === '' || $value === null) {
            return $defaultEmpty;
        }
        elseif (strlen($value) != 11 && strlen($value) != 14 && strlen($value) != 0) {
            return $value;
        }
        elseif (strlen($value) == 11) {
            $format = mask($value, '###.###.###-##');
        }
        elseif (strlen($value) == 14) {
            $format = mask($value, '##.###.###/####-##');
        }

        return $format;
    }
}

if (! function_exists('formatPhone')) {
    /**
     * @param   string|null $value          Número de telefone
     * @param   string      $defaultEmpty   Valor padrão de retorno, caso cheguei em branco ou nulo
     * @return  string                      Retorno da formatação
     */
    function formatPhone(string $value = null, string $defaultEmpty = "Não Informado"): string
    {
        // Número chegou em branco.
        if ($value === '' || $value === null) {
            return $defaultEmpty;
        }
        // Número não padrão telefónico.
        if (strlen($value) !== 10 && strlen($value) !== 11) {
            return $value;
        }

        $mask = '';
        // Telefone fixo.
        if (strlen($value) === 10) {
            $mask = '(##) ####-####';
        }
        // Telefone celular.
        elseif (strlen($value) === 11) {
            $mask = '(##) #####-####';
        }

        return mask($value, $mask);
    }
}

if (! function_exists('formatZipcode')) {
    /**
     * @param   string|null $value          CEP
     * @param   string      $defaultEmpty   Valor padrão de retorno, caso cheguei em branco ou nulo
     * @return  string                      Retorno da formatação
     */
    function formatZipcode(string $value = null, string $defaultEmpty = "Não Informado"): string
    {
        // Número chegou em branco.
        if ($value === '' || $value === null) {
            return $defaultEmpty;
        }
        // Número não padrão de CEP.
        if (strlen($value) !== 8 ) {
            return $value;
        }

        return mask($value, '##.###-###');
    }
}

if (! function_exists('onlyNumbers')) {
    /**
     * Limpa o texto e mantém somente números.
     *
     * @param   string|null $text
     * @return  string|null
     */
    function onlyNumbers(?string $text): ?string
    {
        if (is_null($text)) {
            return null;
        }

        return preg_replace('/\D/', '', $text);
    }
}
