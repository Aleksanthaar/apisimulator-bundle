<?php

namespace Aleksanthaar\ApisimulatorBundle\Extractor;

use Aleksanthaar\ApisimulatorBundle\Extractor\HeaderRemovalTrait;
use Symfony\Component\HttpFoundation\Request;

class RequestExtractor
{
    use HeaderRemovalTrait;

    public const CONTENT_TYPE_FORM_ENCODED = 'application/x-www-form-urlencoded';
    public const CONTENT_TYPE_JSON         = 'application/json';

    /**
     * @var array
     */
    protected $warnings = [];

    /**
     * @var array
     */
    protected $postData = [];

    /**
     * @var array
     */
    protected $postContent = [];
    /**
     * @var array
     */
    protected $customWarnings = [];
    /**
     * @var array
     */
    protected $warningsBodyKeys = [];

    public function __construct(array $headersRemoval, array $customWarnings)
    {
        $this->headersRemoval = $headersRemoval;
        $this->customWarnings = $customWarnings;

        // Early computing
        $this->warningsBodyKeys = array_keys($this->customWarnings['requestBody']);
    }

    public function collect(Request $request): array
    {
        if (
            !empty($request->request->all())
            && $request->headers->contains('Content-Type', static::CONTENT_TYPE_FORM_ENCODED)
        ) {
            $this->storeFormData(explode('&', $request->getContent()));
        }

        if (!empty($request->getContent() && $request->headers->contains('Content-Type', static::CONTENT_TYPE_JSON))) {
            $contentArray = json_decode($request->getContent(), true);

            if (!json_last_error()) {
                $this->storePostBody($contentArray);
            } else {
                $message = sprintf("`Content-Type: application/json` header found, but json_decode failed. Error message: %s", json_last_error_msg());

                $this->registerWarning($message, $request->getContent());
            }
        }

        $headers = $this->cleanHeaders($request->headers);

        $this->checkHeadersForWarning($headers);

        return [
            'requestBody'     => $this->postContent,
            'requestFormData' => $this->postData,
            'requestHeaders'  => $headers,
        ];
    }

    protected function storeFormData(array $formData): void
    {
        foreach($formData as $value) {
            $warnings = array_filter($this->warningsBodyKeys, function (string $warningKey) use ($value) {
                return false !== strpos($value, $warningKey);
            });

            array_walk($warnings, function($warningName) use ($value) {
                $warning = $this->customWarnings['requestBody'][$warningName];
                $message = sprintf('Request formData element `%s` created a warning: %s', $value, $warning['message']);

                $this->registerWarning($message, $value);
            });

            $this->postData[] = $value;
        }
    }

    protected function storePostBody(array $post, string $basePath = ''): void
    {
        foreach($post as $key => $value) {
            $key = sprintf('%s.%s', $basePath, $key);

            if (is_array($value)) {
                $this->storePostBody($value, $key);
            } else {
                if (preg_match('/date/i', $key)) {
                    $message = sprintf("`%s` sounds like a date. Consider removing it from the match criteria.", $key);

                    $this->registerWarning($message);
                }

                $warnings = array_filter($this->warningsBodyKeys, function (string $warningKey) use ($key) {
                    return false !== strpos($key, $warningKey);
                });

                array_walk($warnings, function($warningName) use ($key, $value) {
                    $warning = $this->customWarnings['requestBody'][$warningName];
                    $message = sprintf('Request body element `%s` created a warning for %s: %s', $key, $warningName, $warning['message']);

                    $this->registerWarning($message, $value);
                });

                $this->postContent[$key] = $value;
            }
        }
    }

    protected function checkHeadersForWarning(array $headers): void
    {
        $headersWithWarnings = array_filter($headers, function(string $key) {
            return in_array($key, array_keys($this->customWarnings['requestHeaders']));
        }, ARRAY_FILTER_USE_KEY);

        foreach ($headersWithWarnings as $header => $value) {
            $warning = $this->customWarnings['requestHeaders'][$header];
            $message = sprintf('Request header `%s` created a warning: %s', $header, $warning['message']);

            $this->registerWarning($message, json_encode($value));
        }
    }

    protected function registerWarning(string $message, ?string $debug = null): void
    {
        $this->warnings[] = [
            'message' => $message,
            'debug'   => $debug,
        ];
    }

    /**
     * @return array
     */
    public function getWarnings(): array
    {
        return $this->warnings;
    }
}
