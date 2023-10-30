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

            $result[] = array(
                $value->name,
                formatMoney($value->value, 2, 'R$ '),
                $value->quantity_equipment,
                $value->month_time,
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
        $update = $this->plan->updateById([
            'name'               => filter_var($request->input('name')),
            'description'        => filter_var($request->input('description')),
            'value'              => transformMoneyBr_En(filter_var($request->input('value'))),
            'quantity_equipment' => filter_var($request->input('quantity_equipment')),
            'month_time'         => filter_var($request->input('month_time'), FILTER_VALIDATE_INT)
        ], $id);

        if (!$update) {
            return redirect()->back()
                ->withErrors(['Não foi possível atualizar o plano, tente novamente!'])
                ->withInput();
        }

        return redirect()->route('master.plan.index')
            ->with('success', "Plano atualizado com sucesso!");
    }
}
