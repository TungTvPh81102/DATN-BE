<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Approvable extends Model
{
    use HasFactory;

    protected $fillable = [
        'approver_id',
        'status',
        'approvable_type',
        'approvable_id',
        'note',
        'request_date',
        'approved_at',
        'rejected_at',
        'reason',
        'content_modification',
        'approval_logs'
    ];

    protected $casts = [
        'request_date' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'approval_logs' => 'array',
    ];

    public function approvable()
    {
        return $this->morphTo();
    }
    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'approvable_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'approvable_id');
    }

    public function membershipPlan()
    {
        return $this->belongsTo(MembershipPlan::class, 'approvable_id');
    }

    public function post()
    {
        return $this->belongsTo(Post::class, 'approvable_id');
    }

    public function logApprovalAction($status, $approverId, $note = null, $reason = null)
    {
        $logs = $this->approval_logs ?? [];

        if (!is_array($logs)) {
            $logs = json_decode($logs, true) ?? [];
        }

        $logs[] = [
            'name' => $approverId ? (is_object($approverId) ? $approverId->name : $approverId) : 'Hệ thống',
            'status' => $status,
            'note' => $note,
            'reason' => $reason,
            'action_at' => now()->toDateTimeString(),
        ];

        $this->update(['approval_logs' => $logs]);
    }
}
