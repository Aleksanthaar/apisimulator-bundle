<?php

namespace Aleksanthaar\ApisimulatorBundle\Extractor;

use Aleksanthaar\ApisimulatorBundle\Extractor\HeaderRemovalTrait;
use Symfony\Component\HttpFoundation\Response;

class ResponseExtractor
{
    use HeaderRemovalTrait;

    public function __construct(array $headersRemoval)
    {
        $this->headersRemoval = $headersRemoval;
    }

    public function collect(Response $response): array
    {
        return [
            'responseHeaders' => $this->cleanHeaders($response->headers),
        ];
    }
}