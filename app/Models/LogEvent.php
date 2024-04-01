<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogEvent extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'event_date' => 'datetime',
        'details'   => 'json'
    ];

    public function getById(int $id)
    {
        return $this->find($id);
    }

    public function getGroupByAny(string $group_by)
    {
        return $this->groupBy($group_by)->get();
    }

    public function getToViewLogById(int $id)
    {
        return $this->select('log_events.*', 'companies.name as company_name', 'users.email as user_email')
            ->leftJoin('companies', 'companies.id', '=', 'log_events.company_id')
            ->leftJoin('users', 'users.id', '=', 'log_events.user_id')
            ->where('log_events.id', $id)
            ->first();
    }

    public function getRelationshipLogsToViewLogByAuditableTypeAndAuditableId(string $auditable_type, string $auditable_id)
    {
        return $this->select('log_events.*', 'companies.name as company_name', 'users.email as user_email')
            ->leftJoin('companies', 'companies.id', '=', 'log_events.company_id')
            ->leftJoin('users', 'users.id', '=', 'log_events.user_id')
            ->where([
                'auditable_type' => $auditable_type,
                'auditable_id' => $auditable_id
            ])
            ->get();
    }
}
