<?php

namespace App\Factory\Prepayment;

use App\Entity\Cadeau;
use App\Entity\Client;
use App\Entity\Combinaison;
use App\Entity\Circuit;
use App\Entity\Origine;
use App\Repository\CircuitRepository;
use App\Repository\CombinaisonRepository;
use App\Repository\OrigineRepository;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;

class WixPrepaymentFactory implements PrepaymentFactoryInterface
{
    public function __construct(
        private CircuitRepository $circuitRepository,
        private CombinaisonRepository $combinaisonRepository,
        private OrigineRepository $origineRepository,
        private LoggerInterface $logger,
    ) {}

    public function createPrepaymentFromPayload(array $payload, ?Client $client = null): array
    {
        $prepayments = [];
        if (empty($payload['lineItems'])) {
            $this->logger->warning('Wix payload sans lineItems', ['payload' => $payload]);
            return $prepayments;
        }

        $code = $this->generateCode();
        $creationDate = $this->getDate($payload['_dateCreated'] ?? null, 'now');
        $startValidity = \DateTime::createFromImmutable($creationDate);
        $endValidity = $this->getEndValidity($creationDate);
        $paymentId = $this->getString($payload['number'] ?? null, '');
        $offreur = $this->getIdentity($payload['buyerInfo'] ?? []);
        $beneficiaire = $this->getIdentity($payload['shippingInfo']['shipmentDetails'] ?? []);
        $email = $this->getString($payload['buyerInfo']['email'] ?? null, '');
        $telephone = $this->getString($payload['shippingInfo']['shipmentDetails']['phone'] ?? null, '');
        $isGift = !$this->isSamePerson($offreur, $beneficiaire);
        $origine = $this->getOrigine($this->getString($payload['channelInfo']['type'] ?? null, 'web'), $client);

        foreach ($payload['lineItems'] as $item) {
            $prepayment = new Cadeau();
            $webshopId = $this->getString($item['productId'] ?? null, '');
            $quantity = $this->getInt($item['quantity'] ?? 1, 1);

            $circuit = $this->findCircuit($webshopId, $client);
            $options = $this->getOptions($item, $quantity, $client);
            $prixTotal = $this->getFloat($item['totalPrice'] ?? null, $this->getDefaultPrice($circuit, $options, $quantity));

            if ($client) {
                $prepayment->setClient($client);
            }

            $prepayment->setQuantite($quantity)
                ->setDate($startValidity)
                ->setFin($endValidity)
                ->setCode($code)
                ->setPaymentId($paymentId)
                ->setOffreur($offreur)
                ->setBeneficiaire($beneficiaire)
                ->setEmail($email)
                ->setTelephone($telephone)
                ->setGift($isGift)
                ->setSendEmail(false)
                ->setUsed(false)
                ->setCircuit($circuit)
                ->setOptions($options)
                ->addOrigine($origine)
                ->setPrix($prixTotal);

            $this->logger->info('Wix: Cadeau créé', [
                'code' => $code,
                'webshopId' => $webshopId,
                'circuit' => $circuit?->getNom(),
                'options' => $options?->getNom(),
                'clientId' => $client?->getId(),
                'prix' => $prixTotal,
            ]);

            $prepayments[] = $prepayment;
        }

        return $prepayments;
    }

    private function findCircuit(string $webshopId, ?Client $client): ?Circuit
    {
        $criteria = ['webshopId' => $webshopId];
        if ($client) {
            $criteria['client'] = $client;
        }
        $circuit = $this->circuitRepository->findOneBy($criteria);

        if (!$circuit && $client) {
            $circuit = $this->findGiftCircuit($client);
            $this->logger->info('Wix: fallback circuit bon cadeau', [
                'webshopId' => $webshopId,
                'circuitTrouvé' => $circuit?->getNom(),
            ]);
        }

        if (!$circuit) {
            $this->logger->warning('Wix: aucun circuit trouvé', [
                'webshopId' => $webshopId,
                'clientId' => $client?->getId(),
            ]);
        }

        return $circuit;
    }

    /**
     * Cherche un circuit "bon cadeau" / "gift" pour le client
     */
    private function findGiftCircuit(?Client $client): ?Circuit
    {
        if (!$client) {
            return null;
        }

        $circuits = $this->circuitRepository->findBy(['client' => $client]);
        $keywords = ['cadeau', 'gift', 'bon cadeau', 'bon-cadeau', 'voucher'];

        foreach ($circuits as $circuit) {
            $nom = mb_strtolower($circuit->getNom() ?? '');
            foreach ($keywords as $keyword) {
                if (str_contains($nom, $keyword)) {
                    return $circuit;
                }
            }
        }

        return null;
    }

    private function generateCode(): string
    {
        return substr(base_convert(time(), 10, 36), -6) . substr(base_convert(mt_rand(), 10, 36), 0, 6);
    }

    private function getOptions(array $item, int $quantity, ?Client $client): ?Combinaison
    {
        $noOptions = ['aucune', 'sans option'];
        if (empty($item['options'])) return null;

        foreach ($item['options'] as $option) {
            $selection = trim(strtolower($option['selection'] ?? ''));
            if (!in_array($selection, $noOptions, true)) {
                return $this->findCombinaison($option['selection'], $quantity, $client);
            }
        }

        return null;
    }

    /**
     * Recherche la combinaison par nom, filtrée par client.
     * Tente d'abord le nom Wix tel quel, puis un mapping connu.
     */
    private function findCombinaison(string $wixOption, int $quantity, ?Client $client): ?Combinaison
    {
        $criteria = $client ? ['client' => $client] : [];

        $combinaison = $this->combinaisonRepository->findOneBy(array_merge($criteria, ['nom' => $wixOption]));
        if ($combinaison) {
            return $combinaison;
        }

        $mappedName = $this->mapWixOptionToSite($wixOption, $quantity);
        $combinaison = $this->combinaisonRepository->findOneBy(array_merge($criteria, ['nom' => $mappedName]));

        if (!$combinaison) {
            $this->logger->warning('Wix: combinaison introuvable', [
                'wixOption' => $wixOption,
                'mappedName' => $mappedName,
                'clientId' => $client?->getId(),
            ]);
        }

        return $combinaison;
    }

    private function mapWixOptionToSite(string $wixOption, int $quantity): string
    {
        return $quantity >= 2 ? '2 Portes Photos' : 'Porte Photos';
    }

    private function getDefaultPrice(?Circuit $circuit, ?Combinaison $options, int $quantity): float
    {
        $circuitPrice = $circuit ? $this->getFloat($circuit->getPrix(), 0) : 0;
        $optionsPrice = $options ? $this->getFloat($options->getPrix(), 0) : 0;
        return $quantity * ($circuitPrice + $optionsPrice);
    }

    private function getOrigine(string $origine, ?Client $client = null): ?Origine
    {
        return $this->origineRepository->findOneByNameInsensitive($origine, $client);
    }

    private function getEndValidity(DateTimeImmutable $date): \DateTime
    {
        return \DateTime::createFromImmutable($date->modify('+1 year +1 day'));
    }

    private function isSamePerson(string $buyer, string $receiver): bool
    {
        return strtolower($buyer) === strtolower($receiver);
    }

    private function getIdentity(array $person): string
    {
        $firstName = $this->getString($person['firstName'] ?? null, '');
        $lastName = $this->getString($person['lastName'] ?? null, '');
        return trim($firstName . ' ' . strtoupper($lastName));
    }

    private function getDate($value, string $default): DateTimeImmutable
    {
        return $value ? new DateTimeImmutable($value) : new DateTimeImmutable($default);
    }

    private function getInt($value, int $default): int
    {
        return $value !== null ? intval($value) : $default;
    }

    private function getFloat($value, float $default): float
    {
        return $value !== null ? floatval($value) : $default;
    }

    private function getString($value, string $default): string
    {
        return $value !== null ? strval($value) : $default;
    }
}
