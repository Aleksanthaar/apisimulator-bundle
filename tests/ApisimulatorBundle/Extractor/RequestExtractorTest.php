<?php

namespace Tests\Aleksanthaar\ApisimulatorBundle\Extractor;

use Aleksanthaar\ApisimulatorBundle\Extractor\RequestExtractor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class RequestExtractorTest extends TestCase
{
    /**
     * @test
     */
    public function collectHeaders()
    {
        $request = Request::create('/irrelevant', Request::METHOD_GET, [], [], [], [
            'HTTP_X-AUTH-TOKEN' => 'mySecretToken',
        ]);

        $extractor = new RequestExtractor(
            [
                'X-AUTH-TOKEN'
            ],
            [
                'requestBody'    => [],
                'requestHeaders' => [
                    'host' => [
                       'message' => 'This header will be irrelevant when using APISimulator',    
                    ]
                ],
            ]
        );

        $result = $extractor->collect($request);

        $this->assertEmpty($result['requestBody']);
        $this->assertEmpty($result['requestFormData']);

        // Make sure the X-AUTH-TOKEN header was removed
        $this->assertArrayNotHasKey('x-auth-token', $result);

        // Make sure the custom warning on "host" is here
        $expectWarning = [
            'message' => 'Request header `host` created a warning: This header will be irrelevant when using APISimulator',
            'debug'   => '["localhost"]',
        ];
        $this->assertContains($expectWarning, $extractor->getWarnings());
    }
}