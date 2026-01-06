<?php
declare(strict_types=1);
namespace Soatok\MiniFedi\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Soatok\MiniFedi\Orchestration;

#[CoversClass(Orchestration::class)]
class OrchestrationTest extends TestCase
{
    public function testOrchestration(): void
    {
        $orchestration = new Orchestration();
        $alice = $orchestration->createActor('alice');
        $bob = $orchestration->createActor('bob');
        $expected = [
            'minifedi_actors' => [
                $alice->toArray(),
                $bob->toArray(),
            ],
            'minifedi_fep_521a_publickeys' => [],
            'minifedi_inbox' => [],
            'minifedi_outbox' => [],
        ];
        $this->assertSame($expected, $orchestration->exportArray());
        $this->assertTrue($orchestration->stash());

        $empty = [
            'minifedi_actors' => [],
            'minifedi_fep_521a_publickeys' => [],
            'minifedi_inbox' => [],
            'minifedi_outbox' => [],
        ];
        $this->assertSame($empty, $orchestration->exportArray());
        $this->assertTrue($orchestration->unstash());
        $this->assertSame($expected, $orchestration->exportArray());
    }
}
