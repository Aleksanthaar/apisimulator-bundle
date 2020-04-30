<?php

namespace Tests\Aleksanthaar\ApisimulatorBundle\Guard;

use Aleksanthaar\ApisimulatorBundle\Guard\MethodCollectionGuard;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MethodCollectionGuardTest extends TestCase
{
    /**
     * @test
     * @dataProvider collectorShouldCollectDataProvider
     */
    public function collectorShouldCollect(array $guardedMethods, string $actualMethod, bool $expect)
    {
        $guard = new MethodCollectionGuard($guardedMethods);

        $request = Request::create('/irrelevant', $actualMethod);

        // Response object isn't used in this guard
        $response = $this->getMockBuilder(Response::class)->getMock();

        $result = $guard->collectorShouldCollect($request, $response);

        $this->assertSame($expect, $result);

        // In case of non-collection, let's check the warning message mentions the method
        if (!$expect) {
            $this->assertStringContainsString($actualMethod, $guard->getReason());
        }
    }

    public function collectorShouldCollectDataProvider()
    {
        yield [
            [ Request::METHOD_POST ],
            Request::METHOD_GET,
            true
        ];

        yield [
            [ Request::METHOD_POST ],
            Request::METHOD_POST,
            false
        ];
    }
}