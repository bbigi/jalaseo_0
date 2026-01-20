<?php

namespace App\Services\SocialPublishers;

class SocialPublisherManager
{
    /**
     * @param iterable<SocialPublisherInterface> $publishers
     */
    public function __construct(
        private readonly iterable $publishers = [],
    ) {
    }

    public function resolve(string $provider): ?SocialPublisherInterface
    {
        foreach ($this->publishers as $publisher) {
            if ($publisher->supports($provider)) {
                return $publisher;
            }
        }

        return null;
    }
}
