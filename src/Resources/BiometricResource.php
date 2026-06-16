<?php

namespace Nugsoft\HikBridge\Resources;

use Nugsoft\HikBridge\HikBridgeClient;

class BiometricResource
{
    public function __construct(
        private readonly HikBridgeClient $client,
        private readonly int $personId,
    ) {}

    public function summary(int $deviceId): array
    {
        return $this->client->get("/v1/persons/{$this->personId}/biometrics", [
            'device_id' => $deviceId,
        ]);
    }

    // ── Face ─────────────────────────────────────────────────────────────────

    /**
     * Upload a base64-encoded face image (data-URI prefix is optional).
     */
    public function uploadFace(int $deviceId, string $base64Image): array
    {
        return $this->client->post("/v1/persons/{$this->personId}/face", [
            'device_id' => $deviceId,
            'image'     => $base64Image,
        ]);
    }

    /**
     * Trigger a live face capture on the enrollment station camera.
     * Poll progress with faceCaptureProgress().
     */
    public function captureFace(int $deviceId): array
    {
        return $this->client->post("/v1/persons/{$this->personId}/face/capture", [
            'device_id' => $deviceId,
        ]);
    }

    public function faceCaptureProgress(int $deviceId): array
    {
        return $this->client->get("/v1/persons/{$this->personId}/face/capture/progress", [
            'device_id' => $deviceId,
        ]);
    }

    public function deleteFace(int $deviceId): array
    {
        return $this->client->delete("/v1/persons/{$this->personId}/face?device_id={$deviceId}");
    }

    // ── Fingerprint ──────────────────────────────────────────────────────────

    /**
     * Store a fingerprint template.
     * Pass $template = null to record enrollment locally without pushing a template.
     */
    public function storeFingerprint(int $deviceId, int $fingerIndex, ?string $template = null): array
    {
        return $this->client->post("/v1/persons/{$this->personId}/fingerprints", [
            'device_id'    => $deviceId,
            'finger_index' => $fingerIndex,
            'template'     => $template,
        ]);
    }

    /**
     * Trigger a live fingerprint capture on the enrollment station.
     * $fingerIndex: 0–9 (0 = right thumb). Poll progress with fingerprintCaptureProgress().
     */
    public function captureFingerprint(int $deviceId, int $fingerIndex = 0): array
    {
        return $this->client->post("/v1/persons/{$this->personId}/fingerprints/capture", [
            'device_id'    => $deviceId,
            'finger_index' => $fingerIndex,
        ]);
    }

    public function fingerprintCaptureProgress(int $deviceId): array
    {
        return $this->client->get("/v1/persons/{$this->personId}/fingerprints/capture/progress", [
            'device_id' => $deviceId,
        ]);
    }

    public function deleteFingerprint(int $deviceId, int $fingerIndex): array
    {
        return $this->client->delete(
            "/v1/persons/{$this->personId}/fingerprints/{$fingerIndex}?device_id={$deviceId}"
        );
    }

    // ── Access card ──────────────────────────────────────────────────────────

    /**
     * Register an access card for the person.
     * $cardType: 1 = normal (device-defined, up to 4).
     */
    public function addAccessCard(int $deviceId, string $cardNo, int $cardType = 1): array
    {
        return $this->client->post("/v1/persons/{$this->personId}/access-card", [
            'device_id' => $deviceId,
            'card_no'   => $cardNo,
            'card_type' => $cardType,
        ]);
    }

    public function deleteAccessCard(int $deviceId, string $cardNo): array
    {
        return $this->client->deleteWithBody("/v1/persons/{$this->personId}/access-card", [
            'device_id' => $deviceId,
            'card_no'   => $cardNo,
        ]);
    }
}
