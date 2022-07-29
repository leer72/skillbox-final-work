<?php

namespace App\Service;

use DateInterval;

interface BlaBlaArticleSubscriptionInterface
{
    public function getLevel(): int;

    public function getName(): string;
    
    public function getArticlePerHourLimit(): int;

    public function getAvalibleModules(): bool;

    public function getAvalibleWords(): bool;

    public function getAvalibleKeywordMorphs(): bool;

    public function getAvalibleImages(): bool;

    public function getDuration(): DateInterval;
}
