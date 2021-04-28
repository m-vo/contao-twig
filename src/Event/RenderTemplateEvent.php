<?php

declare(strict_types=1);

/*
 * @author  Moritz Vondano
 * @license MIT
 */

namespace Mvo\ContaoTwig\Event;

use Contao\Template;

class RenderTemplateEvent
{
    private Template $contaoTemplate;

    private string $template;

    private array $context;

    public function __construct(Template $contaoTemplate, string $template, array $context)
    {
        $this->contaoTemplate = $contaoTemplate;
        $this->template = $template;
        $this->context = $context;
    }

    public function getContaoTemplate(): Template
    {
        return $this->contaoTemplate;
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function setTemplate(string $template): void
    {
        $this->template = $template;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function setContext(array $context): void
    {
        $this->context = $context;
    }
}
