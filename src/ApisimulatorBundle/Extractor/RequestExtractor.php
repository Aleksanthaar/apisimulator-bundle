<?php

namespace Aleksanthaar\ApisimulatorBundle\Extractor;

use Aleksanthaar\ApisimulatorBundle\Extractor\HeaderRemovalTrait;
use Symfony\Component\HttpFoundation\Request;

class RequestExtractor
{
    use HeaderRemovalTrait;

    public function __construct(array $headersRemoval)
    {
        $this->headersRemoval = $headersRemoval;
    }

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

        return [
            'requestBody'     => $this->postContent,
            'requestFormData' => $this->postData,
            'requestHeaders'  => $this->cleanHeaders($request->headers),
        ];
    }

    protected function storeFormData(array $formData): void
    {
        foreach($formData as $value) {
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

                $this->postContent[$key] = $value;
            }
        }
    }

    protected function registerWarning(string $message, ?string $debug = null): void
    {
        $this->warnings[] = [
            'message' => $message,
            'debug'   => $debug,
        ];
    }
}
