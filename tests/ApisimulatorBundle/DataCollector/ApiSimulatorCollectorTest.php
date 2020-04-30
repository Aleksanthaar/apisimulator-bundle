<?php

namespace Tests\Aleksanthaar\ApisimulatorBundle\DataCollector;

use Aleksanthaar\ApisimulatorBundle\DataCollector\ApiSimulatorCollector;
use Aleksanthaar\ApisimulatorBundle\Extractor\RequestExtractor;
use Aleksanthaar\ApisimulatorBundle\Extractor\ResponseExtractor;
use Aleksanthaar\ApisimulatorBundle\Guard\MethodCollectionGuard;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Stopwatch\Stopwatch;
use Twig\Environment;

class ApiSimulatorCollectorTest extends TestCase
{
    /**
     * @var ApiSimulatorCollector
     */
    protected $collector;

    public function setUp(): void
    {
        // We'll just guard the HEAD method for this test
        $guards = new RewindableGenerator(function() {
            yield new MethodCollectionGuard([ Request::METHOD_HEAD ]);
        }, 1);

        $requestExtractor  = new RequestExtractor(
            [],
            [
                'requestBody' => [],
                'requestHeaders' => [],
            ]
        );
        $responseExtractor = new ResponseExtractor([], []);

        // Mock twig for now
        $environment = $this
            ->getMockBuilder(Environment::class)
            ->disableOriginalConstructor()
            ->onlyMethods([ 'render' ])
            ->getMock()
        ;

        $environment->method('render')->willReturn('simlet stub');

        $this->collector = new ApiSimulatorCollector(
            $guards,
            $requestExtractor,
            $responseExtractor,
            $environment,
            new Stopwatch()
        );
    }

    /**
     * @test
     *
     * Yes, this is only here to hit 100% code coverage.
     */
    public function getColletorName()
    {
        $this->assertSame(ApiSimulatorCollector::COLLECTOR_NAME, $this->collector->getName());
    }

    /**
     * @test
     * @dataProvider collectDataProvider
     */
    public function collect(Request $request, Response $response, bool $expectCollected, array $warnings, ?string $simlet)
    {
        $this->collector->collect($request, $response);

        $this->assertSame($expectCollected, $this->collector->getCollected());
        $this->assertSame($warnings, $this->collector->getWarnings());
        $this->assertSame($simlet, $this->collector->getSimlet());

        // Response isn't made available unless collect took place
        if ($expectCollected) {
            $this->assertSame($response, $this->collector->getResponse());
        }

        // Reset and check data
        $this->collector->reset();

        $this->assertFalse($this->collector->getCollected());
        $this->assertNull($this->collector->getSimlet());
        $this->assertNull($this->collector->getResponse());
        $this->assertEmpty($this->collector->getWarnings());
    }

    public function collectDataProvider()
    {
        // No collect
        yield [
            Request::create('/irrelevant', Request::METHOD_HEAD),
            new Response(),
            false,
            [
                [
                    'message' => 'Collect didn\'t take place because of method `HEAD`',
                ],
            ],
            null, // no simlet generated
        ];

        // Collect
        yield [
            Request::create('/irrelevant', Request::METHOD_POST),
            new Response(),
            true,
            [],
            'simlet stub'
        ];  
    }
}
