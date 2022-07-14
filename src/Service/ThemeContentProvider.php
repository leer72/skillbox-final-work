<?php

namespace App\Service;

class ThemeContentProvider
{
    private $themes = [];

    public function __construct(iterable $themes)
    {
        $this->themes = $themes;
    }

    public function getThemes()
    {
        $themesHandler = [];
        foreach($this->themes as $theme) {
            $themesHandler += [
                $theme->getName() => $theme->getSlug(),
        ];
        }
        return $themesHandler;
    }

    public function findThemeBySlug($slug)
    {
        foreach($this->themes as $theme) {
            if($theme->getSlug() === $slug) {
                
                return $theme;
            }
        }

        return null;
    }
}