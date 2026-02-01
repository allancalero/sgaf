<?php

namespace App\Observers;

use App\Jobs\ProcessAuditLog;
use App\Services\AuditContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AuditObserver
{
    protected $auditContext;

    public function __construct(AuditContext $auditContext)
    {
        $this->auditContext = $auditContext;
    }

    public function created(Model $model)
    {
        $this->dispatchLog($model, 'CREATE', [], $model->toArray());
    }

    public function updated(Model $model)
    {
        // Only log if something actually changed
        if ($model->isDirty()) {
            $this->dispatchLog(
                $model, 
                'UPDATE', 
                $this->getOriginalValues($model), 
                $model->getChanges()
            );
        }
    }

    public function deleted(Model $model)
    {
        $this->dispatchLog($model, 'DELETE', $model->toArray(), []);
    }

    protected function dispatchLog(Model $model, string $action, array $old, array $new)
    {
        $context = $this->auditContext->get();
        
        $payload = [
            'event_id' => (string) Str::uuid(),
            'timestamp' => now()->format('Y-m-d H:i:s.u'),
            'user_id' => auth()->id(),
            'session_id' => $context['session_id'] ?? null,
            'ip_address' => $context['ip'] ?? request()->ip(),
            'user_agent' => $context['user_agent'] ?? request()->userAgent(),
            'action' => $action,
            'entity_type' => get_class($model),
            'entity_id' => $model->getKey(),
            'old_values' => $old,
            'new_values' => $new,
            'metadata' => $context['geolocation'] ?? [],
        ];

        // Dispatch to Queue (Critical for performance)
        ProcessAuditLog::dispatch($payload)->onQueue('audits');
    }

    protected function getOriginalValues(Model $model)
    {
        $original = $model->getOriginal();
        $changes = $model->getChanges();
        
        return array_intersect_key($original, $changes);
    }
}
