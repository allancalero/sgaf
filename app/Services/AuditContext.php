<?php

namespace App\Services;

class AuditContext
{
    protected array $context = [];

    /**
     * Set context data.
     *
     * @param array $data
     * @return void
     */
    public function set(array $data): void
    {
        $this->context = array_merge($this->context, $data);
    }

    /**
     * Get specific key or all context.
     *
     * @param string|null $key
     * @return mixed
     */
    public function get(?string $key = null): mixed
    {
        if ($key) {
            return $this->context[$key] ?? null;
        }
        return $this->context;
    }

    /**
     * Get the current context as JSON.
     * 
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this->context);
    }
}
