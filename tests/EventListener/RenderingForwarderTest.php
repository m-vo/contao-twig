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

    public function testSkipsForwardRenderingIfTemplateNotRegistered(): void
    {
        $renderingForwarder = $this->getRenderingForwarder(null, $this->mockContaoFramework());

        $output = $renderingForwarder->forwardRendering('old buffer', 'text');
        $this->assertSame('old buffer', $output);
    }

    public function testSkipsForwardRenderingIfContextWasNotSet(): void
    {
        $renderingForwarder = $this->getRenderingForwarder();

        $renderingForwarder->setTemplatePaths($this->getTemplatePaths());
        $renderingForwarder->registerTemplates();

        $output = $renderingForwarder->forwardRendering(
            'old buffer',
            'foo'
        );

        $this->assertSame('old buffer', $output);
    }

    public function testForwardRendering(): void
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

        $renderingForwarder = $this->getRenderingForwarder($twig);

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
            ->method('getData')
            ->willReturn(['a' => 123]);

        $renderingForwarder->storeTemplateContext($template);

        $output = $renderingForwarder->forwardRendering(
            'old buffer',
            'bar'
        );

        $this->assertSame('twig content', $output);
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
