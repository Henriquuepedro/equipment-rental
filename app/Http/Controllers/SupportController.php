<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Support;
use App\Models\SupportMessage;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SupportController extends Controller
{
    private Support $support;
    private SupportMessage $support_message;
    private Company $company;
    private User $user;

    public function __construct()
    {
        $this->support = new Support();
        $this->support_message = new SupportMessage();
        $this->company = new Company();
        $this->user = new User();
    }

    public function index()
    {
        $companies = array();
        if (hasAdminMaster()) {
            $companies = $this->company->getAllCompaniesActive();
        }

        return view('support.index', ['companies' => $companies]);
    }

    public function create()
    {
        $path_files = getKeyRandom();

        return view('support.create', ['path_files' => $path_files]);
    }

    public function insert(Request $request): RedirectResponse
    {
        $description = strip_tags($request->input('description'), FULL_ALLOWABLE_TAGS);

        $validator = Validator::make($request->all(),
            [
                'subject' => 'required',
            ], [
                'subject.required' => 'Assunto do atendimento é inválido.'
            ]
        );

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors( $validator->errors()->all())
                ->withInput();
        }

        if ($description === '<p><br></p>') {
            return redirect()->back()
                ->withErrors(['Descrição do atendimento é inválida.'])
                ->withInput();
        }

        $this->support->insert(array(
            'company_id'    => $request->user()->company_id,
            'user_created'  => $request->user()->id,
            'subject'       => $request->input('subject'),
            'description'   => $description,
            'path_files'    => $request->input('path_files'),
            'status'        => 'open'
        ));

        return redirect()->route('support.index')
            ->with('success', "Atendimento cadastrado com sucesso!");
    }


    /**
     * @throws Exception
     */
    public function saveImageDescription(Request $request, string $path): JsonResponse
    {
        if ($request->hasFile('image')) {
            $validator = Validator::make($request->file(),
                [
                    'image' => 'required|image|mimes:apng,avif,gif,jpeg,jpg,png,svg,bmp,tiff,webp|max:4096',
                ], [
                    'image.required'    => 'Imagem é obrigatório.',
                    'image.image'       => 'O arquivo deve ser uma imagem.',
                    'image.mimes'       => 'São aceitos os tipos gif, jpeg, jpg, png, svg.',
                    'image.max'         => 'O tamanho máximo é de 4mb.'
                ]
            );

            if ($validator->fails()) {
                return response()->json($validator->errors()->all(), 400);
            }

            $file       = $request->file('image');
            $fileName   = getKeyRandom() . '.' . $file->getClientOriginalExtension();

            $uploadPath = "assets/images/support/$path";
            checkPathExistToCreate($uploadPath);

            $file->move($uploadPath, $fileName);

            return response()->json(array('data' => "$uploadPath/$fileName"));
        }

        return response()->json(array('Arquivo não aceito.'), 400);
    }

    public function listSupports(Request $request): JsonResponse
    {
        $company_id     = Auth::user()->__get('company_id');
        $is_admin       = hasAdminMaster();
        $company        = $request->input('company');
        $priority       = $request->input('priority');
        $interval_dates = explode(' - ', $request->input('interval_dates'));
        $date_start     = null;
        $date_end       = null;
        if (count($interval_dates) === 2) {
            $date_start = dateBrazilToDateInternational($interval_dates[0]) . ' 00:00:00';
            $date_end = dateBrazilToDateInternational($interval_dates[1]) . ' 23:59:59';
        }

        $filter = [];

        if (!empty($company)) {
            $filter[] = ['supports.company_id', '=', $company];
        }
        if (!empty($priority)) {
            $filter[] = ['supports.priority', '=', $priority];
        }
        if (!empty($date_start) && !empty($date_end)) {
            $filter[] = ['supports.created_at', '>=', $date_start];
            $filter[] = ['supports.created_at', '<=', $date_end];
        }

        $supports = $is_admin ? $this->support->getAll($filter) : $this->support->getAllByCompany($company_id, $filter);

        return response()->json(array_map(function($support) {
            return [
                'code'          => formatCodeIndex($support['id'], 3),
                'id'            => $support['id'],
                'subject'       => $support['subject'],
                'status'        => $support['status'],
                'status_name'   => getStatusSupport($support['status']),
                'company_name'  => $support['company_name'],
                'user_name'     => $support['user_name'],
                'priority'      => $support['priority'] ?? 'new',
                'priority_name' => getPrioritySupport($support['priority'] ?? 'new'),
                'priority_color'=> getColorPrioritySupport($support['priority'] ?? 'new'),
                'created_at'    => date('d', strtotime($support['created_at'])) . ' de ' . MONTH_NAME_PT[date('m', strtotime($support['created_at']))] . ' às ' . date('H:i', strtotime($support['created_at']))
            ];
        }, $supports->toArray()));
    }

    public function getSupport(int $support_id): JsonResponse
    {
        $company_id = Auth::user()->__get('company_id');
        $is_admin = hasAdminMaster();

        $support = $is_admin ? $this->support->getByid($support_id) : $this->support->getByCompany($company_id, $support_id);

        if (!$support) {
            return response()->json(array('message' => 'Atendimento não localizado.'), 400);
        }

        $support_message = $this->support_message->getByCompany($support_id);

        $users = array_map(function($user) {
                return $this->user->getUserById($user)->toArray();
            },
            array_unique(array_map(function($message) {
                return $message['user_created'];
            }, $support_message->toArray()))
        );

        return response()->json(array(
            'support' => [
                'id'            => $support['id'],
                'subject'       => $support['subject'],
                'description'   => $support['description'],
                'status'        => $support['status'],
                'status_name'   => getStatusSupport($support['status']),
                'priority'      => $support['priority'] ?? 'new',
                'priority_name' => getPrioritySupport($support['priority'] ?? 'new'),
                'priority_color'=> getColorPrioritySupport($support['priority'] ?? 'new'),
                'path_files'    => $support['path_files'],
                'created_at'    => date('d', strtotime($support['created_at'])) . ' de ' . MONTH_NAME_PT[date('m', strtotime($support['created_at']))]
            ],
            'support_message' => array_map(function($message) use ($users) {

                $comment_user = getArrayByValueIn($users, $message['user_created'], 'id');

                if (!$comment_user) {
                    return null;
                }

                return [
                    'support_id'    => $message['support_id'],
                    'user_created'  => $message['user_created'],
                    'user_name'     => $comment_user['name'],
                    'description'   => $message['description'],
                    'sent_by'       => $message['sent_by'],
                    'logo_message'  => asset($message['sent_by'] == 'user' ? ($comment_user['profile'] ? "assets/images/profile/$comment_user[id]/$comment_user[profile]" : 'assets/images/system/profile.png') : 'assets/images/system/logo.png'),
                    'created_at'    => date('d', strtotime($message['created_at'])) . ' de ' . MONTH_NAME_PT[date('m', strtotime($message['created_at']))] . ' ' . date('H:i', strtotime($message['created_at']))
                ];
            }, $support_message->toArray())
        ));
    }

    public function registerComment(Request $request, int $support_id): JsonResponse
    {
        $company_id = $request->user()->company_id;
        $support = hasAdminMaster() ? $this->support->getByid($support_id) : $this->support->getByCompany($company_id, $support_id);

        if (!$support) {
            return response()->json(['Atendimento não localizado.'], 400);
        }

        $description = $request->input('description');
        $mark_close  = (bool)$request->input('mark_close');

        if ($description === '<p><br></p>') {
            return response()->json(['Descrição do atendimento é inválida.'], 400);
        }

        $this->support_message->insert(array(
            'support_id'    => $support_id,
            'user_created'  => $request->user()->id,
            'company_id'    => $company_id,
            'description'   => $description,
            'sent_by'       => hasAdminMaster() ? 'operator' : 'user'
        ));


        // Fechou o atendimento.
        if ($mark_close) {
            $data_support = array(
                'status'    => 'closed',
                'open'      => 0,
                'closed_at' => dateNowInternational()
            );

            hasAdminMaster() ?
                $this->support->updateBySupport($support_id, $data_support):
                $this->support->updateBySupportAndCompany($company_id, $support_id, $data_support);
        }

        // Usuário respondeu um atendimento que aguardava retorno.
        // Administrador respondeu um novo atendimento.
        if (
            $support->status === 'awaiting_return' && !hasAdminMaster() ||
            $support->status === 'open' && hasAdminMaster()
        ) {
            $data_support = array(
                'status'    => 'ongoing'
            );
            hasAdminMaster() ?
                $this->support->updateBySupport($support_id, $data_support):
                $this->support->updateBySupportAndCompany($company_id, $support_id, $data_support);
        }

        // Administrador respondeu um atendimento em atendimento.
        if ($support->status === 'ongoing' && hasAdminMaster()) {
            $data_support = array(
                'status'    => 'awaiting_return'
            );
            hasAdminMaster() ?
                $this->support->updateBySupport($support_id, $data_support):
                $this->support->updateBySupportAndCompany($company_id, $support_id, $data_support);
        }

        return response()->json();
    }

    public function updatePriority(Request $request, int $support_id): JsonResponse
    {
        if (!hasAdminMaster()) {
            return response()->json([
                'success' => false,
                'message' => 'Sem autorização para fazer essa ação.'
            ]);
        }

        $support        = $this->support->getByid($support_id);
        $new_priority   = $request->input('new_priority');

        if (!$support) {
            return response()->json([
                'success' => false,
                'message' => 'Atendimento não localizado.'
            ]);
        }

        if (!in_array($new_priority, [
            'new',
            'low',
            'medium',
            'high'
        ])) {
            return response()->json([
                'success' => false,
                'message' => 'Prioridade não localizada.'
            ]);
        }

        if ($support->priority == $new_priority) {
            return response()->json([
                'success' => false,
                'message' => 'Atendimento ja contém essa mesma prioridade.'
            ]);
        }

        $data_priority = array(
            'priority' => $new_priority
        );

        $this->support->updateBySupport($support_id, $data_priority);

        return response()->json([
            'success' => true,
            'message' => 'Prioridade atualizada com sucesso.'
        ]);
    }

    public function updateStatus(Request $request, int $support_id): JsonResponse
    {
        if (!hasAdminMaster()) {
            return response()->json([
                'success' => false,
                'message' => 'Sem autorização para fazer essa ação.'
            ]);
        }

        $support    = $this->support->getByid($support_id);
        $new_status = $request->input('new_status');

        if (!$support) {
            return response()->json([
                'success' => false,
                'message' => 'Atendimento não localizado.'
            ]);
        }

        if (!in_array($new_status, [
            'open',
            'ongoing',
            'awaiting_return',
            'closed'
        ])) {
            return response()->json([
                'success' => false,
                'message' => 'Situação não localizada.'
            ]);
        }

        if ($support->status == $new_status) {
            return response()->json([
                'success' => false,
                'message' => 'Atendimento ja contém essa mesma situação.'
            ]);
        }

        // Dados para atualizar o status.
        $data_update = array(
            'status' => $new_status
        );

        // Reabriu atendimento.
        if ($support->status == 'closed') {
            $data_update['open'] = 1;
            $data_update['closed_at'] = null;
        }

        $this->support->updateBySupport($support_id, $data_update);

        return response()->json([
            'success' => true,
            'message' => 'Situação atualizada com sucesso.'
        ]);
    }
}
