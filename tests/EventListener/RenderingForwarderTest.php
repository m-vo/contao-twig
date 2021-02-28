<?php

declare(strict_types=1);

/*
 * @author  Moritz Vondano
 * @license MIT
 */

namespace Mvo\ContaoTwig\Tests\EventListener;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Template;
use Contao\TemplateLoader;
use Mvo\ContaoTwig\EventListener\RenderingForwarder;
use Mvo\ContaoTwig\Filesystem\TemplateLocator;
use Mvo\ContaoTwig\Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Twig\Environment;
use Twig\Loader\LoaderInterface;

class RenderingForwarderTest extends TestCase
{
    public function testRegistersTemplates(): void
    {
        $renderingForwarder = $this->getRenderingForwarder();

        $renderingForwarder->setTemplatePaths($this->getTemplatePaths());
        $renderingForwarder->registerTemplates();
    }

    public function testReloadsTemplatesInDevEnvironment(): void
    {
        /** @var TemplateLocator&MockObject $locator */
        $locator = $this->createMock(TemplateLocator::class);
        $locator
            ->expects($this->once())
            ->method('getTwigTemplatePaths')
            ->willReturn($this->getTemplatePaths());

        $renderingForwarder = $this->getRenderingForwarder(null, null, $locator, 'dev');

        $renderingForwarder->setTemplatePaths($this->getTemplatePaths());
        $renderingForwarder->registerTemplates();
    }

    public function testDoesNotReloadTemplatesInProdEnvironment(): void
    {
        /** @var TemplateLocator&MockObject $locator */
        $locator = $this->createMock(TemplateLocator::class);
        $locator
            ->expects($this->never())
            ->method('getTwigTemplatePaths');

        $renderingForwarder = $this->getRenderingForwarder(null, null, $locator, 'prod');

        $renderingForwarder->setTemplatePaths($this->getTemplatePaths());
        $renderingForwarder->registerTemplates();
    }

    public function testSetsProxyTemplate(): void
    {
        $renderingForwarder = $this->getRenderingForwarder();

        $renderingForwarder->setTemplatePaths($this->getTemplatePaths());
        $renderingForwarder->registerTemplates();

        /** @var Template&MockObject $template */
        $template = $this->createMock(Template::class);
        $template
            ->expects($this->once())
            ->method('getName')
            ->willReturn('bar');
        $template
            ->expects($this->once())
            ->method('setName')
            ->with('twig_template_proxy');
        $template
            ->expects($this->once())
            ->method('getData')
            ->willReturn(['a' => 123]);
        $template
            ->expects($this->once())
            ->method('setData')
            ->with([
                'twig_template' => 'sub/bar.html.twig',
                'context' => ['a' => 123],
                'contao_template' => 'bar',
            ]);

        $renderingForwarder->delegateRendering($template);
    }

    public function testSkipsSettingProxyTemplateForUnregisteredTemplates(): void
    {
        $renderingForwarder = $this->getRenderingForwarder();
        $renderingForwarder->setTemplatePaths($this->getTemplatePaths());
        $renderingForwarder->registerTemplates();

        /** @var Template&MockObject $template */
        $template = $this->createMock(Template::class);
        $template
            ->expects($this->once())
            ->method('getName')
            ->willReturn('text');

        $template
            ->expects($this->never())
            ->method('setName');

        $template
            ->expects($this->never())
            ->method('__set');

        $renderingForwarder->delegateRendering($template);
    }

    public function testRender(): void
    {
        $twigLoader = $this->createMock(LoaderInterface::class);
        $twigLoader
            ->expects($this->once())
            ->method('exists')
            ->with('sub/bar.html.twig')
            ->willReturn(true);

        /** @var Environment&MockObject $twig */
        $twig = $this->createMock(Environment::class);
        $twig
            ->expects($this->once())
            ->method('render')
            ->with('sub/bar.html.twig', ['a' => 123])
            ->willReturn('twig content');
        $twig
            ->expects($this->once())
            ->method('getLoader')
            ->willReturn($twigLoader);

        $renderingForwarder = $this->getRenderingForwarder($twig, $this->mockContaoFramework());

        /** @var Template&MockObject $template */
        $template = $this->createMock(Template::class);
        $template
            ->expects($this->once())
            ->method('getData')
            ->willReturn([
                'twig_template' => 'sub/bar.html.twig',
                'context' => ['a' => 123],
            ]);

        $output = $renderingForwarder->render($template);

        $this->assertSame('twig content', $output);
    }

    public function testRenderThrowsIfTemplateNotSet(): void
    {
        $renderingForwarder = $this->getRenderingForwarder(null, $this->mockContaoFramework());

        /** @var Template&MockObject $template */
        $template = $this->createMock(Template::class);
        $template
            ->expects($this->once())
            ->method('getData')
            ->willReturn(['a' => 123]);

        $this->expectExceptionMessage('The template\'s context must contain a value for \'twig_template\'');

        $renderingForwarder->render($template);
    }

    public function testRenderThrowsIfTemplateWasNotLoaded(): void
    {
        $twigLoader = $this->createMock(LoaderInterface::class);
        $twigLoader
            ->expects($this->once())
            ->method('exists')
            ->with('foobar.html.twig')
            ->willReturn(false);

        /** @var Environment&MockObject $twig */
        $twig = $this->createMock(Environment::class);
        $twig
            ->expects($this->once())
            ->method('getLoader')
            ->willReturn($twigLoader);

        $renderingForwarder = $this->getRenderingForwarder($twig, $this->mockContaoFramework());

        /** @var Template&MockObject $template */
        $template = $this->createMock(Template::class);
        $template
            ->expects($this->once())
            ->method('getData')
            ->willReturn([
                'twig_template' => 'foobar.html.twig',
                'context' => ['a' => 123],
            ]);

        $this->expectExceptionMessage('Template \'foobar.html.twig\' wasn\'t loaded.');

        $renderingForwarder->render($template);
    }

    /**
     * @return ContaoFramework&MockObject
     */
    private function getFrameworkWithTemplateLoader()
    {
        $templateLoader = $this->mockAdapter(['addFile']);
        $templateLoader
            ->expects($this->exactly(2))
            ->method('addFile')
            ->withConsecutive(
                ['foo', 'templates'],
                ['bar', 'templates/sub']
            );

        return $this->mockContaoFramework([TemplateLoader::class => $templateLoader]);
    }

    private function getTemplatePaths(): array
    {
        return [
            $this->templateDir.'/foo.html.twig',
            $this->templateDir.'/sub/bar.html.twig',
        ];
    }

    private function getRenderingForwarder(
        Environment $twig = null,
        ContaoFramework $framework = null,
        TemplateLocator $locator = null,
        string $environment = 'prod'
    ): RenderingForwarder {
        /** @var Environment&MockObject $twig */
        $twig = $twig ?? $this->createMock(Environment::class);

        $locator = $locator ?? new TemplateLocator($this->templateDir);

        /** @var ContaoFramework&MockObject $framework */
        $framework = $framework ?? $this->getFrameworkWithTemplateLoader();

        return new RenderingForwarder(
            $twig,
            $locator,
            $framework,
            $this->rootDir,
            $environment
        );
    }
}
