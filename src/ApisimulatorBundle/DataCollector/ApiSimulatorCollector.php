<?php

namespace Aleksanthaar\ApisimulatorBundle\DataCollector;

use Aleksanthaar\ApisimulatorBundle\Extractor\RequestExtractor;
use Aleksanthaar\ApisimulatorBundle\Extractor\ResponseExtractor;
use Aleksanthaar\ApisimulatorBundle\Guard\CollectionGuard;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\Stopwatch\Stopwatch;
use Twig\Environment;

class ApiSimulatorCollector extends DataCollector
{
    public const COLLECTOR_NAME = 'data_collector.apisimulator';

    /**
     * @var array
     */
    protected $guards = [];

    /**
     * @var RequestExtractor
     */
    protected $reqExtractor;

    /**
     * @var ResponseExtractor
     */
    protected $resExtractor;

    /**
     * @var Environment
     */
    protected $twig;

    /**
     * @var array
     */
    protected $warnings = [];

    /**
     * @var Stopwatch
     */
    protected $stopwatch;

    public function __construct(
        iterable $guards,
        RequestExtractor $reqExtractor,
        ResponseExtractor $resExtractor,
        Environment $twig,
        Stopwatch $stopwatch
    ) {
        $this->guards       = iterator_to_array($guards);
        $this->reqExtractor = $reqExtractor;
        $this->resExtractor = $resExtractor;
        $this->twig         = $twig;
        $this->stopwatch    = $stopwatch;
    }

    public function collect(Request $request, Response $response, \Throwable $exception = null)
    {
        foreach($this->guards as $guard) {
            if (!$guard->collectorShouldCollect($request, $response)) {
                $this->data = [
                    'collected' => false,
                    'warnings'  => [
                        [
                            'message' => $guard->getReason(),
                        ],
                    ],
                ];

                return;
            }
        }

        $this->stopwatch->start('simulations');

        $requestContext  = $this->reqExtractor->collect($request);
        $responseContext = $this->resExtractor->collect($response);
        $context         = [
            'request'  => $request,
            'response' => $response,
        ];

        $context = array_merge($context, $requestContext, $responseContext);
        $simlet  = $this->twig->render('@Apisimulator/Simlet/simlet.yaml.twig', $context);

        $warnings = array_merge($this->warnings, $this->reqExtractor->getWarnings());

        $this->data = [
            'simlet'       => $simlet,
            'response'     => $response,
            'warnings'     => $warnings,
            'collected'    => true,
        ];

        $this->stopwatch->stop('simulations');
    }

    public function reset()
    {
        $this->data = [
            'collected' => false,
            'simlet'    => null,
            'response'  => null,
            'warnings'  => [],
        ];
    }

    public function getName()
    {
        return static::COLLECTOR_NAME;
    }

    public function getSimlet()
    {
        return $this->data['simlet'];
    }

    public function getResponse()
    {
        return $this->data['response'];
    }

    public function getWarnings()
    {
        return $this->data['warnings'];
    }

    public function getCollected()
    {
        return $this->data['collected'];
    }
}