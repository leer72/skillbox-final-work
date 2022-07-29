<?php

namespace App\Service;

use DateInterval;

class PlusSubscription implements BlaBlaArticleSubscriptionInterface
{
    private static $level = 2;

    private static $name = 'Plus';

    private static $perHourLimit = 2;

    private static $avalibleModules = false;

    private static $avalibleWords = true;

    private static $avalibleKeywordMorphs = true;

    private static $avalibleImages = true;

    private $duration;

    public function __construct()
    {
        $this->duration = \DateInterval::createFromDateString('1 week');
    }

    public function getLevel(): int
    {
        return self::$level;
    }

    public function getName(): string
    {
        return self::$name;
    }

    public function getArticlePerHourLimit(): int
    {
        return self::$perHourLimit;
    }

    public function getAvalibleModules(): bool
    {
        return self::$avalibleModules;
    }

    public function getDuration(): DateInterval
    {
        return $this->duration;
    }

    public function getAvalibleWords(): bool
    {
        return self::$avalibleWords;
    }

    public function getAvalibleKeywordMorphs(): bool
    {
        return self::$avalibleKeywordMorphs;
    }

    public function getAvalibleImages(): bool
    {
        return self::$avalibleImages;
    }
}
