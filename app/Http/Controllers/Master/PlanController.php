<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PlanController extends Controller
{
    private Plan $plan;

    public function __construct()
    {
        $this->plan = new Plan();
    }

    public function index(): Factory|View|Application
    {
        return view('master.plan.index');
    }

    public function edit(int $id): View|Factory|RedirectResponse|Application
    {
        $plan = $this->plan->getById($id);

        if (!$plan) {
            return redirect()->route('master.plan.index');
        }

        return view('master.plan.update', compact('plan'));
    }

    public function create(): View|Factory|RedirectResponse|Application
    {
        return view('master.plan.update');
    }

    public function fetch(Request $request): JsonResponse
    {
        $result     = array();
        $draw       = $request->input('draw');

        try {
            $filters        = array();
            $filter_default = array();
            $fields_order   = array('name', 'value', 'quantity_equipment', 'month_time', '');

            $query = array(
                'from' => 'plans'
            );

            $data = fetchDataTable(
                $query,
                array('month_time', 'desc'),
                null,
                [],
                $filters,
                $fields_order,
                $filter_default
            );

        } catch (Exception $exception) {
            return response()->json(getErrorDataTables($exception->getMessage(), $draw));
        }

        foreach ($data['data'] as $value) {
            $buttons = "<a href='".route('master.plan.edit', ['id' => $value->id])."' class='btn btn-primary btn-sm btn-rounded btn-action' data-toggle='tooltip' title='Atualizar' ><i class='fas fa-edit'></i></a>";

            $highlight = $value->highlight ? ' <i class="fa fa-star text-warning"></i>' : '';
            $month_time = $value->month_time == 1 ? ' mês' : ' meses';
            $allowed_users = empty($value->allowed_users) ? '' : ' usuários';
            $from_value = empty($value->from_value) ? '' : "<s>".formatMoney($value->from_value, 2, 'R$ ')."</s> <i class='fa fa-arrow-right'></i> ";

            $result[] = array(
                $value->name . $highlight,
                $from_value . formatMoney($value->value, 2, 'R$ '),
                $value->quantity_equipment . ' equipamentos',
                ((int)$value->allowed_users ?: 'Ilimitado') . $allowed_users,
                $value->month_time . $month_time,
                $buttons
            );
        }

        $output = array(
            "draw"              => $draw,
            "recordsTotal"      => $data['recordsTotal'],
            "recordsFiltered"   => $data['recordsFiltered'],
            "data"              => $result
        );

        return response()->json($output);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $update = $this->plan->updateById($this->formatDataPlanToSAve($request), $id);

        if (!$update) {
            return redirect()->back()
                ->withErrors(['Não foi possível atualizar o plano, tente novamente!'])
                ->withInput();
        }

        return redirect()->route('master.plan.index')
            ->with('success', "Plano atualizado com sucesso!");
    }

    public function insert(Request $request): RedirectResponse
    {
        $insert = $this->plan->insert($this->formatDataPlanToSAve($request));

        if (!$insert) {
            return redirect()->back()
                ->withErrors(['Não foi possível cadastrar o plano, tente novamente!'])
                ->withInput();
        }

        return redirect()->route('master.plan.index')
            ->with('success', "Plano cadastrado com sucesso!");
    }

    private function formatDataPlanToSAve(Request $request): array
    {
        return [
            'name'                  => filter_var($request->input('name')),
            'description'           => filter_var($request->input('description')),
            'value'                 => transformMoneyBr_En(filter_var($request->input('value'))),
            'from_value'            => transformMoneyBr_En(filter_var($request->input('from_value'))) ?: null,
            'quantity_equipment'    => filter_var($request->input('quantity_equipment')),
            'highlight'             => (bool)$request->input('highlight'),
            'month_time'            => filter_var($request->input('month_time'), FILTER_VALIDATE_INT),
            'allowed_users'         => filter_var($request->input('allowed_users'), FILTER_VALIDATE_INT, FILTER_FLAG_EMPTY_STRING_NULL) ?: null
        ];
    }
}
