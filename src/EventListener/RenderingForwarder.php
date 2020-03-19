<?php

declare(strict_types=1);

/*
 * @author  Moritz Vondano
 * @license MIT
 */

namespace Mvo\ContaoTwig\EventListener;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\Template;
use Contao\TemplateLoader;
use Twig\Environment;
use Webmozart\PathUtil\Path;

class RenderingForwarder
{
    private Environment $twig;
    private ContaoFramework $framework;
    private string $rootDir;
    private string $templateDir;

    /** @var string[] */
    private array $templatePaths;

    /** @var array<string, string> */
    private array $templates = [];

    /** @var array<string, array> */
    private array $templateContext = [];

    public function __construct(Environment $twig, ContaoFramework $framework, string $rootDir, string $templateDir)
    {
        $this->twig = $twig;
        $this->framework = $framework;
        $this->rootDir = $rootDir;
        $this->templateDir = $templateDir;
    }

    public function setTemplatePaths(array $templatePaths): void
    {
        $this->templatePaths = $templatePaths;
    }

    /**
     * @Hook("initializeSystem")
     */
    public function registerTemplates(): void
    {
        foreach ($this->templatePaths as $templatePath) {
            $identifier = Path::getFilenameWithoutExtension($templatePath, '.html.twig');

            // add template to the TemplateLoader so that they show up in the backend
            $directory = Path::getDirectory($templatePath);

            /** @var TemplateLoader $templateLoader */
            $templateLoader = $this->framework->getAdapter(TemplateLoader::class);

            /* @noinspection StaticInvocationViaThisInspection */
            $templateLoader->addFile($identifier, Path::makeRelative($directory, $this->rootDir));

            // keep track of the relative path (inside the template path)
            $this->templates[$identifier] = Path::makeRelative($templatePath, $this->templateDir);
        }
    }

    /**
     * @Hook("parseTemplate")
     */
    public function storeTemplateContext(Template $template): void
    {
        $this->templateContext[$template->getName()] = $template->getData();
    }

    /**
     * @Hook("parseFrontendTemplate")
     */
    public function forwardRendering(string $buffer, string $identifier): string
    {
        $context = $this->templateContext[$identifier] ?? null;
        $template = $this->templates[$identifier] ?? null;

        if (null === $context || null === $template) {
            return $buffer;
        }

        if (!$this->twig->getLoader()->exists($template)) {
            throw new \RuntimeException("Template '$identifier' ($template) wasn't loaded.");
        }

        // drop the current buffer and forward the rendering to twig instead
        return $this->twig->render($template, $context);
    }
}
