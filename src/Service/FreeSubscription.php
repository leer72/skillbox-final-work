<?php

namespace App\Service;

use DateInterval;

class FreeSubscription implements BlaBlaArticleSubscriptionInterface
{
    private static $level = 1;

    private static $name = 'Free';

    private static $perHourLimit = 2;

    private static $avalibleModules = false;

    private static $avalibleWords = false;

    private static $avalibleKeywordMorphs = false;

    private static $avalibleImages = false;

    private $duration;

    public function __construct()
    {
        $this->duration = \DateInterval::createFromDateString('100 years');
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
