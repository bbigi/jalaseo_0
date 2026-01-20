<?php

namespace App\Services\SocialPublishers;

use App\Models\DistributionSchedule;
use App\Models\SocialAccount;
use App\Models\SocialAsset;

interface SocialPublisherInterface
{
    public function supports(string $provider): bool;

    public function publish(SocialAsset $asset, DistributionSchedule $schedule, SocialAccount $account): PublishResult;
}
