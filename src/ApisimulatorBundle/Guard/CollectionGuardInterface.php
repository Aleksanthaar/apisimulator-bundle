<?php

namespace Aleksanthaar\ApisimulatorBundle\Guard;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface CollectionGuardInterface
{
    /**
     * Defines whether a simulation should be generated.
     *
     * @param  Request  $request
     * @param  Response $response
     *
     * @return bool
     */
    public function collectorShouldCollect(Request $request, Response $response): bool;

    /**
     * Returs the message describing why the simulation wasn't generated.
     *
     * @return string
     */
    public function getReason(): string;
}
