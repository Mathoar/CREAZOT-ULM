<?php

namespace App\Factory\Prepayment;

use App\Entity\Client;

interface PrepaymentFactoryInterface
{
    public function createPrepaymentFromPayload(array $payload, ?Client $client = null): array;
}
