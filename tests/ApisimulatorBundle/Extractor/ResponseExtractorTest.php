<?php

namespace Tests\Aleksanthaar\ApisimulatorBundle\Extractor;

use Aleksanthaar\ApisimulatorBundle\Extractor\ResponseExtractor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class ResponseExtractorTest extends TestCase
{
    /**
     * @test
     */
    public function collect()
    {
        $response = new Response('', Response::HTTP_OK, [
            'host'            => 'localhost',
            'x-secret-header' => 'lorem ipsum',
            'X-CUSTOM-HEADER' => 'foo bar',
        ]);

        $extractor = new ResponseExtractor([ 'X-SECRET-HEADER', 'host' ]);

        $data = $extractor->collect($response);

        $this->assertArrayNotHasKey('x-secret-header', $data['responseHeaders']);
        $this->assertArrayNotHasKey('X-SECRET-HEADER', $data['responseHeaders']);
    }
}