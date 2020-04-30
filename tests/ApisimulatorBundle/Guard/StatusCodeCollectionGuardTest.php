<?php

namespace Tests\Aleksanthaar\ApisimulatorBundle\Guard;

use Aleksanthaar\ApisimulatorBundle\Guard\StatusCodeCollectionGuard;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class StatusCodeCollectionGuardTest extends TestCase
{
    /**
     * @test
     * @dataProvider collectorShouldCollectDataProvider
     */
    public function collectorShouldCollect(array $guardedStatusCodes, int $actualStatusCode, bool $expect)
    {
        $guard = new StatusCodeCollectionGuard($guardedStatusCodes);

        // Request object isn't used in this guard
        $request = $this->getMockBuilder(Request::class)->getMock();

        $response = new Response('', $actualStatusCode);

        $result = $guard->collectorShouldCollect($request, $response);

        $this->assertSame($expect, $result);

        // In case of non-collection, let's check the warning message mentions the status code
        if (!$expect) {
            $this->assertStringContainsString($actualStatusCode, $guard->getReason());
        }
    }

    public function collectorShouldCollectDataProvider()
    {
        // Status code passes
        yield [
            [ Response::HTTP_I_AM_A_TEAPOT ],
            Response::HTTP_OK,
            true
        ];

        // Status code blocked single-code format
        yield [
            [ Response::HTTP_I_AM_A_TEAPOT ],
            Response::HTTP_I_AM_A_TEAPOT,
            false
        ];

        // Status code blocked range-code format
        yield [
            [ '400-499' ],
            Response::HTTP_I_AM_A_TEAPOT,
            false
        ];
    }
}