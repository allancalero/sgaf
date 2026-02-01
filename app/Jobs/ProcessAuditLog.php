<?php

namespace App\Jobs;

use App\Models\AuditLedger;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class ProcessAuditLog implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $payload;

    /**
     * Create a new job instance.
     *
     * @param array $payload
     */
    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // 1. Get Previous Hash (Start of blockchain)
        $previousRecord = AuditLedger::latest('timestamp')->first();
        $previousHash = $previousRecord ? $previousRecord->record_hash : str_repeat('0', 64);

        // 2. Prepare Data for Hashing
        $timestamp = $this->payload['timestamp']; // Expecting microsecond timestamp
        $userId = $this->payload['user_id'];
        $action = $this->payload['action'];
        $newValues = json_encode($this->payload['new_values']);
        
        // Canonical String for Hashing: Timestamp + UserID + Action + NewValues + PreviousHash
        $dataToHash = "{$timestamp}|{$userId}|{$action}|{$newValues}|{$previousHash}";
        
        // 3. Calculate Hash (HMAC with App Key for internal integrity)
        $secret = config('app.key'); 
        $recordHash = hash_hmac('sha256', $dataToHash, $secret);

        // 4. Create Record
        AuditLedger::create([
            'id' => Str::uuid(),
            'event_id' => $this->payload['event_id'] ?? Str::uuid(),
            'timestamp' => $timestamp,
            'user_id' => $userId,
            'session_id' => $this->payload['session_id'],
            'ip_address' => $this->payload['ip_address'],
            'user_agent' => $this->payload['user_agent'],
            'action' => $action,
            'entity_type' => $this->payload['entity_type'] ?? null,
            'entity_id' => $this->payload['entity_id'] ?? null,
            'old_values' => $this->payload['old_values'],
            'new_values' => $this->payload['new_values'],
            'metadata' => $this->payload['metadata'] ?? null,
            'previous_hash' => $previousHash,
            'record_hash' => $recordHash,
            // 'signature' => $this->sign($recordHash), // Implement Digital Signature here if key available
        ]);
    }
}
