<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    private Notification $notification;

    public function __construct()
    {
        $this->notification = new Notification();
    }

    public function index(): Factory|View|RedirectResponse|Application
    {
        if (!hasPermission('NotificationView')) {
            return redirect()->route('dashboard')
                ->with('warning', "Você não tem permissão para acessar essa página!");
        }

        return view('notification.index');
    }

    public function view(int $id): View|Factory|RedirectResponse|Application
    {
        if (!hasPermission('NotificationView')) {
            return redirect()->route('notification.index')
                ->with('warning', "Você não tem permissão para acessar essa página!");
        }

        $company_id = Auth::user()->__get('company_id');

        $notification = $this->notification->getByid($company_id, $id);
        if (!$notification) {
            return redirect()->route('notification.index');
        }

        if (!$notification->read) {
            $this->notification->edit(array(
                'read' => true,
                'user_read_by' => Auth::user()->__get('id'),
                'read_at' => dateNowInternational()
            ), $id);
        }

        return view('notification.view', compact('notification'));

    }

    public function fetch(Request $request): JsonResponse
    {
        $read       = $request->input('read');
        $result     = array();
        $draw       = $request->input('draw');
        $company_id = $request->user()->company_id;

        try {
            $filters        = array();
            $filter_default = array();
            $fields_order   = array('title','active','');

            $filter_default[]['where']['function'] = function($query) use ($company_id) {
                $query->where('company_id', null)
                    ->orWhere('company_id', $company_id);
            };

            if (!is_null($read) && $read !== 'all') {
                $filters[]['where']['read'] = $read;
            }

            $query = array(
                'from' => 'notifications'
            );

            $data = fetchDataTable(
                $query,
                array('title', 'asc'),
                null,
                ['NotificationView'],
                $filters,
                $fields_order,
                $filter_default
            );
        } catch (Exception $exception) {
            return response()->json(getErrorDataTables($exception->getMessage(), $draw));
        }

        foreach ($data['data'] as $value) {
            $buttons = "<a href='".route('notification.view', ['id' => $value->id])."' class='dropdown-item'><i class='fas fa-eye'></i> Visualizar</a>";

            $result[] = array(
                $value->title,
                $value->read ? '<div class="badge badge-pill badge-lg badge-success">Lido</div>' : '<div class="badge badge-pill badge-lg badge-danger">Não lido</div>',
                dropdownButtonsDataList($buttons, $value->id)
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
}
