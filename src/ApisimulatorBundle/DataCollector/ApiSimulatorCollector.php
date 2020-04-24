<?php

namespace Aleksanthaar\ApisimulatorBundle\DataCollector;

use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

class ApiSimulatorCollector extends DataCollector
{
    public const COLLECTOR_NAME = 'data_collector.apisimulator';

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
    protected $postContent = [];

    /**
     * @var array
     */
    protected $postData = [];

    /**
     * @var array
     */
    protected $warnings = [];

    public function __construct(Environment $twig, RouterInterface $router)
    {
        $this->twig   = $twig;
        $this->router = $router;
    }

    public function collect(Request $request, Response $response, \Throwable $exception = null)
    {
        $this->handleRequestData($request);

        $simlet = $this->twig->render('@Apisimulator/Simlet/simlet.yaml.twig', [
            'method'          => $request->getMethod(),
            'request'         => $request,
            'requestHeaders'  => $this->cleanHeaders($request->headers),
            'requestBody'     => $this->postContent,
            'requestFormData' => $this->postData,
            'response'        => $response,
            'responseHeaders' => $this->cleanHeaders($response->headers),
        ]);

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

    protected function cleanHeaders(HeaderBag $headers)
    {
        return array_filter($headers->all(), function (string $headerName) {
            return !in_array($headerName, static::REMOVE_HEADERS);
        }, ARRAY_FILTER_USE_KEY);
    }

    protected function handleRequestData(Request $request)
    { 
        if (
            !empty($request->request->all())
            && $request->headers->contains('Content-Type', 'application/x-www-form-urlencoded')
        ) {
            $this->storePostForm(explode('&', $request->getContent()));
        }

        if (!empty($request->getContent() && $request->headers->contains('Content-Type', 'application/json'))) {
            $contentArray = json_decode($request->getContent(), true);

            if (!json_last_error()) {
                $this->storePostToJsonPath($contentArray);
            } else {
                $message = sprintf("`Content-Type: application/json` header found, but json_decode failed. Error message: %s", json_last_error_msg());

                $this->registerWarning($message, $request->getContent());
            }
        }
    }

    protected function storePostForm(array $formData): void
    {
        foreach($formData as $value) {
            $this->postData[] = $value;
        }
    }

    protected function storePostToJsonPath(array $post, string $basePath = ''): void
    {
        foreach($post as $key => $value) {
            $key = sprintf('%s.%s', $basePath, $key);

            if (is_array($value)) {
                $this->storePostToJsonPath($value, $key);
            } else {
                if (preg_match('/date/i', $key)) {
                    $message = sprintf("`%s` sounds like a date. Consider removing it from the match criteria.", $key);

                    $this->registerWarning($message);
                }

                $this->postContent[$key] = $value;
            }
        }
    }

    protected function registerWarning(string $message, ?string $debug = null)
    {
        $this->warnings[] = [
            'message' => $message,
            'debug'   => $debug,
        ];
    }
}