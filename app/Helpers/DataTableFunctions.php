<?php

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

if (!function_exists('getFetchDataTables')) {
    /**
     * @param array              $query
     * @param int|null           $offset
     * @param int|null           $limit
     * @param array              $order_by
     * @param string|null        $group_by
     * @param string|null        $search_text
     * @param array              $filters
     * @param bool               $return_count
     * @param array              $fields_order
     * @return array|array[]|int|Collection
     */
    function getFetchDataTables(
        array   $query,
        ?int    $offset = null,
        ?int    $limit = null,
        array   $order_by = array(),
        ?string $group_by = null,
        string  $search_text = null,
        array   $filters = array(),
        bool    $return_count = false,
        array   $fields_order = array()
    ): array|int|Collection
    {
        $db = doQuery($query);

        if (!empty($search_text) && strlen($search_text) >= 2) {
            $db->where(function($query) use ($fields_order, $search_text) {
                foreach ($fields_order as $field_order) {
                    // Se for um array, consideramos todos os campos no filtro, mas para ordenar o primeiro.
                    if (is_string($field_order)) {
                        if (!empty($field_order)) {
                            $query->orWhere($field_order, 'like', "%$search_text%");
                        }
                    } else {
                        foreach ($field_order as $_field_order) {
                            if (!empty($_field_order)) {
                                $query->orWhere($_field_order, 'like', "%$search_text%");
                            }
                        }
                    }
                }
            });
        }

        /**
         *
         * $filters = [
         *  'where' => [
         *      'column' => 'value'
         *  ]
         * ]
         *
         */
        foreach ($filters as $filters_) {
            foreach ($filters_ as $type_filter => $filter) {
                // vai agrupar a query.
                /*if (in_array($type_filter, ['group_start', 'group_end', 'or_group_start'])) {
                    $db->$type_filter();
                    continue;
                }*/
                foreach ($filter as $column => $value) {
                    if (likeText('% %', $column)) {
                        $exp_column = explode(' ', $column);
                        $db->$type_filter($exp_column[0], $exp_column[1], $value);
                        continue;
                    }

                    $db->$type_filter($column, $value);
                }
            }
        }

        // Existe agrupamento.
        if (!empty($group_by)) {
            if ($return_count) {
                $db->distinct($group_by);
            } else {
                $db->groupBy($group_by);
            }
        }

        // Existe ordenação.
        if (!empty($order_by)) {
            $db->orderBy($order_by[0], $order_by[1]);
        }

        // Existe limite e deslocamento.
        if (!is_null($limit) && !is_null($offset)) {
            $db->limit($limit)->offset($offset);
        }

        return $return_count ? $db->count() : $db->get();
    }
}

if (!function_exists('fetchDataTable')) {
    /**
     * Consulta as informações para retornar ao datatable.
     *
     * @param   array       $query
     * @param   array       $order_by                   Ordem padrão. [field, direction]
     * @param   string|null $group_by                   Campo a ser agrupado.
     * @param   array       $permissions                Permissões a serem validadas pelo usuário. Ex.: ['createCarrierRegistration', 'updateCarrierRegistration]
     * @param   array       $filters                    Filtros adicionais para a consulta. Ex.: ['where_in' => ['csv_to_verification.store_id' => [10,20,30]], 'where' => ['csv_to_verification.module' => 'Shippingcompany', 'csv_to_verification.final_stuation' => 'success']]
     * @param   array       $fields_order               Campos pertencente a tabela no front para realizar a ordenagem dos resultados. Ex.: ['csv_to_verification.id', 'csv_to_verification.upload_file', 'csv_to_verification.created_at', ...]
     * @param   array       $filter_default             Filtros que devem ser considerados na contagem total de registros.
     * @return  array                                           Será retornado
     * @throws  Exception
     */
    function fetchDataTable(
        array $query,
        array $order_by = array(),
        ?string $group_by = null,
        array $permissions = array(),
        array $filters = array(),
        array $fields_order = array(),
        array $filter_default = array()
    ): array
    {
        if (!empty($permissions)) {
            foreach ($permissions as $permission) {
                if (!hasPermission($permission)) {
                    throw new Exception("Sem autorização para fazer essa ação.", 403);
                }
            }
        }

        $body_post  = request()->all();

        $start  = $body_post['start'] ?? null;
        $length = $body_post['length'] ?? null;
        $search = $body_post['search'] ?? null;

        if (empty($start)) {
            $start = 0;
        }

        if (empty($length)) {
            $length = 200;
        }

        if ($length == -1) {
            $length = null;
            $start = null;
        }

        if (!empty($search) && isset($search['value'])) {
            $search_text = trim($search['value']);
        } else {
            $search_text = null;
        }

        if (!empty($body_post['order'])) {
            if ($body_post['order'][0]['dir'] == "asc") {
                $direction = "asc";
            } else {
                $direction = "desc";
            }
            $field = $fields_order[$body_post['order'][0]['column']] ?? '';

            // Se for um array, consideramos o primeiro campo para ordenar.
            $field = is_array($field) ? $field[0] : $field;
            if ($field != "") {
                $order_by = array($field, $direction);
            }
        }

        $filters = array_merge_recursive($filter_default, $filters);

        try {
            $registers = getFetchDataTables(
                $query,
                $start,
                $length,
                $order_by,
                $group_by,
                $search_text,
                $filters,
                false,
                $fields_order
            );

            $count_filtered = getFetchDataTables(
                $query,
                null,
                null,
                $order_by,
                $group_by,
                $search_text,
                $filters,
                true,
                $fields_order
            );

            $count_total = getFetchDataTables(
                $query,
                null,
                null,
                $order_by,
                $group_by,
                null,
                $filter_default,
                true,
                $fields_order
            );
        } catch (Exception $exception) {
            throw new Exception("Não foi possível realizar a consulta.\n" . $exception->getMessage(), 400);
        }

        return array(
            'data'              => $registers,
            'recordsFiltered'   => $count_filtered,
            'recordsTotal'      => $count_total
        );
    }
}

if (!function_exists('doQuery')) {
    /**
     * @param array $query
     * @return Builder
     */
    function doQuery(array $query): Builder
    {
        $db = DB::table($query['from']);

        foreach ($query as $type_field => $fields) {
            if ($type_field === 'from') {
                continue;
            }
            if ($type_field === 'select') {
                $db->select(DB::raw(implode(',', $fields)));
                continue;
            }

            foreach ($fields as $value) {
                if ($type_field == 'join') {
                    $db->join($value[0], $value[1], $value[2], $value[3]);
                }
            }
        }

        return $db;
    }
}
