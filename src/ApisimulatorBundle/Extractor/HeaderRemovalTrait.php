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
        $lowerCaseHeaders = array_map('strtolower', $this->headersRemoval);

        return array_filter($headers->all(), function (string $headerName) use ($lowerCaseHeaders) {
            return !in_array(strtolower($headerName), $lowerCaseHeaders);
        }, ARRAY_FILTER_USE_KEY);
    }
}