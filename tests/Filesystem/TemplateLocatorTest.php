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
}
