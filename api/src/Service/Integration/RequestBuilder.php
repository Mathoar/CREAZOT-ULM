<?php

declare(strict_types=1);

namespace App\Service\Integration;

use App\Entity\IntegrationPattern;

class RequestBuilder
{
    /**
     * @param array<string, string> $variables
     * @return array{method: string, url: string, options: array}
     */
    public function build(IntegrationPattern $pattern, array $variables): array
    {
        $url = $this->interpolate($pattern->getUrlTemplate(), $variables);

        $options = [];

        $headers = [];
        foreach ($pattern->getHeaders() ?? [] as $header) {
            $name = $header['name'] ?? '';
            $value = $this->interpolate($header['value'] ?? '', $variables);
            if ($name !== '') {
                $headers[$name] = $value;
            }
        }
        if (!empty($headers)) {
            $options['headers'] = $headers;
        }

        $queryParams = [];
        foreach ($pattern->getQueryParams() ?? [] as $param) {
            $name = $param['name'] ?? '';
            $value = $this->interpolate($param['value'] ?? '', $variables);
            if ($name !== '') {
                $queryParams[$name] = $value;
            }
        }
        if (!empty($queryParams)) {
            $options['query'] = $queryParams;
        }

        if (in_array($pattern->getMethod(), ['POST', 'PUT', 'PATCH'], true) && $pattern->getBodyTemplate()) {
            $contentType = $pattern->getContentType() ?? 'application/json';
            $body = $this->interpolateBody($pattern->getBodyTemplate(), $variables, $contentType);

            $options['headers']['Content-Type'] = $contentType;
            $options['body'] = $body;
        }

        return [
            'method' => $pattern->getMethod(),
            'url' => $url,
            'options' => $options,
        ];
    }

    /**
     * Interpolation simple sans encodage (pour URL et headers).
     */
    private function interpolate(string $template, array $variables): string
    {
        return preg_replace_callback('/\{\{(\w+)\}\}/', function (array $matches) use ($variables) {
            return $variables[$matches[1]] ?? $matches[0];
        }, $template);
    }

    /**
     * Interpolation du body avec auto-encoding selon le contentType.
     * - application/json : json_encode des valeurs (échappe guillemets, retours ligne, unicode)
     * - application/x-www-form-urlencoded : urlencode des valeurs
     * - autres : interpolation brute (rétrocompatibilité)
     */
    private function interpolateBody(string $template, array $variables, string $contentType): string
    {
        $isJson = str_contains($contentType, 'json');
        $isFormUrlEncoded = str_contains($contentType, 'x-www-form-urlencoded');

        return preg_replace_callback('/\{\{(\w+)\}\}/', function (array $matches) use ($variables, $isJson, $isFormUrlEncoded) {
            $value = $variables[$matches[1]] ?? $matches[0];

            if ($isJson) {
                $encoded = json_encode($value, JSON_UNESCAPED_UNICODE);
                return is_string($encoded) ? trim($encoded, '"') : (string) $value;
            }

            if ($isFormUrlEncoded) {
                return urlencode((string) $value);
            }

            return (string) $value;
        }, $template);
    }
}
