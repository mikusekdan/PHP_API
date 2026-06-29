<?php

declare(strict_types=1);

namespace Mikusek\PhpApi\Builder;

class MediaBuyerBuilder
{
    private array $data;

    private function __construct()
    {
        $this->data = [
            'mbId'        => (string) random_int(100000000, 999999999),
            'initials'    => 'TM',
            'name'        => 'Test Media Buyer',
            'email'       => 'test-' . uniqid() . '@example.com',
            'slackUserId' => 'U05AZ3DQBBKK',
            'active'      => true,
        ];
    }

    public static function create(): self
    {
        return new self();
    }

    public function withEmail(string $email): self
    {
        $this->data['email'] = $email;

        return $this;
    }

    public function withName(string $name): self
    {
        $this->data['name'] = $name;

        return $this;
    }

    public function withoutName(): self
    {
        unset($this->data['name']);

        return $this;
    }

    public function build(): array
    {
        return $this->data;
    }

    public function withActive(mixed $active): self
    {
        $this->data['active'] = $active;

        return $this;
    }
}