<?php

namespace App\Services\SocialPublishers;

class PublishResult
{
    public function __construct(
        public bool $success,
        public ?string $platformPostId = null,
        public array $response = [],
        public ?string $error = null,
    ) {
    }

    public static function success(?string $platformPostId = null, array $response = []): self
    {
        return new self(true, $platformPostId, $response);
    }

    public static function failure(string $error, array $response = []): self
    {
        return new self(false, null, $response, $error);
    }
}
