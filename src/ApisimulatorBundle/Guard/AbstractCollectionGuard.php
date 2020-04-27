<?php

namespace Aleksanthaar\ApisimulatorBundle\Guard;

use Aleksanthaar\ApisimulatorBundle\Guard\CollectionGuardInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractCollectionGuard implements CollectionGuardInterface
{
    /**
     * @var string
     */
    protected $reason = '';

    /**
     * @return string
     */
    public function getReason(): string
    {
        return $this->reason;
    }
}