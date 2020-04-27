<?php

namespace Aleksanthaar\ApisimulatorBundle\Guard;

use Aleksanthaar\ApisimulatorBundle\Guard\AbstractCollectionGuard;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MethodCollectionGuard extends AbstractCollectionGuard
{
    /**
     * @var array
     */
    protected $methods = [];

    /**
     * @param array $methods
     */
    public function __construct(array $methods) {
        $this->methods = $methods;
    }

    /**
     * {inheritdoc}
     */
    public function collectorShouldCollect(Request $request, Response $response): bool
    {
        if (in_array($request->getMethod(), $this->methods)) {
            $this->reason = sprintf("Collect didn't take place because of method `%s`", $request->getMethod());

            return false;
        }

        return true;
    }
}