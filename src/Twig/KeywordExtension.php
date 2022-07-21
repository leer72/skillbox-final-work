<?php

namespace App\Twig;

use Twig\TwigFilter;
use App\Entity\Keyword;
use Twig\Extension\AbstractExtension;

class KeywordExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('morph', [$this, 'getKeywordMorph']),
        ];
    }

    public function getKeywordMorph(Keyword $keyword, int $morph)
    {
        return $keyword->getKeywordForm($morph);
    }
}
