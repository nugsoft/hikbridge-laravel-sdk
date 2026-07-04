<?php

namespace Nugsoft\HikBridge\Resources;

use Nugsoft\HikBridge\HikBridgeClient;

class WebhookResource
{
    public function __construct(private readonly HikBridgeClient $client) {}

    public function list(): array
    {
        return $this->client->get('/v1/webhooks');
    }

    public function get(int $webhookId): array
    {
        return $this->client->get("/v1/webhooks/{$webhookId}");
    }

    /**
     * Create a webhook subscription.
     *
     * The signing secret (whsec_...) is returned in the response exactly once — store it.
     *
     * @param array{url: string, event_types: string[], is_active?: bool} $data
     */
    public function create(array $data): array
    {
        return $this->client->post('/v1/webhooks', $data);
    }

    public function update(int $webhookId, array $data): array
    {
        return $this->client->put("/v1/webhooks/{$webhookId}", $data);
    }

    public function delete(int $webhookId): void
    {
        $this->client->delete("/v1/webhooks/{$webhookId}");
    }

    /**
     * Send a test ping to verify the webhook endpoint is reachable.
     */
    public function sendTestPing(int $webhookId): array
    {
        return $this->client->post("/v1/webhooks/{$webhookId}/test");
    }

    /**
     * List recent delivery attempts for a webhook (paginated).
     *
     * Supported params: per_page, page.
     */
    public function deliveries(int $webhookId, array $params = []): array
    {
        return $this->client->get("/v1/webhooks/{$webhookId}/deliveries", $params);
    }
}
