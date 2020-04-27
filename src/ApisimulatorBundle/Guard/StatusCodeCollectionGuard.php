<?php

namespace Aleksanthaar\ApisimulatorBundle\Guard;

use Aleksanthaar\ApisimulatorBundle\Guard\AbstractCollectionGuard;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class StatusCodeCollectionGuard extends AbstractCollectionGuard
{
    /**
     * @var array
     */
    protected $statusCodes = [];

    /**
     * @param array $statusCodes
     */
    public function __construct(array $statusCodes) {
        $this->unwrapStatusCodes($statusCodes);
    }

    /**
     * {inheritdoc}
     */
    public function collectorShouldCollect(Request $request, Response $response): bool
    {
        if (in_array($response->getStatusCode(), $this->statusCodes)) {
            $this->reason = sprintf("Collect didn't take place because of status code `%d`", $response->getStatusCode());

            return false;
        }

        return true;
    }

    /**
     * Spreads ranges of codes to list.
     *
     * @param  array  $statusCodes
     *
     * @return void
     */
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
}