<?php

declare(strict_types=1);

/*
 * @author  Moritz Vondano
 * @license MIT
 */

namespace Mvo\ContaoTwig\DependencyInjection\Compiler;

use Mvo\ContaoTwig\EventListener\RenderingForwarder;
use Mvo\ContaoTwig\Filesystem\TemplateLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class LocateTwigTemplatesPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(RenderingForwarder::class)) {
            return;
        }

        $definition = $container->getDefinition(RenderingForwarder::class);
        $templateDir = $container->getParameter('twig.default_path');

        if (!\is_string($templateDir)) {
            return;
        }

        $locator = new TemplateLocator($templateDir);
        $templatePaths = $locator->getTwigTemplatePaths();

        $definition->addMethodCall('setTemplatePaths', [$templatePaths]);
    }
}
