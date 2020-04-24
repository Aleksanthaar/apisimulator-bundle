<?php

namespace Aleksanthaar\ApisimulatorBundle\Extractor;

use Symfony\Component\HttpFoundation\HeaderBag;

trait HeaderRemovalTrait
{
    /**
     * @var array
     */
    protected $headersRemoval = [];

    /**
     * @param  HeaderBag $headers
     *
     * @return array
     */
    protected function cleanHeaders(HeaderBag $headers): array
    {
        return array_filter($headers->all(), function (string $headerName) {
            return !in_array($headerName, $this->headersRemoval);
        }, ARRAY_FILTER_USE_KEY);
    }
}