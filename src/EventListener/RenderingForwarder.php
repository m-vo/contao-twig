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
use Mvo\ContaoTwig\Filesystem\TemplateLocator;
use Twig\Environment;
use Webmozart\PathUtil\Path;

class RenderingForwarder
{
    private const TWIG_TEMPLATE = 'twig_template';
    private const TEMPLATE_CONTEXT = 'context';

    private Environment $twig;
    private TemplateLocator $templateLocator;
    private ContaoFramework $framework;
    private string $rootDir;
    private string $environment;

    /** @var string[] */
    private array $templatePaths;

    /** @var array<string, string> */
    private array $templates = [];

    public function __construct(Environment $twig, TemplateLocator $templateLocator, ContaoFramework $framework, string $rootDir, string $environment)
    {
        $this->twig = $twig;
        $this->templateLocator = $templateLocator;
        $this->framework = $framework;
        $this->rootDir = $rootDir;
        $this->environment = $environment;
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
        if ('dev' === $this->environment) {
            $this->templatePaths = $this->templateLocator->getTwigTemplatePaths();
        }

        foreach ($this->templatePaths as $templatePath) {
            $identifier = Path::getFilenameWithoutExtension($templatePath, '.html.twig');

            // add template to the TemplateLoader so that they show up in the backend
            $directory = Path::getDirectory($templatePath);

            /** @var TemplateLoader $templateLoader */
            $templateLoader = $this->framework->getAdapter(TemplateLoader::class);

            /* @noinspection StaticInvocationViaThisInspection */
            $templateLoader->addFile($identifier, Path::makeRelative($directory, $this->rootDir));

            // keep track of the relative path (inside the template path)
            $this->templates[$identifier] = $this->templateLocator->getRelativeTemplatePath($templatePath);
        }
    }

    /**
     * @Hook("parseTemplate", priority=-128)
     */
    public function delegateRendering(Template $contaoTemplate): void
    {
        $template = $this->templates[$contaoTemplate->getName()] ?? null;

        if (null === $template) {
            return;
        }

        // delegate to our proxy template that will call render()
        $contaoTemplate->setName('twig_template_proxy');

        $contaoTemplate->setData([
            self::TWIG_TEMPLATE => $template,
            self::TEMPLATE_CONTEXT => $contaoTemplate->getData(),
        ]);
    }

    public function render(Template $contaoTemplate): string
    {
        $data = $contaoTemplate->getData();

        $template = $data[self::TWIG_TEMPLATE] ?? null;
        $context = $data[self::TEMPLATE_CONTEXT] ?? null;

        if (null === $template) {
            throw new \InvalidArgumentException("The template's context must contain a value for '".self::TWIG_TEMPLATE."'");
        }

        if (!$this->twig->getLoader()->exists($template)) {
            throw new \RuntimeException("Template '$template' wasn't loaded.");
        }

        return $this->twig->render($template, $context);
    }
}
