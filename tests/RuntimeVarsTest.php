<?php
declare(strict_types=1);
namespace Soatok\MiniFedi\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Soatok\MiniFedi\FediServerConfig;
use Soatok\MiniFedi\RuntimeVars;

#[CoversClass(RuntimeVars::class)]
class RuntimeVarsTest extends TestCase
{
    public function testVars(): void
    {
        $conf = FediServerConfig::instance();
        $vars = $conf->vars();
        $this->assertNotEmpty($vars->hostname);
    }
}
