<?php

declare(strict_types=1);

/*
 * @author  Moritz Vondano
 * @license MIT
 */

namespace Mvo\ContaoTwig\Tests\Twig;

use Mvo\ContaoTwig\Tests\TestCase;
use Mvo\ContaoTwig\Twig\ContaoTemplateExtension;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class ContaoTemplateExtensionTest extends TestCase
{
    public function testFnFunction(): void
    {
        $environment = $this->getEnvironmentWithExtension();

        $context = [
            'some_closure' => static fn () => 'some <b>html</b>',
        ];

        $result = $environment->render('foo.html.twig', $context);

        $this->assertSame('some <b>html</b>', $result);
    }

    public function getEnvironmentWithExtension(): Environment
    {
        $loader = new FilesystemLoader($this->templateDir, $this->rootDir);
        $environment = new Environment($loader);

        $environment->addExtension(new ContaoTemplateExtension());

        return $environment;
    }
}
