<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Models\Audit;
use Illuminate\Http\Request;

class LogSuccessfulLogin
{
    /**
     * The request instance.
     *
     * @var \Illuminate\Http\Request
     */
    public $request;

    /**
     * Create the event listener.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Handle the event.
     *
     * @param  \Illuminate\Auth\Events\Login  $event
     * @return void
     */
    public function handle(Login $event)
    {
        Audit::create([
            'user_id' => $event->user->getAuthIdentifier(),
            'event' => 'login',
            'auditable_type' => get_class($event->user),
            'auditable_id' => $event->user->getAuthIdentifier(),
            'ip_address' => $this->request->ip(),
            'user_agent' => $this->request->userAgent(),
            'url' => $this->request->fullUrl(),
            'tags' => 'auth',
            'old_values' => null,
            'new_values' => null,
        ]);
    }
}
