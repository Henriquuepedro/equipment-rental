<?php

use App\Models\Permission;
use Illuminate\Support\Facades\File;
use Illuminate\Http\UploadedFile;

const DATETIME_INTERNATIONAL = 'Y-m-d H:i:s';
const DATE_INTERNATIONAL = 'Y-m-d';
const DATETIME_BRAZIL = 'd/m/Y H:i:s';
const DATETIME_BRAZIL_NO_SECONDS = 'd/m/Y H:i';
const DATETIME_INTERNATIONAL_NO_SECONDS = 'd/m/Y H:i';
const DATE_BRAZIL = 'd/m/Y';
const DATETIME_INTERNATIONAL_TIMEZONE = 'Y-m-d H:i:sP';
const TIMEZONE_DEFAULT = 'America/Fortaleza';

if (! function_exists('hasPermission')) {
    function hasPermission(string $permission): bool
    {
        if (hasAdmin()) {
            return true;
        }

        $permissions = empty(auth()->user()->__get('permission')) ? [] : json_decode(auth()->user()->__get('permission'));
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
        return auth()->user()->__get('type_user') === 1 || hasAdminMaster();
    }
}

if (! function_exists('hasAdminMaster')) {
    function hasAdminMaster(): bool
    {
        // 0 = user
        // 1 = admin
        // 2 = master
        return auth()->user()->__get('type_user') === 2;
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
     * @param   string      $needle     Valor a ser procurado
     * @param   string|null $haystack   Valor real para comparação
     * @return  bool                    Retorna o status da consulta
     */
    function likeText(string $needle, string $haystack = null): bool
    {
        if (is_null($haystack)) {
            return false;
        }
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
        // Número não padrão telefônico.
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

if (! function_exists('dateBrazilToDateInternational')) {
    /**
     * Retorna a data formatada.
     *
     * @param   string|null $date
     * @return  string|null
     */
    function dateBrazilToDateInternational(?string $date): ?string
    {
        if (is_null($date)) {
            return null;
        }

        if (strlen($date) !== 10 && strlen($date) !== 16 && strlen($date) !== 19 && strlen($date) !== 27) {
            return null;
        }

        // Data tem time. 2022-12-17T03:07:08.000000Z
        if (strlen($date) === 27) {
            $date = date(DATETIME_INTERNATIONAL, strtotime($date));
        }

        $format_in  = DATE_BRAZIL;
        $format_out = DATE_INTERNATIONAL;

        if (strlen($date) === 16) {
            $format_in  .= ' H:i';
            $format_out .= ' H:i';
        } elseif (strlen($date) === 19) {
            $format_in  .= ' H:i:s';
            $format_out .= ' H:i:s';
        }

        try {
            return DateTime::createFromFormat($format_in, $date)->format($format_out);
        } catch (Exception | Throwable $e) {
            return $date;
        }
    }
}

if (! function_exists('dateInternationalToDateBrazil')) {
    /**
     * Formata a data.
     *
     * @param   string|null $date
     * @return  string|null
     */
    function dateInternationalToDateBrazil(?string $date, string $format = null): ?string
    {
        if (is_null($date)) {
            return null;
        }

        if (strlen($date) !== 10 && strlen($date) !== 16 && strlen($date) !== 19 && strlen($date) !== 27) {
            return null;
        }

        // Data tem time. 2022-12-17T03:07:08.000000Z
        if (strlen($date) === 27) {
            $date = date(DATETIME_INTERNATIONAL, strtotime($date));
        }

        $format_in  = DATE_INTERNATIONAL;
        $format_out = DATE_BRAZIL;

        if (strlen($date) === 16) {
            $format_in  .= ' H:i';
            $format_out .= ' H:i';
        } elseif (strlen($date) === 19) {
            $format_in  .= ' H:i:s';
            $format_out .= ' H:i:s';
        }

        try {
            return DateTime::createFromFormat($format_in, $date)->format($format ?? $format_out);
        } catch (Exception | Throwable $e) {
            return $date;
        }
    }
}

if (! function_exists('formatMoney')) {
    function formatMoney(string $value = null, int $decimals = 2, string $prefix = ''): string
    {
        if (empty($value)) {
            return 0.00;
        }

        return $prefix . number_format($value, $decimals, ',', '.');
    }
}

if (! function_exists('formatCodeRental')) {
    function formatCodeRental(string $code): string
    {
        return str_pad($code, 5, 0, STR_PAD_LEFT);
    }
}

if (!function_exists('dateNowInternational')) {
    function dateNowInternational($timezone = null, string $format = null): string
    {
        if ($timezone) {
            $dateTimeNow = new DateTimeZone($timezone);
        } else {
            $dateTimeNow = new DateTimeZone(TIMEZONE_DEFAULT);
        }

        return (new DateTime())->setTimezone($dateTimeNow)->format($format ?? DATETIME_INTERNATIONAL);

    }
}

if (!function_exists('getImageCompanyBase64')) {
    function getImageCompanyBase64(object $company): string
    {
        if ($company->logo) {
            $image = "assets/images/company/$company->id/$company->logo";
        } else {
            $image = "assets/images/company/company.png";
        }

        $extension = File::extension($image);

        $img_to_base64 = base64_encode(File::get($image));
        return "data:image/$extension;base64, $img_to_base64";
    }
}

if (!function_exists('dropdownButtonsDataList')) {
    function dropdownButtonsDataList(string $data_buttons, int $index = 0, string $direction = 'left'): string
    {
        return "<div class='row'><div class='col-12'><div class='dropdown drop$direction'>
            <button class='btn btn-outline-primary icon-btn dropdown-toggle' type='button' id='dropActionsDataList-$index' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
              <i class='fa fa-cog'></i>
            </button>
            <div class='dropdown-menu' aria-labelledby='dropActionsRental-$index'>$data_buttons</div</div>
        </div>";
    }
}

if (!function_exists('uploadFile')) {
    /**
     * @param   string                                  $upload_path
     * @param array|UploadedFile|UploadedFile[]|null    $file
     * @param   string|null                             $name_file
     * @param   array                                   $ext_accept
     * @param   int|null                                $max_size
     * @return  bool|string
     * @throws  Exception
     */
    function uploadFile(string $upload_path, array|UploadedFile|null $file, ?string $name_file = null, array $ext_accept = [], ?int $max_size = null): bool|string
    {
        $extension      = $file->getClientOriginalExtension(); // Recupera extensão da imagem.
        $nameOriginal   = $file->getClientOriginalName(); // Recupera nome da imagem.
        $file_size      = $file->getSize() / 1000;

        if (!empty($max_size) && $file_size > $max_size) {
            throw new Exception("Tamanho superior ao permitido. Enviado {$file_size}KB, será aceito até {$max_size}KB.");
        }

        if (!empty($ext_accept)) {
            $ext_accept = array_map(function($ext){ return strtolower($ext); }, $ext_accept);
            if (!in_array(strtolower($extension), $ext_accept)) {
                throw new Exception("Extensão $extension não aceita. São aceitas somente: " . implode(', ', $ext_accept));
            }
        }

        if (!$name_file) {
            $imageName = base64_encode($nameOriginal); // Gera um novo nome para a imagem.
            $name_file = substr($imageName, 0, 15) . rand(0, 100) . ".$extension"; // Pega apenas o 15 primeiros e adiciona a extensão.
        }

        $uploaded = $file->move($upload_path, $name_file);

        if (!$uploaded) {
            throw new Exception("Não foi possível enviar o arquivo.");
        }

        return $name_file;
    }
}

if (!function_exists('getFormPermission')) {
    function getFormPermission($groupPermissions, $user_permission = null): string
    {
        $htmlPermissions = '';
        $group_name = [];

        foreach ($groupPermissions as $groupPermission) {
            $group_name[$groupPermission->group_text][] = $groupPermission;
        }

        foreach ($group_name as $group => $permissions) {

            $htmlPermissions .= '
            <div class="col-md-4 grid-margin stretch-card permissions">
                <div class="card">
                  <div class="card-body">
                    <h4 class="card-title text-uppercase">'.$group.'</h4>
                    <div class="template-demo table-responsive">
                      <table class="table mb-0">
                        <tbody>';

            $prefix_input = !is_null($user_permission) ? '' : 'newuser_';
            $class_input = !is_null($user_permission) ? 'update-permission' : '';

            foreach ($permissions as $permission) {
                $checked = is_array($user_permission) && in_array($permission->id, $user_permission) ? 'checked' : '';

                $htmlPermissions .= '
                          <tr>
                            <td class="pr-0 pl-0 pt-3 d-flex align-items-center">
                              <div class="switch">
                                <input type="checkbox" class="switch-input '.$class_input.'" name="permission[]" value="'.$permission->id.'" id="permission_'.$prefix_input.$permission->id.'" data-permission-id="'.$permission->id.'" data-auto-check="'.$permission->auto_check.'" '.$checked.'>
                                <label for="permission_'.$prefix_input.$permission->id.'" class="switch-label"></label>
                              </div>
                              '.$permission->text.'
                            </td>
                          </tr>';
            }

            $htmlPermissions .= '
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>';
        }

        return $htmlPermissions;
    }
}

if (!function_exists('getErrorDataTables')) {
    function getErrorDataTables(string $message, ?int $draw, $result = []): array
    {
        return array(
            "draw"              => $draw,
            "recordsTotal"      => 0,
            "recordsFiltered"   => 0,
            "data"              => $result,
            "message"           => $message
        );
    }
}

