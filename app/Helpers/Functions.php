<?php

use App\Models\LogEvent;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

const DATETIME_INTERNATIONAL = 'Y-m-d H:i:s';
const DATE_INTERNATIONAL = 'Y-m-d';
const DATETIME_BRAZIL = 'd/m/Y H:i:s';
const DATETIME_BRAZIL_NO_SECONDS = 'd/m/Y H:i';
const DATETIME_INTERNATIONAL_NO_SECONDS = 'Y-m-d H:i';
const DATE_BRAZIL = 'd/m/Y';
const DATETIME_INTERNATIONAL_TIMEZONE = 'Y-m-d H:i:sP';
const DATETIME_INTERNATIONAL_MICROSECOND = 'Y-m-d H:i:s.u';
const TIMEZONE_DEFAULT = 'America/Fortaleza';
const HALF_ALLOWABLE_TAGS = "<p><br><h1><h2><h3><h4><h5><h6><strong><b><em><i><u><small><ul><ol><li><div><span><a>";
const FULL_ALLOWABLE_TAGS = "<p><br><h1><h2><h3><h4><h5><h6><strong><b><em><i><u><s><small><ul><ol><li><div><span><a><img><iframe>";
const MONTH_NAME_PT = [
    '01'    => 'Janeiro',
    '02'    => 'Fevereiro',
    '03'    => 'Março',
    '04'    => 'Abril',
    '05'    => 'Maio',
    '06'    => 'Junho',
    '07'    => 'Julho',
    '08'    => 'Agosto',
    '09'    => 'Setembro',
    '1'     => 'Janeiro',
    '2'     => 'Fevereiro',
    '3'     => 'Março',
    '4'     => 'Abril',
    '5'     => 'Maio',
    '6'     => 'Junho',
    '7'     => 'Julho',
    '8'     => 'Agosto',
    '9'     => 'Setembro',
    '10'    => 'Outubro',
    '11'    => 'Novembro',
    '12'    => 'Dezembro'
];
const SHORT_MONTH_NAME_PT = [
    '01'    => 'Jan',
    '02'    => 'Fev',
    '03'    => 'Mar',
    '04'    => 'Abr',
    '05'    => 'Mai',
    '06'    => 'Jun',
    '07'    => 'Jul',
    '08'    => 'Ago',
    '09'    => 'Set',
    '1'     => 'Jan',
    '2'     => 'Fev',
    '3'     => 'Mar',
    '4'     => 'Abr',
    '5'     => 'Mai',
    '6'     => 'Jun',
    '7'     => 'Jul',
    '8'     => 'Ago',
    '9'     => 'Set',
    '10'    => 'Out',
    '11'    => 'Nov',
    '12'    => 'Dez'
];

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
        return auth()->user()->__get('type_user') === User::$TYPE_USER['admin'] || hasAdminMaster();
    }
}

if (! function_exists('hasAdminMaster')) {
    function hasAdminMaster(): bool
    {
        return auth()->user()->__get('type_user') === User::$TYPE_USER['master'];
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

        return roundDecimal($value);
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
            return number_format(0, 2, ',', '.');
        }

        return $prefix . number_format($value, $decimals, ',', '.');
    }
}

if (! function_exists('formatCodeIndex')) {
    function formatCodeIndex(string $code, int $size_min = 5): string
    {
        return str_pad($code, $size_min, 0, STR_PAD_LEFT);
    }
}

if (!function_exists('dateNowInternational')) {
    function dateNowInternational($timezone = null, string $format = DATETIME_INTERNATIONAL): string
    {
        if ($timezone) {
            $dateTimeNow = new DateTimeZone($timezone);
        } else {
            $dateTimeNow = new DateTimeZone(TIMEZONE_DEFAULT);
        }

        return (new DateTime())->setTimezone($dateTimeNow)->format($format);

    }
}

if (!function_exists('getImageCompanyBase64')) {
    function getImageCompanyBase64(object $company): string
    {
        if ($company->logo) {
            $image = "assets/images/company/$company->id/$company->logo";
        } else {
            if (auth()->user()) {
                $image = auth()->user()->__get('style_template') == 1 ? 'assets/images/system/logotipo-horizontal-black.png' : 'assets/images/system/logotipo-horizontal-white.png';
            } else {
                $image = "assets/images/system/company.png";
            }
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
            <button class='btn btn-outline-primary icon-btn dropdown-toggle' type='button' id='dropActionsDataList-$index' data-bs-toggle='dropdown' data-boundary='window' aria-haspopup='true' aria-expanded='false'>
              <i class='fa fa-cog'></i>
            </button>
            <div class='dropdown-menu' aria-labelledby='dropActionsRental-$index'>$data_buttons</div</div>
        </div>";
    }
}

if (!function_exists('newDropdownButtonsDataList')) {
    function newDropdownButtonsDataList(array $data_action, int $index = 0, string $direction = 'left'): string
    {
        $data_buttons = '';

        foreach ($data_action as $action) {
            if (isset($action['can']) && !$action['can']) {
                continue;
            }

            $tag        = $action['tag'] ?? '';
            $title      = $action['title'] ?? '';
            $icon       = $action['icon'] ?? '';
            $href       = empty($action['href']) ? '' : "href='$action[href]'";
            $class      = $action['class'] ?? '';
            $attribute  = $action['attribute'] ?? '';

            $data_buttons .= "<$tag $href class='dropdown-item $class' $attribute><i class='$icon'></i> $title</$tag>";
        }

        return "<div class='row'><div class='col-12'><div class='dropdown drop$direction'>
            <button class='btn btn-outline-primary icon-btn dropdown-toggle' type='button' id='dropActionsDataList-$index' data-bs-toggle='dropdown' data-boundary='window' aria-haspopup='true' aria-expanded='false'>
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

        checkPathExistToCreate($upload_path);

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
            <div class="col-md-4 grid-margin stretch-card permissions p-2">
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

if (!function_exists('sumDate')) {
    function sumDate(?string $date, int $year = null, int $month = null, int $day = null, int $hour = null, int $minute = null, int $second = null): ?string
    {
        if (is_null($date) || (strlen($date) !== 10 && strlen($date) !== 16 && strlen($date) !== 19 && strlen($date) !== 27)) {
            return null;
        }

        $data = DateTime::createFromFormat(DATETIME_INTERNATIONAL, $date);

        $format = DATE_INTERNATIONAL;

        if (strlen($date) === 16) {
            $format .= ' H:i';
        } elseif (strlen($date) === 19) {
            $format .= ' H:i:s';
        }

        if (!is_null($year)) {
            $data->add(new DateInterval("P{$year}Y"));
        }
        if (!is_null($month)) {
            $data->add(new DateInterval("P{$month}M"));
        }
        if (!is_null($day)) {
            $data->add(new DateInterval("P{$day}D"));
        }
        if (!is_null($hour)) {
            $data->add(new DateInterval("PT{$hour}H"));
        }
        if (!is_null($minute)) {
            $data->add(new DateInterval("PT{$minute}M"));
        }
        if (!is_null($second)) {
            $data->add(new DateInterval("PT{$second}S"));
        }

        return $data->format($format);
    }
}

if (!function_exists('subDate')) {
    function subDate(?string $date, int $year = null, int $month = null, int $day = null, int $hour = null, int $minute = null, int $second = null): ?string
    {
        if (is_null($date) || (strlen($date) !== 10 && strlen($date) !== 16 && strlen($date) !== 19 && strlen($date) !== 27)) {
            return null;
        }

        $format = DATE_INTERNATIONAL;
        $format_in = DATE_INTERNATIONAL;

        if (strlen($date) === 16) {
            $format .= ' H:i';
            $format_in .= ' H:i';
        } elseif (strlen($date) === 19) {
            $format .= ' H:i:s';
            $format_in .= ' H:i:s';
        }

        $data = DateTime::createFromFormat($format_in, $date);

        if (!is_null($year)) {
            $data->sub(new DateInterval("P{$year}Y"));
        }
        if (!is_null($month)) {
            $data->sub(new DateInterval("P{$month}M"));
        }
        if (!is_null($day)) {
            $data->sub(new DateInterval("P{$day}D"));
        }
        if (!is_null($hour)) {
            $data->sub(new DateInterval("PT{$hour}H"));
        }
        if (!is_null($minute)) {
            $data->sub(new DateInterval("PT{$minute}M"));
        }
        if (!is_null($second)) {
            $data->sub(new DateInterval("PT{$second}S"));
        }

        return $data->format($format);
    }
}

if (!function_exists('roundDecimal')) {
    function roundDecimal(string|float $value, int $decimal = 2): float
    {
        return (float)number_format($value, $decimal, '.', '');
    }
}

if (! function_exists('formatDateInternational')) {
    /**
     * Formata a data internacional.
     *
     * @param   string|null $date
     * @param   string $format
     * @param   string|null $timezone
     * @return  string|null
     */
    function formatDateInternational(?string $date, string $format = DATETIME_INTERNATIONAL, string $timezone = null): ?string
    {
        if (is_null($date)) {
            return null;
        }

        try {
            if ($timezone) {
                $dateTimeNow = new DateTimeZone($timezone);
            } else {
                $dateTimeNow = new DateTimeZone(TIMEZONE_DEFAULT);
            }

            return (new DateTime($date))->setTimezone($dateTimeNow)->format($format);
        } catch (Exception | Throwable $e) {
            return $date;
        }
    }
}

if (! function_exists('getKeyRandom')) {
    /**
     * Recuperar uma chave única.
     */
    function getKeyRandom(): ?string
    {
        return substr(bin2hex(random_bytes(6)), 1) . substr(md5(date("YmdHis")), 1, 14);
    }
}

if (! function_exists('getColorStatusMP')) {
    /**
     * Recuperar cor de acordo com o status.
     *
     * @param string $status
     * @return null|string
     */
    function getColorStatusMP(string $status): ?string
    {
        if (in_array($status, array('pending','inprocess','inmediation'))) {
            return 'warning';
        } elseif (in_array($status, array('rejected','cancelled','refunded','chargedback'))) {
            return 'danger';
        } elseif (in_array($status, array('approved', 'authorized'))) {
            return 'success';
        }

        return '';
    }
}

if (! function_exists('getNamePaymentTypeMP')) {
    /**
     * Recuperar o nome da forma de pagamento.
     *
     * @param object $payment
     * @return null|string
     */
    function getNamePaymentTypeMP(object $payment): ?string
    {
        $payment_type = __("mp.$payment->payment_type_id");
        $payment_type_complement = ucfirst(str_replace('mp.', '', __("mp.$payment->payment_method_id")));
        if (!empty($payment_type_complement)) {
            $payment_type .= " ($payment_type_complement)";
        }

        return $payment_type;
    }
}

if (! function_exists('checkPathExistToCreate')) {
    /**
     * Se o caminho não existir, será criado.
     *
     * @param string $path
     */
    function checkPathExistToCreate(string $path): void
    {
        $path_validate = public_path();
        foreach (explode('/', $path) as $p_) {
            $path_validate .= "/$p_";
            if (!is_dir($path_validate)) {
                File::makeDirectory($path_validate);
            }
        }
    }
}

if (! function_exists('createLogEvent')) {
    /**
     * Se o caminho não existir, será criado.
     *
     * @param string $event             Nome do evento.
     * @param object $auditable_model   Entidade de log.
     */
    function createLogEvent(string $event, object $auditable_model): void
    {
        try {
            $details = null;
            if ($event === 'updated') {
                $old_log = [];
                foreach ($auditable_model->getDirty() as $dity_key => $dirty_value) {
                    $old_log[$dity_key] = $auditable_model->getOriginal($dity_key);
                }

                $details = [
                    'old' => $old_log,
                    'new' => $auditable_model->getDirty()
                ];
            } elseif ($event === 'deleted') {
                $details = $auditable_model->toArray();
            }

            LogEvent::create([
                'event'             => $event,
                'user_id'           => is_null(auth()->user()) ? null : auth()->id(),
                'company_id'        => is_null(auth()->user()) ? null : auth()->user()->__get('company_id'),
                'event_date'        => now(),
                'ip'                => request()->ip(),
                'auditable_id'      => $auditable_model->id,
                'auditable_type'    => $auditable_model::class,
                'details'           => $details
            ]);
        } catch (Throwable $exception) {
            Log::emergency("Error to save log event. {$exception->getMessage()}", $exception->getTrace());
        }
    }
}

if (! function_exists('getStatusSupport')) {
    /**
     * Recuperar o nome do status de um atendimento.
     *
     * @param string $status
     * @return string
     */
    function getStatusSupport(string $status): string
    {
        return match ($status) {
            'open'              => 'Novo',
            'ongoing'           => 'Em atendimento',
            'awaiting_return'   => 'Aguardando retorno',
            'closed'            => 'Finalizado',
            default             => $status,
        };
    }
}

if (! function_exists('getPrioritySupport')) {
    /**
     * Recuperar o nome da prioridade de um atendimento.
     *
     * @param string $priority
     * @return string
     */
    function getPrioritySupport(string $priority): string
    {
        return match ($priority) {
            'new'       => 'Novo',
            'low'       => 'Baixo',
            'medium'    => 'Médio',
            'high'      => 'Alto',
            default     => $priority,
        };
    }
}

if (! function_exists('getColorPrioritySupport')) {
    /**
     * Recuperar a cor da prioridade de um atendimento.
     *
     * @param string $priority
     * @return string
     */
    function getColorPrioritySupport(string $priority): string
    {
        return match ($priority) {
            'low'       => 'primary',
            'medium'    => 'warning',
            'high'      => 'danger',
            default     => 'success',
        };
    }
}

if (!function_exists('getArrayByValueIn')) {
    function getArrayByValueIn(?array $array, string $fieldValidate, string $fieldArray)
    {
        if ($array === null) {
            return array();
        }

        return current(array_filter($array, function($item) use ($fieldValidate, $fieldArray) {
            if (($item->$fieldArray ?? $item[$fieldArray]) == $fieldValidate) {
                return true;
            }
            return false;
        }));
    }
}

if (!function_exists('getLogoUser')) {
    function getLogoUser(string $filename, int $user_id = null): string
    {
        return asset($filename ? "assets/images/profile/$user_id/$filename" : 'assets/images/system/profile.png');
    }
}

if (!function_exists('extractDataPhone')) {
    function extractDataPhone(string $phone_number): array
    {
        $ddd = substr($phone_number, 0, 2);
        $phone = substr($phone_number, 2);

        return [
            'ddd' => $ddd,
            'phone' => $phone
        ];
    }
}
