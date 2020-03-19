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
use Contao\TestCase\ContaoTestCase;
use Mvo\ContaoTwig\EventListener\RenderingForwarder;
use PHPUnit\Framework\MockObject\MockObject;
use Twig\Environment;
use Twig\Loader\LoaderInterface;
use Webmozart\PathUtil\Path;

class RenderingForwarderTest extends ContaoTestCase
{
    private string $rootDir;

    private string $templateDir;

    public function setUp(): void
    {
        parent::setUp();

        $this->rootDir = Path::canonicalize(__DIR__.'/../Fixtures');
        $this->templateDir = $this->rootDir.'/templates';
    }

    public function testRegistersTemplates(): void
    {
        $renderingForwarder = $this->getRenderingForwarder();

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
            ->method('__set')
            ->with('_twig_template', 'sub/bar.html.twig');

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
            ->willReturn(['a' => 123, '_twig_template' => 'sub/bar.html.twig']);

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

        $this->expectExceptionMessage('The template\'s context must contain a value for \'_twig_template\'');

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
            ->willReturn(['a' => 123, '_twig_template' => 'foobar.html.twig']);

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

    private function getRenderingForwarder(Environment $twig = null, ContaoFramework $framework = null): RenderingForwarder
    {
        /** @var Environment&MockObject $twig */
        $twig = $twig ?? $this->createMock(Environment::class);

        /** @var ContaoFramework&MockObject $framework */
        $framework = $framework ?? $this->getFrameworkWithTemplateLoader();

        return new RenderingForwarder(
            $twig,
            $framework,
            $this->rootDir,
            $this->templateDir
        );
    }
}
