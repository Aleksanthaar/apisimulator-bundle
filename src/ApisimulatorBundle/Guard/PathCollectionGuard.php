<?php

namespace Aleksanthaar\ApisimulatorBundle\Guard;

use Aleksanthaar\ApisimulatorBundle\Guard\AbstractCollectionGuard;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PathCollectionGuard extends AbstractCollectionGuard
{
    /**
     * @var array
     */
    protected $paths = [];

    /**
     * @param array $paths
     */
    public function __construct(array $paths) {
        $this->paths   = $paths;
    }

    /**
     * {inheritdoc}
     */
    public function collectorShouldCollect(Request $request, Response $response): bool
    {
        foreach($this->paths as $path) {
            $regex = sprintf('|%s|', $path);

            if (preg_match($regex, $request->getPathInfo())) {
                $this->reason = sprintf("Collect didn't take place because of path `%s`", $request->getPathInfo());

                return false;
            }
        }

        return true;
    }
}