<?php

namespace App\Service\Export;

use App\Entity\Cadeau;
use App\Entity\Payment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class PaymentExportFilter implements ExportFilterInterface
{
    public function __construct(private EntityManagerInterface $em) {}

    public function supports(string $entityClass): bool
    {
        return $entityClass === Payment::class;
    }

    public function getResults(Request $request): array
    {
        $params = $request->query->all();
        $qb = $this->em->getRepository(Payment::class)
                        ->createQueryBuilder('p')
                        ->leftJoin('p.details', 'details')
                        ->leftJoin('details.prepayment', 'prepayment');

        foreach ($params as $key => $value) {
            if (empty($value) && $value !== '0') continue;

            switch ($key) {
                case 'intitule':
                    $qb->andWhere('LOWER(p.name) LIKE :intitule')
                        ->setParameter('intitule', '%' . strtolower($value) . '%');
                    break;
                case 'mode':
                case 'details_mode':
                    $qb->andWhere('LOWER(details.mode) LIKE :mode')
                        ->setParameter('mode', '%' . strtolower($value) . '%');
                    break;
                case 'date':
                    if (!empty($value['after'])) {
                        $after = \DateTimeImmutable::createFromFormat('d/m/Y', $value['after']) ?: new \DateTimeImmutable($value['after']);
                        $qb->andWhere('p.date >= :after')->setParameter('after', $after);
                    }
                    if (!empty($value['before'])) {
                        $before = \DateTimeImmutable::createFromFormat('d/m/Y', $value['before']) ?: new \DateTimeImmutable($value['before']);
                        $qb->andWhere('p.date <= :before')->setParameter('before', $before);
                    }
                    break;
            }
        }

        return $qb->getQuery()->getResult();
    }

    public function formatExport(array $results, string $format = 'csv'): array
    {
        $headers = ['Id', 'Date', 'Référence', 'Nom', 'Code de réservation', 'Origine', 'Mode', 'Montant TTC', 'Taux TVA', 'Montant HT', 'Montant TVA', 'Prépaiement'];

        $rows = [];

        foreach ($results as $payment) {
            $details = $payment->getDetails();
            $first = true;

            foreach ($details as $detail) {
                $amount = $detail->getAmount() ?? 0;
                $tauxTva = $detail->getTauxTva();
                $amountHT = $detail->getAmountHT() ?? $amount;
                $montantTva = $detail->getMontantTva() ?? 0;

                $rows[] = [
                    $first ? $payment->getId() : '',
                    $first ? $payment->getDate()?->format('Y-m-d H:i') : '',
                    $first ? ($payment->getReference() ?? '') : '',
                    $first ? ($payment->getName() ?? '') : '',
                    $first ? ($payment->getReservationCode() ?? '') : '',
                    $first ? (count($payment->getOrigine()) > 0
                        ? implode(', ', $payment->getOrigine()
                            ->filter(fn($o) => ($o->getDiscount() > 0) || $o->getHasCommission())
                            ->map(fn($o) => $o->getName())
                            ->toArray()
                        ) : ''
                    ) : '',
                    $this->getPaymentDetailName($detail->getMode()) ?? '',
                    number_format($amount, 2, ',', '') . ' €',
                    $tauxTva !== null ? number_format($tauxTva * 100, 1, ',', '') . ' %' : '',
                    number_format($amountHT, 2, ',', '') . ' €',
                    $montantTva > 0 ? number_format($montantTva, 2, ',', '') . ' €' : '',
                    $this->getPrepaymentInformations($detail->getPrepayment()),
                ];

                $first = false;
            }
        }

        return [$headers, $rows];
    }

     private function getPaymentDetailName(string $code): string 
    {
        $payments = [
            'cb'       => 'CB',
            'especes'  => 'Espèces',
            'web'      => 'Site Web',
            'virement' => 'Virement',
            'cheque'   => 'Chèque'
        ];

        return $payments[$code] ?? '';
    }

    private function getPrepaymentInformations(?Cadeau $cadeau): string 
    {
        if (\is_null($cadeau)) return '';

        $paymentId = $cadeau->getPaymentId() ?? '';
        $offreur = $cadeau->getOffreur() ?? '';
        $date = $cadeau->getDate()?->format('d/m/Y') ?? '';

        return "Prépaiement N°$paymentId" . (\strlen($date) > 0 ? " du $date" : '') . (\strlen($offreur) > 0 ? " - $offreur" : '');
    }
}
