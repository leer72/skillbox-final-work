<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Carbon\Carbon;

class AgoExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('ago', [$this, 'getDiff']),
        ];
    }

    public function getDiff($value)
    {
        return Carbon::make($value)->locale('ru')->diffForHumans();
    }
}
