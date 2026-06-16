# HikBridge SDK — AI Skill

You are helping a developer work with the `nugsoft/hikbridge-laravel` SDK.
This skill gives you full context on the package so you can write, review, and debug
HikBridge integration code without needing to re-read the source.

---

## Package identity

| | |
|---|---|
| Composer | `nugsoft/hikbridge-laravel` |
| Namespace | `Nugsoft\HikBridge` |
| Facade | `use Nugsoft\HikBridge\Facades\HikBridge;` |
| Config | `config/hikbridge.php` |
| Env vars | `HIKBRIDGE_BASE_URL`, `HIKBRIDGE_API_KEY`, `HIKBRIDGE_TIMEOUT` |
| Requires | PHP 8.2+, Laravel 11+ |

---

## Auth

Every request uses the API key from config as a Bearer token.
The key is scoped to abilities set at creation time:
`organization:read`, `devices:read`, `persons:read`, `persons:write`,
`biometrics:read`, `biometrics:write`, `events:read`, `webhooks:manage`.

A `ForbiddenException` (403) means the key lacks the required ability for that endpoint.

---

## Return types

- All methods return **plain PHP arrays** (decoded JSON).
- Responses follow `{ "data": ... }` — single resources under `data`, lists under `data` with `meta.next_cursor`.
- Async endpoints return a `PendingOperation` object instead of an array.

---

## Full API reference

### Organization
```php
HikBridge::organization()->get(): array
```

### Devices
```php
HikBridge::devices()->list(array $params = []): array
HikBridge::devices()->get(int $deviceId): array
```

### Persons
```php
HikBridge::persons()->list(array $params = []): array
HikBridge::persons()->get(int $personId): array
HikBridge::persons()->create(array $data): array|PendingOperation
  // No device_id in $data → 202 async fan-out → PendingOperation
  // device_id in $data   → 201 sync one device → array
HikBridge::persons()->update(int $personId, array $data): array
HikBridge::persons()->delete(int $personId): PendingOperation      // always async
HikBridge::persons()->deleteFromDevice(int $personId, int $deviceId): array
```

Person fields: `person_code` (required, unique), `first_name`, `last_name`, `status` (`active`|`inactive`).

### Biometrics
```php
HikBridge::biometrics(int $personId)->summary(int $deviceId): array
HikBridge::biometrics(int $personId)->uploadFace(int $deviceId, string $base64Image): array
HikBridge::biometrics(int $personId)->captureFace(int $deviceId): array
HikBridge::biometrics(int $personId)->faceCaptureProgress(int $deviceId): array
HikBridge::biometrics(int $personId)->deleteFace(int $deviceId): array
HikBridge::biometrics(int $personId)->storeFingerprint(int $deviceId, int $fingerIndex, ?string $template = null): array
HikBridge::biometrics(int $personId)->captureFingerprint(int $deviceId, int $fingerIndex = 0): array
HikBridge::biometrics(int $personId)->fingerprintCaptureProgress(int $deviceId): array
HikBridge::biometrics(int $personId)->deleteFingerprint(int $deviceId, int $fingerIndex): array
HikBridge::biometrics(int $personId)->addAccessCard(int $deviceId, string $cardNo, int $cardType = 1): array
HikBridge::biometrics(int $personId)->deleteAccessCard(int $deviceId, string $cardNo): array
```

### Events
```php
HikBridge::events()->list(array $params = []): array
  // params: per_page, cursor, from (ISO8601), to (ISO8601),
  //         person_code, event_type, device_id
HikBridge::events()->triggerSync(?string $from = null, ?string $to = null): array
```

### Webhooks
```php
HikBridge::webhooks()->list(): array
HikBridge::webhooks()->get(int $webhookId): array
HikBridge::webhooks()->create(array $data): array   // secret returned once only
HikBridge::webhooks()->update(int $webhookId, array $data): array
HikBridge::webhooks()->delete(int $webhookId): void
HikBridge::webhooks()->sendTestPing(int $webhookId): array
HikBridge::webhooks()->deliveries(int $webhookId): array
```

Webhook `event_types`: `access.event`, `person.synced`, `*`.
The signing `secret` (`whsec_...`) is returned in `create()` exactly once.

### Operations
```php
HikBridge::operations()->get(string $operationId): array
  // data.status: pending | completed | failed
  // data.devices[]: per-device results
```

---

## PendingOperation

Returned by any 202 response. Has:
```php
$op->operationId  // string
$op->data         // array — the created/deleted entity
$op->isPending()  // always true

$op->waitUntilDone(int $timeout = 60, int $interval = 2): array
  // Polls GET /v1/operations/{id} every $interval seconds.
  // Returns the final operation array on 'completed'.
  // Throws HikBridgeException on 'failed' or timeout.
```

---

## Exceptions

All extend `Nugsoft\HikBridge\Exceptions\HikBridgeException`.

| Class | HTTP | When |
|---|---|---|
| `AuthenticationException` | 401 | Invalid/missing API key |
| `ForbiddenException` | 403 | Key lacks required ability |
| `NotFoundException` | 404 | Resource does not exist |
| `ValidationException` | 422 | Bad input — use `->errors()` |
| `RateLimitException` | 429 | Too many requests |
| `ServerException` | 5xx | HikBridge server error |

---

## Common patterns

### Create person and wait for all devices to sync
```php
$op = HikBridge::persons()->create([
    'person_code' => 'EMP001',
    'first_name'  => 'Amina',
    'last_name'   => 'Nakato',
    'status'      => 'active',
]);
$result = $op->waitUntilDone(timeout: 60);
```

### Create on one device only (sync)
```php
$person = HikBridge::persons()->create([
    'person_code' => 'EMP001',
    'first_name'  => 'Amina',
    'device_id'   => 35,
]);
```

### Upload face
```php
$base64 = base64_encode(file_get_contents($path));
HikBridge::biometrics($personId)->uploadFace($deviceId, $base64);
```

### Paginate events
```php
$cursor = null;
do {
    $page   = HikBridge::events()->list(['per_page' => 100, 'cursor' => $cursor]);
    $cursor = $page['meta']['next_cursor'] ?? null;
    foreach ($page['data'] as $event) { /* process */ }
} while ($cursor);
```

### Verify webhook signature
```php
$expected = 'sha256=' . hash_hmac('sha256', $request->getContent(), $secret);
if (!hash_equals($expected, $request->header('X-HikBridge-Signature'))) {
    abort(401);
}
```

### Full exception handling
```php
use Nugsoft\HikBridge\Exceptions\{NotFoundException, ValidationException, HikBridgeException};

try {
    $person = HikBridge::persons()->get($id);
} catch (NotFoundException $e) {
    // 404
} catch (ValidationException $e) {
    $errors = $e->errors(); // ['field' => ['message']]
} catch (HikBridgeException $e) {
    // catch-all for auth/forbidden/rate-limit/server errors
}
```

---

## Writing tests for HikBridge code

Use `Http::fake()` — no custom mocking needed.

```php
use Illuminate\Support\Facades\Http;

Http::fake([
    '*/v1/persons/57*' => Http::response(['data' => ['id' => 57]], 200),
]);

$person = HikBridge::persons()->get(57);
expect($person['data']['id'])->toBe(57);
Http::assertSentCount(1);
```

Fake a 202 async flow:
```php
Http::fake([
    '*/v1/persons'           => Http::response(['operation_id' => 'op_1', 'data' => []], 202),
    '*/v1/operations/op_1'   => Http::response(['data' => ['status' => 'completed']], 200),
]);

$op = HikBridge::persons()->create(['person_code' => 'EMP001', ...]);
$op->waitUntilDone(timeout: 10, interval: 0);
```

---

## Source layout (for contributors)

```
src/
  HikBridgeClient.php        ← HTTP + exception mapping
  HikBridgeManager.php       ← facade target, creates resources
  HikBridgeServiceProvider.php
  PendingOperation.php       ← 202 wrapper
  Facades/HikBridge.php
  Resources/
    OrganizationResource.php
    DeviceResource.php
    PersonResource.php       ← handles sync vs async via response status
    BiometricResource.php
    EventResource.php
    WebhookResource.php
    OperationResource.php
  Exceptions/
    HikBridgeException.php   ← base
    AuthenticationException, ForbiddenException, NotFoundException
    ValidationException      ← has ->errors() method
    RateLimitException, ServerException
config/hikbridge.php
tests/
  Pest.php                   ← uses(TestCase::class)->in(__DIR__)
  TestCase.php               ← Orchestra Testbench base
  HikBridgeClientTest.php
  PendingOperationTest.php
  Resources/
    PersonResourceTest.php
    WebhookResourceTest.php
```

---

## How to add a new endpoint

1. Find the resource class in `src/Resources/` that owns the endpoint group.
2. Add a method that calls `$this->client->get/post/put/delete()`.
3. If the endpoint can return 202, use `$this->client->postRaw()` / `deleteRaw()`, check
   `$response->status() === 202`, and return a `PendingOperation`.
4. Add a Pest test using `Http::fake()`.

Example:
```php
// In PersonResource.php
public function restore(int $personId): array
{
    return $this->client->post("/v1/persons/{$personId}/restore");
}
```
