<?php

namespace App\Service\Export;

use App\Entity\Expense;
use App\Service\Export\ExportUtils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class ExpenseExportFilter implements ExportFilterInterface
{
    public function __construct(private EntityManagerInterface $em, private ExportUtils $exportUtils) {}

    public function supports(string $entityClass): bool
    {
        return $entityClass === Expense::class;
    }

    public function getResults(Request $request): array
    {
        $params = $request->query->all();
        $qb = $this->em->getRepository(Expense::class)
                        ->createQueryBuilder('p')
                        ->leftJoin('p.details', 'details');

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
        $headers = ['Id', 'Date', 'Bénéficiaire', 'Libellé', 'Mode', 'Montant TTC', 'Taux TVA', 'Montant HT', 'Montant TVA', 'Total TTC', 'Total HT', 'Justificatif'];

        $rows = [];

        foreach ($results as $expense) {
            $details = $expense->getDetails();
            $first = true;

            foreach ($details as $detail) {
                $amount = $detail->getAmount() ?? 0;
                $tauxTva = $detail->getTauxTva();
                $amountHT = $detail->getAmountHT() ?? $amount;
                $montantTva = $detail->getMontantTva() ?? 0;

                $rows[] = [
                    $first ? $expense->getId() : '',
                    $first ? $expense->getDate()?->format('Y-m-d H:i') : '',
                    $first ? ($expense->getBeneficiaire() ?? '') : '',
                    $first ? ($expense->getLibelle() ?? '') : '',
                    $this->getExpenseDetailName($detail->getMode()) ?? '',
                    number_format($amount, 2, ',', '') . ' €',
                    $tauxTva !== null ? number_format($tauxTva * 100, 1, ',', '') . ' %' : '',
                    number_format($amountHT, 2, ',', '') . ' €',
                    $montantTva > 0 ? number_format($montantTva, 2, ',', '') . ' €' : '',
                    $first ? number_format($expense->getTotalTTC() ?? 0, 2, ',', '') . ' €' : '',
                    $first ? number_format($expense->getTotalHT() ?? 0, 2, ',', '') . ' €' : '',
                    $first ? $this->exportUtils->makeLink($expense->getDocument(), null, $format) ?? '' : '',
                ];

                $first = false;
            }
        }

        return [$headers, $rows];
    }

     private function getExpenseDetailName(string $code): string 
    {
        $expenses = [
            'cb'       => 'CB',
            'especes'  => 'Espèces',
            'web'      => 'Site Web',
            'virement' => 'Virement',
            'cheque'   => 'Chèque'
        ];

        return $expenses[$code] ?? '';
    }
}
