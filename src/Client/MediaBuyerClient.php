<?php

declare(strict_types=1);

namespace Mikusek\PhpApi\Client;

use Tests\Support\ApiTester;

class MediaBuyerClient
{
    public function __construct(
        private ApiTester $I
    ) {
    }

    public function create(array $payload): void
    {
        $this->I->sendPost('/mediabuyers', $payload);
    }

    public function getAll(): void
    {
        $this->I->sendGet('/mediabuyers');
    }

    public function grabResponse(): array
    {
        /** @var array $response */
        $response = json_decode($this->I->grabResponse(), true);

        return $response;
    }
}