<?php

declare(strict_types=1);

/*
 * @author  Moritz Vondano
 * @license MIT
 */

namespace Mvo\ContaoTwig\Tests\DependencyInjection;

use Mvo\ContaoTwig\DependencyInjection\MvoContaoTwigExtension;
use Mvo\ContaoTwig\EventListener\RenderingForwarder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MvoContaoTwigExtensionTest extends TestCase
{
    public function testLoad(): void
    {
        $containerBuilder = new ContainerBuilder();

        $extension = new MvoContaoTwigExtension();
        $extension->load([], $containerBuilder);

        $this->assertTrue(
            $containerBuilder->hasDefinition(RenderingForwarder::class)
        );
    }
}
