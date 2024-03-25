<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\LogEvent;
use App\Models\User;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    private LogEvent $log_event;
    private User $user;
    private Company $company;

    public function __construct()
    {
        $this->log_event = new LogEvent();
        $this->user = new User();
        $this->company = new Company();
    }

    public function index(): Factory|View|Application
    {
        $user = $this->user;
        $auditable_types    = array_map(fn($log_event) => $log_event['auditable_type'], $this->log_event->getGroupByAny('auditable_type')->toArray());
        $events             = array_map(fn($log_event) => $log_event['event'], $this->log_event->getGroupByAny('event')->toArray());
        $users              = array_map(function ($log_event) use ($user) {
            return [
                'id'    => $log_event['user_id'],
                'email' => $user->getUserById($log_event['user_id'])->email
            ];
        }, array_filter($this->log_event->getGroupByAny('user_id')->toArray(), fn($log_event) => !is_null($log_event['user_id'])));
        $companies = $this->company->getAllCompanies();

        return view('master.log_event.index', ['auditable_types' => $auditable_types, 'events' => $events, 'users' => $users, 'companies' => $companies]);
    }

    public function view(int $id): View|Factory|RedirectResponse|Application
    {
        $log = $this->log_event->getToViewLogById($id);

        if (!$log) {
            return redirect()->route('master.log_event.index');
        }

        $relationship_logs = $this->log_event->getRelationshipLogsToViewLogByAuditableTypeAndAuditableId($log->auditable_type, $log->auditable_id);

        return view('master.log_event.view', compact('log', 'relationship_logs'));
    }

    public function create(): View|Factory|RedirectResponse|Application
    {
        return view('master.plan.update');
    }

    public function fetch(Request $request): JsonResponse
    {
        $result = array();
        $draw = $request->input('draw');

        try {
            $filters = array();
            $filter_default = array();
            $fields_order = array('auditable_type', 'event', 'user', 'auditable_id', 'created_at', '');

            $auditable_type = $request->input('auditable_type');
            $event          = $request->input('event');
            $user           = $request->input('user');
            $auditable_id   = $request->input('auditable_id');
            $company        = $request->input('company');
            $interval_dates = explode(' - ', $request->input('intervalDates'));
            $date_start     = dateBrazilToDateInternational($interval_dates[0]);
            $date_end       = dateBrazilToDateInternational($interval_dates[1]);

            if (!empty($auditable_type)) {
                $filters[]['where']['auditable_type'] = $auditable_type;
            }
            if (!empty($event)) {
                $filters[]['where']['event'] = $event;
            }
            if (!empty($user)) {
                $filters[]['where']['user'] = $user;
            }
            if (!empty($auditable_id)) {
                $filters[]['where']['auditable_id'] = $auditable_id;
            }
            if (!empty($intervalDates)) {
                $filters[]['where']['intervalDates'] = $intervalDates;
            }
            if (!empty($company)) {
                $filters[]['where']['company_id'] = $company;
            }

            $filter_default[]['whereBetween']['created_at'] = [$date_start, $date_end];

            $query = array(
                'from' => 'log_events'
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
            $buttons = "<a href='" . route('master.audit_log.view', ['id' => $value->id]) . "' class='dropdown-item' data-rental-id='$value->id'><i class='fas fa-eye'></i> Visualizar Log</a>";
            $buttons = dropdownButtonsDataList($buttons, $value->id);

            $result[] = array(
                $value->auditable_type,
                $value->event,
                $value->user_id,
                $value->auditable_id,
                formatDateInternational($value->event_date, DATETIME_BRAZIL),
                $buttons
            );
        }

        $output = array(
            "draw" => $draw,
            "recordsTotal" => $data['recordsTotal'],
            "recordsFiltered" => $data['recordsFiltered'],
            "data" => $result
        );

        return response()->json($output);
    }
}
