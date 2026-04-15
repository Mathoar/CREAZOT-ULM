<?php

declare(strict_types=1);

namespace App\Service\Integration;

use App\Entity\Aeronef;
use App\Entity\Client;
use App\Entity\IntegrationPattern;
use App\Entity\IntegrationVariable;
use App\Entity\SiteSettings;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class VariableResolver
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly PropertyAccessorInterface $propertyAccessor,
    ) {}

    /**
     * @param array<string, string> $context Variables dynamiques passées par le controller
     * @return array<string, string> Resolved variables keyed by variableName
     * @throws \RuntimeException if a required variable cannot be resolved
     */
    public function resolve(IntegrationPattern $pattern, Client $client, ?Aeronef $aeronef = null, array $context = []): array
    {
        $resolved = [];

        foreach ($pattern->getVariables() as $variable) {
            $value = $this->resolveVariable($variable, $client, $aeronef, $context);

            if ($value === null && $variable->isRequired()) {
                throw new \RuntimeException(sprintf(
                    'Variable "%s" is required but could not be resolved (source: %s.%s)',
                    $variable->getVariableName(),
                    $variable->getSource(),
                    $variable->getSourceField()
                ));
            }

            $resolved[$variable->getVariableName()] = $value ?? '';
        }

        return $resolved;
    }

    private function resolveVariable(IntegrationVariable $variable, Client $client, ?Aeronef $aeronef, array $context): ?string
    {
        if ($variable->getSource() === 'context') {
            $value = $context[$variable->getSourceField()] ?? $context[$variable->getVariableName()] ?? null;
            return $value !== null ? (string) $value : $variable->getDefaultValue();
        }

        $entity = $this->getSourceEntity($variable->getSource(), $client, $aeronef);

        if ($entity === null) {
            return $variable->getDefaultValue();
        }

        try {
            $value = $this->propertyAccessor->getValue($entity, $variable->getSourceField());
        } catch (\Exception) {
            $value = null;
        }

        if ($value === null || $value === '') {
            return $variable->getDefaultValue();
        }

        return (string) $value;
    }

    private function getSourceEntity(string $source, Client $client, ?Aeronef $aeronef): ?object
    {
        return match ($source) {
            'client' => $client,
            'aeronef' => $aeronef,
            'site_settings' => $this->em->getRepository(SiteSettings::class)->findOneBy([]),
            'static' => null,
            default => null,
        };
    }
}
