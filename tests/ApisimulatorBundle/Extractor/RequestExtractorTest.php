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

    /**
     * @test
     */
    public function collectJsonBody()
    {
        $body = json_encode([
            'foo'   => 'bar',
            'date'  => 'trigger warning',
            'lorem' => [
                'ipsum' => [
                    'dolor' => [
                        'sit' => 'amet',
                    ],
                ],
            ],
        ]);

        $request = Request::create('/irrelevant', Request::METHOD_GET, [], [], [], [
            'HTTP_Content-Type' => RequestExtractor::CONTENT_TYPE_JSON,
        ], $body);

        $extractor = new RequestExtractor(
            [],
            [
                'requestBody'    => [
                    'lorem' => [
                        'message' => 'This parameter seems to contain dummy text'
                    ],
                ],
                'requestHeaders' => [],
            ]
        );

        $result = $extractor->collect($request);

        $expectBody = [
            '.foo'                   => 'bar',
            '.date'                  => 'trigger warning',
            '.lorem.ipsum.dolor.sit' => 'amet',
        ];

        $this->assertSame($expectBody, $result['requestBody']);

        $dateWarning = [
            'message' => '`.date` sounds like a date. Consider removing it from the match criteria.',
            'debug'   => null,
        ];

        $loremIpsumWarning = [
            'message' => 'Request body element `.lorem.ipsum.dolor.sit` created a warning for lorem: This parameter seems to contain dummy text',
            'debug'   => 'amet',
        ];

        $this->assertContains($dateWarning, $extractor->getWarnings());
        $this->assertContains($loremIpsumWarning, $extractor->getWarnings());
    }


    /**
     * @test
     */
    public function collectInvalidJsonBody()
    {
        $body = json_encode([
            'foo'   => 'bar',
            'date'  => 'trigger warning',
            'lorem' => [
                'ipsum' => [
                    'dolor' => [
                        'sit' => 'amet',
                    ],
                ],
            ],
        ]);

        $request = Request::create('/irrelevant', Request::METHOD_GET, [], [], [], [
            'HTTP_Content-Type' => RequestExtractor::CONTENT_TYPE_JSON,
        ], '{{notJson');

        $extractor = new RequestExtractor(
            [],
            [
                'requestBody'    => [
                    'lorem' => [
                        'message' => 'This parameter seems to contain dummy text'
                    ],
                ],
                'requestHeaders' => [],
            ]
        );

        $result = $extractor->collect($request);

        $this->assertEmpty($result['requestBody']);

        $notJsonWarning = [
            'message' => '`Content-Type: application/json` header found, but json_decode failed. Error message: Syntax error',
            'debug'   => '{{notJson',
        ];

        $this->assertContains($notJsonWarning, $extractor->getWarnings());

    }

    /**
     * @test
     */
    public function collectBodyFormData()
    {
        $body = implode('&', [
            'foo=bar',
            'order=66',
        ]);

        $request = Request::create('/irrelevant', Request::METHOD_POST, [ 'post' => 'post' ], [], [], [
            'HTTP_Content-Type' => RequestExtractor::CONTENT_TYPE_FORM_ENCODED,
        ], $body);

        $extractor = new RequestExtractor(
            [],
            [
                'requestBody'    => [
                    'order' => [
                        'message' => 'The Emperor approves.'
                    ],
                ],
                'requestHeaders' => [],
            ]
        );

        $result = $extractor->collect($request);

        $expectBody = [
            'foo=bar',
            'order=66',
        ];

        $this->assertSame($expectBody, $result['requestFormData']);

        $order66Warning = [
            'message' => 'Request formData element `order=66` created a warning: The Emperor approves.',
            'debug'   => 'order=66',
        ];

        $this->assertContains($order66Warning, $extractor->getWarnings());
    }
}