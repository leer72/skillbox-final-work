<?php

namespace App\Twig;

use Twig\Error\LoaderError;
use Twig\Loader\LoaderInterface;
use Twig\Source;

class StringLoader implements LoaderInterface
{
    public function getSourceContext($name): Source
    {
        if (! $this->exists($name)) {
            throw new LoaderError(sprintf('Template "%s" is not defined.', $name));
        }

        return new Source($name, $name);
    }

    public function getCacheKey($name): string
    {
        if (! $this->exists($name)) {
            throw new LoaderError(sprintf('Template "%s" is not defined.', $name));
        }

        return md5($name);
    }

    public function isFresh($name, $time): bool
    {
        if (! $this->exists($name)) {
            throw new LoaderError(sprintf('Template "%s" is not defined.', $name));
        }

        return true;
    }

    public function exists($name): bool
    {
        return (bool) preg_match('/\s/', $name);
    }
}
