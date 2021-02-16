<?php

declare(strict_types=1);

/*
 * @author  Moritz Vondano
 * @license MIT
 */

namespace Mvo\ContaoTwig\Tests;

use Contao\TestCase\ContaoTestCase;

class TestCase extends ContaoTestCase
{
    protected string $rootDir;
    protected string $templateDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->rootDir = __DIR__.'/Fixtures';
        $this->templateDir = $this->rootDir.'/templates';
    }
}
