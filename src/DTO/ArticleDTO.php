<?php

namespace App\DTO;

use App\Entity\User;
use App\Service\ThemeInterface;

class ArticleDTO
{
    private ?string $title;

    private User $author;

    private ?array $keyword;

    private ?array $words;

    private ?int $sizeFrom;

    private ?int $sizeTo;

    private ?ThemeInterface $theme;

    public function __construct(
        ?string $title, 
        User $author, 
        ?array $keyword, 
        ?array $words, 
        ?int $sizeFrom, 
        ?int $sizeTo, 
        ?ThemeInterface $theme
    ) {
        $this->title = $title;
        $this->author = $author;
        $this->keyword = $keyword;
        $this->words = $words;
        $this->sizeFrom = $sizeFrom;
        $this->sizeTo = $sizeTo;
        $this->theme = $theme;
    }

    public static function fromArray(array $args)
    {
        return new static(
            $args['title'], 
            $args['author'], 
            $args['keyword'], 
            $args['words'], 
            $args['sizeFrom'], 
            $args['sizeTo'], 
            $args['theme']
        );
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getAuthor()
    {
        return $this->author;
    }

    public function getKeyword()
    {
        return $this->keyword;
    }

    public function getWords()
    {
        return $this->words;
    }

    public function getSizeFrom()
    {
        return $this->sizeFrom;
    }

    public function getSizeTo()
    {
        return $this->sizeTo;
    }

    public function getTheme()
    {
        return $this->theme;
    }
}
