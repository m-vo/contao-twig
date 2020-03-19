<?php

declare(strict_types=1);

/*
 * @author  Moritz Vondano
 * @license MIT
 */

namespace Mvo\ContaoTwig\Tests\Filesystem;

use Mvo\ContaoTwig\Filesystem\TemplateLocator;
use Mvo\ContaoTwig\Tests\TestCase;

class TemplateLocatorTest extends TestCase
{
    public function testGetTwigTemplatePaths(): void
    {
        $locator = new TemplateLocator($this->templateDir);

        $expected = [
            $this->templateDir.'/foo.html.twig',
            $this->templateDir.'/sub/bar.html.twig',
        ];

        $paths = $locator->getTwigTemplatePaths();

        sort($expected);
        sort($paths);

        $this->assertSame($expected, $paths);
    }

    /**
     * @testWith ["/path/to/project/templates/foo.html.twig", "foo.html.twig"]
     *           ["/path/to/project/templates/sub/bar.html.twig", "sub/bar.html.twig"]
     */
    public function testGetRelativeTemplatePath(string $inputPath, string $outputPath): void
    {
        $locator = new TemplateLocator('/path/to/project/templates');

        $this->assertSame($outputPath, $locator->getRelativeTemplatePath($inputPath));
    }
}
