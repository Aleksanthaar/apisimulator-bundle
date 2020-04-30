<?php

namespace Tests\Aleksanthaar\ApisimulatorBundle\Guard;

use Aleksanthaar\ApisimulatorBundle\Guard\PathCollectionGuard;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PathCollectionGuardTest extends TestCase
{
    /**
     * @test
     * @dataProvider collectorShouldCollectDataProvider
     */
    public function collectorShouldCollect(array $guardedPath, string $actualPath, bool $expect)
    {
        $guard = new PathCollectionGuard($guardedPath);

        $request = Request::create($actualPath);

        // Response object isn't used in this guard
        $response = $this->getMockBuilder(Response::class)->getMock();

        $result = $guard->collectorShouldCollect($request, $response);

        $this->assertSame($expect, $result);

        // In case of non-collection, let's check the warning message mentions the path
        if (!$expect) {
            $this->assertStringContainsString($actualPath, $guard->getReason());
        }
    }

    public function collectorShouldCollectDataProvider()
    {
        yield [
            [ '/api/doc'],
            '/contact',
            true
        ];

        yield [
            [ '/api/doc' ],
            '/api/doc',
            false
        ];
    }
}