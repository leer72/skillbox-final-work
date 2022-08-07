<?php

namespace App\DTO;

use App\Entity\User;
use App\Service\ThemeInterface;

class ArticleDTO
{
    public ?string $title;

    public User $author;

    public ?array $keyword;

    public ?array $words;

    public ?int $sizeFrom;

    public ?int $sizeTo;

    public ?ThemeInterface $theme;

    public function __construct($args)
    {
        $this->title = $args['title'] ?? null;
        $this->author = $args['author'];
        $this->keyword = $args['keyword'] ?? null;
        $this->words = $args['words'] ?? null;
        $this->sizeFrom = $args['sizeFrom'] ?? null;
        $this->sizeTo = $args['sizeTo'] ?? null;
        $this->theme = $args['theme'] ?? null;
    }
}
