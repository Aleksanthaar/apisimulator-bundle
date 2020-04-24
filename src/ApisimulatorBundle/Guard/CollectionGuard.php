<?php

namespace Aleksanthaar\ApisimulatorBundle\Guard;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CollectionGuard
{
    /**
     * @var array
     */
    protected $methods = [];

    /**
     * @var array
     */
    protected $statusCodes = [];

    /**
     * @var array
     */
    protected $paths = [];

    /**
     * @var string
     */
    protected $reason = '';

    public function __construct(
        array $methods,
        array $statusCodes,
        array $paths
    ) {
        $this->methods = $methods;
        $this->paths   = $paths;

        $this->unwrapStatusCodes($statusCodes);
    }

    public function collectorShouldCollect(Request $request, Response $response): bool
    {
        if (in_array($request->getMethod(), $this->methods)) {
            $this->reason = sprintf("Collect didn't take place because of method `%s`", $request->getMethod());

            return false;
        }

        if (in_array($response->getStatusCode(), $this->statusCodes)) {
            $this->reason = sprintf("Collect didn't take place because of status code `%d`", $response->getStatusCode());

            return false;
        }

        foreach($this->paths as $path) {
            $regex = sprintf('|%s|', $path);

            if (preg_match($regex, $request->getPathInfo())) {
                $this->reason = sprintf("Collect didn't take place because of path `%s`", $request->getPathInfo());

                return false;
            }
        }

        return true;
    }

    protected function unwrapStatusCodes(array $statusCodes): void
    {
        foreach ($statusCodes as $code) {
            if (preg_match('/^(?P<from>\d+)\-(?P<to>\d+)$/', $code, $match)) { // Codes range
                $this->statusCodes = array_merge($this->statusCodes, range($match['from'], $match['to']));
            } else { // One status code
                $this->statusCodes[] = $code;
            }
        }
    }

    /**
     * @return string
     */
    public function getReason(): string
    {
        return $this->reason;
    }
}