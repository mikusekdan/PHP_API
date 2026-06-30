<?php

declare(strict_types=1);

namespace Tests\Api;

use Codeception\Util\HttpCode;
use Mikusek\PhpApi\Builder\MediaBuyerBuilder;
use Mikusek\PhpApi\Client\MediaBuyerClient;
use Tests\Support\ApiTester;

class CreateMediaBuyerCest
{
    private MediaBuyerClient $client;

    public function _before(ApiTester $I): void
    {
        $this->client = new MediaBuyerClient($I);
    }

    public function shouldCreateMediaBuyerSuccessfully(ApiTester $I): void
    {
        $payload = MediaBuyerBuilder::create()
            ->build();

        $this->client->create($payload);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeJsonContentType();

        $I->seeResponseIsValidOnJsonSchema(
            codecept_root_dir('tests/schemas/post-media-buyer-schema.json')
        );

        $response = $this->client->grabResponse();

        $I->assertArrayHasKey('id', $response['data']);
        $I->assertIsInt($response['data']['id']);
        $I->assertGreaterThan(0, $response['data']['id']);

        $I->seeResponseContainsJson([
            'data' => [
                'mbId'        => $payload['mbId'],
                'initials'    => $payload['initials'],
                'name'        => $payload['name'],
                'email'       => $payload['email'],
                'slackUserId' => $payload['slackUserId'],
                'active'      => 1,
            ],
        ]);
    }

    public function shouldReturnBadRequestWhenNameIsMissing(ApiTester $I): void
    {
        $payload = MediaBuyerBuilder::create()
            ->withoutName()
            ->build();

        $this->client->create($payload);

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeJsonContentType();

        $I->seeErrorDetail('This field is missing: [name]');
    }

    public function shouldReturnBadRequestWhenEmailIsInvalid(ApiTester $I): void
    {
        $payload = MediaBuyerBuilder::create()
            ->withEmail('not-an-email')
            ->build();

        $this->client->create($payload);

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeJsonContentType();

        $I->seeErrorDetail('The email not-an-email is not a valid email.');
    }

    #[\Codeception\Attribute\Examples(['A'])]
    #[\Codeception\Attribute\Examples(['ABCDEFGHIJKLMNOPQRSTUVWXYZABCDE'])]
    public function shouldReturnBadRequestWhenNameLengthIsInvalid(
        ApiTester $I,
        \Codeception\Example $example
    ): void {
        $payload = MediaBuyerBuilder::create()
            ->withName($example[0])
            ->build();

        $this->client->create($payload);

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeJsonContentType();
    }

    public function shouldReturnConflictWhenMbIdAlreadyExists(ApiTester $I): void
    {
        $payload = MediaBuyerBuilder::create()
            ->build();

        $this->client->create($payload);

        $this->client->create($payload);

        $I->seeResponseCodeIs(HttpCode::CONFLICT);
        $I->seeJsonContentType();
    }
}