<?php

declare(strict_types=1);

/*
 * @author  Moritz Vondano
 * @license MIT
 */

namespace Mvo\ContaoTwig\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ContaoTemplateExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            // allow executing closures (like they appear in the Contao templates)
            new TwigFunction('fn', static fn ($closure) => $closure(), ['isSafe' => 'html']),
        ];
    }
}
