<?php

declare(strict_types=1);

namespace Tests\Api;

use Codeception\Util\HttpCode;
use Mikusek\PhpApi\Client\MediaBuyerClient;
use Tests\Support\ApiTester;

class GetMediaBuyersCest
{
    private MediaBuyerClient $client;

    public function _before(ApiTester $I): void
    {
        $this->client = new MediaBuyerClient($I);
    }

    public function shouldReturnAllMediaBuyersSuccessfully(ApiTester $I): void
    {
        $this->client->getAll();

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeJsonContentType();

        $I->seeResponseIsValidOnJsonSchema(
            codecept_root_dir('tests/schemas/get-media-buyers-schema.json')
        );
    }

    public function shouldReturnArrayWithUniqueIds(ApiTester $I): void
    {
        $this->client->getAll();

        $I->seeResponseCodeIs(HttpCode::OK);

        $response = $this->client->grabResponse();

        $I->assertIsArray($response['data']);

        $ids = array_column($response['data'], 'id');

        foreach ($ids as $id) {
            $I->assertIsInt($id);
            $I->assertGreaterThan(0, $id);
        }

        $I->assertCount(
            count(array_unique($ids)),
            $ids
        );
    }

    public function shouldReturnActiveAsZeroOrOne(ApiTester $I): void
    {
        $this->client->getAll();

        $I->seeResponseCodeIs(HttpCode::OK);

        $response = $this->client->grabResponse();

        foreach ($response['data'] as $mediaBuyer) {
            $I->assertContains(
                $mediaBuyer['active'],
                [0, 1]
            );
        }
    }
}