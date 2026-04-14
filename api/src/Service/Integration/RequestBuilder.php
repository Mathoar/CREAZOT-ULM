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
            $body = $this->interpolate($pattern->getBodyTemplate(), $variables);
            $contentType = $pattern->getContentType() ?? 'application/json';

            $options['headers']['Content-Type'] = $contentType;

            if (str_contains($contentType, 'json')) {
                $options['body'] = $body;
            } else {
                $options['body'] = $body;
            }
        }

        return [
            'method' => $pattern->getMethod(),
            'url' => $url,
            'options' => $options,
        ];
    }

    private function interpolate(string $template, array $variables): string
    {
        return preg_replace_callback('/\{\{(\w+)\}\}/', function (array $matches) use ($variables) {
            return $variables[$matches[1]] ?? $matches[0];
        }, $template);
    }
}
