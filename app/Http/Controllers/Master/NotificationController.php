<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Notification;
use App\Models\Permission;
use DateTime;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    private Notification $notification;
    private Permission $permission;
    private Company $company;

    public function __construct()
    {
        $this->notification = new Notification();
        $this->permission = new Permission();
        $this->company = new Company();
    }

    public function index(): Factory|View|RedirectResponse|Application
    {
        return view('master.notification.index');
    }

    public function fetch(Request $request): JsonResponse
    {
        $result     = array();
        $draw       = $request->input('draw');

        try {
            $filters        = array();
            $filter_default = array();
            $fields_order   = array('id', 'title', 'expires_in', 'only_permission', 'active', 'created_at', '');

            $query = array(
                'from' => 'notifications'
            );

            $data = fetchDataTable(
                $query,
                array('id', 'desc'),
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
            $result[] = array(
                $value->id,
                $value->title,
                formatDateInternational($value->expires_in, DATETIME_BRAZIL_NO_SECONDS) ?? '',
                $value->only_permission ? $this->permission->getById($value->only_permission)->name : '',
                getHtmlStatusList($value->active),
                formatDateInternational($value->created_at, DATETIME_BRAZIL_NO_SECONDS),
                newDropdownButtonsDataList([
                    [
                        'tag'   => 'a',
                        'title' => 'Atualizar Notificação',
                        'icon'  => 'fas fa-edit',
                        'href'  => route('master.notification.edit', ['id' => $value->id])
                    ],
                    [
                        'tag'       => 'button',
                        'title'     => 'Excluir Notificação',
                        'icon'      => 'fas fa-times',
                        'class'     => 'btnRemove',
                        'attribute' => "data-notification-id='$value->id'"
                    ]
                ], $value->id)
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

    public function create(): Factory|View|RedirectResponse|Application
    {
        $companies = $this->company->getAllCompaniesActive();
        $user_permissions = $this->permission->getAllPermissions();
        return view('master.notification.create', compact('companies', 'user_permissions'));
    }


    public function insert(Request $request): JsonResponse|RedirectResponse
    {
        $this->notification->insert([
            'company_id'        => filter_var($request->input('company_id'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL),
            'user_insert'       => $request->user()->id,
            'expires_in'        => $request->input('expires_in') ? DateTime::createFromFormat(DATETIME_BRAZIL_NO_SECONDS, $request->input('expires_in'))->format(DATETIME_INTERNATIONAL) : null,
            'only_permission'   => filter_var($request->input('only_permission'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL),
            'title'             => filter_var($request->input('title'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL),
            'description'       => strip_tags($request->input('description'), HALF_ALLOWABLE_TAGS),
            'title_icon'        => filter_var($request->input('title_icon'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL),
            'active'            => $request->has('active')
        ]);

        return redirect()->route('master.notification.index')
            ->with('success', "Notificação cadastrada com sucesso!");
    }

    public function edit(int $id): Factory|View|RedirectResponse|Application
    {
        $notification = $this->notification->get($id);

        if (!$notification) {
            return redirect()->back()
                ->withErrors(['Notificação não encontrada'])
                ->withInput();
        }
        $companies = $this->company->getAllCompaniesActive();
        $user_permissions = $this->permission->getAllPermissions();

        return view('master.notification.create', ['notification' => $notification, 'companies' => $companies, 'user_permissions' => $user_permissions]);
    }


    public function update(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $notification = $this->notification->get($id);

        if (!$notification) {
            return redirect()->back()
                ->withErrors(['Notificação não encontrada'])
                ->withInput();
        }

        $this->notification->edit([
            'company_id'        => filter_var($request->input('company_id'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL),
            'user_insert'       => $request->user()->id,
            'expires_in'        => $request->input('expires_in') ? DateTime::createFromFormat(DATETIME_BRAZIL_NO_SECONDS, $request->input('expires_in'))->format(DATETIME_INTERNATIONAL) : null,
            'only_permission'   => filter_var($request->input('only_permission'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL),
            'title'             => filter_var($request->input('title'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL),
            'description'       => strip_tags($request->input('description'), HALF_ALLOWABLE_TAGS),
            'title_icon'        => filter_var($request->input('title_icon'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL),
            'active'            => $request->has('active'),
            'read'              => null,
            'read_at'           => null,
            'user_read_by'      => null,
        ], $id);

        return redirect()->route('master.notification.index')
            ->with('success', "Notificação atualizada com sucesso!");
    }

    public function delete(Request $request): JsonResponse
    {
        $notification_id = $request->input('notification_id');

        if (!$this->notification->get($notification_id)) {
            return response()->json(['success' => false, 'message' => 'Não foi possível localizar a notificação!']);
        }

        if (!$this->notification->remove($notification_id)) {
            return response()->json(['success' => false, 'message' => 'Não foi possível excluir a notificação!']);
        }

        return response()->json(['success' => true, 'message' => 'Notificação excluída com sucesso!']);
    }
}
