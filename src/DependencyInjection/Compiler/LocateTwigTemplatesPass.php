<?php

declare(strict_types=1);

/*
 * @author  Moritz Vondano
 * @license MIT
 */

namespace Mvo\ContaoTwig\DependencyInjection\Compiler;

use Mvo\ContaoTwig\EventListener\RenderingForwarder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Finder\Finder;

class LocateTwigTemplatesPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(RenderingForwarder::class)) {
            return;
        }

        $definition = $container->getDefinition(RenderingForwarder::class);
        $templateDir = $container->getParameter('twig.default_path');

        $templateFiles = (new Finder())
            ->in($templateDir)
            ->name('*.twig')
            ->getIterator();

        $templatePaths = array_map(
            static fn ($file) => $file->getPathname(),
            iterator_to_array($templateFiles)
        );

        $definition->addMethodCall('setTemplatePaths', [$templatePaths]);
    }
}
