<?php

declare(strict_types=1);

namespace App\Service\Integration;

use App\Entity\IntegrationPattern;
use App\Entity\IntegrationResponseMapping;

class ResponseMapper
{
    /**
     * @param array $rawResponse Decoded JSON response from external API
     * @return array Normalized internal model
     */
    public function map(IntegrationPattern $pattern, array $rawResponse): array
    {
        $normalized = [];

        foreach ($pattern->getResponseMappings() as $mapping) {
            $value = $this->extractValue($rawResponse, $mapping->getExternalPath());
            $value = $this->transform($value, $mapping->getTransformer());
            $this->setNestedValue($normalized, $mapping->getInternalField(), $value);
        }

        return $normalized;
    }

    /**
     * Extract value from nested array using dot-notation path (e.g. "data.position.latitude")
     */
    private function extractValue(array $data, string $path): mixed
    {
        $keys = explode('.', $path);
        $current = $data;

        foreach ($keys as $key) {
            if (is_array($current) && array_key_exists($key, $current)) {
                $current = $current[$key];
            } else {
                return null;
            }
        }

        return $current;
    }

    private function transform(mixed $value, ?string $transformer): mixed
    {
        if ($value === null || $transformer === null) {
            return $value;
        }

        return match ($transformer) {
            'float' => (float) $value,
            'int', 'integer' => (int) $value,
            'string' => (string) $value,
            'boolean', 'bool' => (bool) $value,
            'datetime' => $value instanceof \DateTimeInterface ? $value : new \DateTimeImmutable((string) $value),
            'split_first' => is_string($value) && str_contains($value, ',') ? (float) explode(',', $value)[0] : $value,
            'split_second' => is_string($value) && str_contains($value, ',') ? (float) explode(',', $value)[1] : $value,
            'json_decode' => is_string($value) ? json_decode($value, true) : $value,
            'uppercase' => is_string($value) ? strtoupper($value) : $value,
            'lowercase' => is_string($value) ? strtolower($value) : $value,
            'trim' => is_string($value) ? trim($value) : $value,
            default => $value,
        };
    }

    /**
     * Set value in nested array using dot-notation key (e.g. "tracking.lat" → ['tracking']['lat'])
     */
    private function setNestedValue(array &$array, string $path, mixed $value): void
    {
        $keys = explode('.', $path);
        $current = &$array;

        foreach ($keys as $i => $key) {
            if ($i === count($keys) - 1) {
                $current[$key] = $value;
            } else {
                if (!isset($current[$key]) || !is_array($current[$key])) {
                    $current[$key] = [];
                }
                $current = &$current[$key];
            }
        }
    }
}
