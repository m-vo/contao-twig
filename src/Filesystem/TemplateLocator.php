<?php

declare(strict_types=1);

/*
 * @author  Moritz Vondano
 * @license MIT
 */

namespace Mvo\ContaoTwig\Filesystem;

use Symfony\Component\Finder\Finder;
use Webmozart\PathUtil\Path;

class TemplateLocator
{
    private string $templateDir;

    public function __construct(string $templateDir)
    {
        $this->templateDir = $templateDir;
    }

    public function getTwigTemplatePaths(): array
    {
        $templateFiles = (new Finder())
            ->in($this->templateDir)
            ->name('*.twig')
            ->getIterator();

        return array_map(
            static fn ($file) => $file->getPathname(),
            iterator_to_array($templateFiles)
        );
    }

    public function getRelativeTemplatePath(string $path): string
    {
        return Path::makeRelative($path, $this->templateDir);
    }
}
