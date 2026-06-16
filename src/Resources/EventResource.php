<?php

namespace Nugsoft\HikBridge\Resources;

use Nugsoft\HikBridge\HikBridgeClient;

class EventResource
{
    public function __construct(private readonly HikBridgeClient $client) {}

    /**
     * List access events (cursor-paginated, newest first).
     *
     * Supported params: per_page, from, to, person_code, event_type, device_id, cursor
     */
    public function list(array $params = []): array
    {
        return $this->client->get('/v1/events', $params);
    }

    /**
     * Manually trigger an event sync from devices.
     * Optional $from/$to (ISO 8601) narrow the window; defaults to last 24h on the server.
     */
    public function triggerSync(?string $from = null, ?string $to = null): array
    {
        $body = array_filter(['from' => $from, 'to' => $to]);

        return $this->client->post('/v1/events/sync', $body);
    }
}
