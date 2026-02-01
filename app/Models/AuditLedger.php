<?php

namespace App\Models;

use Illuminate\Database\Eloquent\HasUuids;
use Illuminate\Database\Eloquent\Model;

class AuditLedger extends Model
{
    use HasUuids;

    protected $table = 'audit_ledger';

    protected $fillable = [
        'event_id',
        'timestamp', // Microseconds
        'user_id',
        'session_id',
        'ip_address',
        'user_agent',
        'action',
        'entity_type',
        'entity_id',
        'old_values',
        'new_values',
        'metadata',
        'record_hash',
        'previous_hash',
        'signature',
    ];

    protected $casts = [
        'timestamp' => 'datetime:Y-m-d H:i:s.u',
        'old_values' => 'array',
        'new_values' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Get the user responsible for the action.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
