<?php

namespace App\Service;

use App\Entity\Keyword;

interface ThemeInterface
{
    public function getParagraphs(Keyword $value);

    public function getTitle(Keyword $value);

    public function getSlug();

    public function getName();
}