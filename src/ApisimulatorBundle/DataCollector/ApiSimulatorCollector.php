<?php

namespace Aleksanthaar\ApisimulatorBundle\DataCollector;

use Aleksanthaar\ApisimulatorBundle\Extractor\RequestExtractor;
use Aleksanthaar\ApisimulatorBundle\Extractor\ResponseExtractor;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

class ApiSimulatorCollector extends DataCollector
{
    public const COLLECTOR_NAME = 'data_collector.apisimulator';
    public const REMOVE_HEADERS = [];

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
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var array
     */
    protected $warnings = [];

    public function __construct(
        RequestExtractor $reqExtractor,
        ResponseExtractor $resExtractor,
        Environment $twig,
        RouterInterface $router
    ) {
        $this->reqExtractor = $reqExtractor;
        $this->resExtractor = $resExtractor;
        $this->twig         = $twig;
        $this->router       = $router;
    }

    public function collect(Request $request, Response $response, \Throwable $exception = null)
    {
        $requestContext  = $this->reqExtractor->collect($request);
        $responseContext = $this->resExtractor->collect($response);
        $context         = [
            'request'  => $request,
            'response' => $response,
        ];

        $context = array_merge($context, $requestContext, $responseContext);
        $simlet  = $this->twig->render('@Apisimulator/Simlet/simlet.yaml.twig', $context);

        $this->data = [
            'simlet'       => $simlet,
            'response'     => $response,
            'warnings'     => $this->warnings,
            'collected'    => true,
        ];
    }

    public function reset()
    {
        $this->data = [
            'collected' => false,
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

    public function getPostContent()
    {
        return $this->data['post_content'];
    }
}