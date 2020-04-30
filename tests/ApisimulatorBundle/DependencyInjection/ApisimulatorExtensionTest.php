<?php

namespace Tests\Aleksanthaar\ApisimulatorBundle\DependencyInjection;

use Aleksanthaar\ApisimulatorBundle\DependencyInjection\ApisimulatorExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ApisimulatorExtensionTest extends TestCase
{
    /**
     * @test
     * @dataProvider loadDataProvider
     */
    public function load(array $configuration, array $expectedParams)
    {
        $extension = new ApisimulatorExtension();
        $container = new ContainerBuilder();

        $extension->load($configuration, $container);

        // In the end, all parameters must be defined no matter the configuration.
        $this->assertTrue($container->hasParameter('apisimulator.guards.methods'));
        $this->assertTrue($container->hasParameter('apisimulator.guards.status_codes'));
        $this->assertTrue($container->hasParameter('apisimulator.guards.paths'));
        $this->assertTrue($container->hasParameter('apisimulator.headers_removal.request'));
        $this->assertTrue($container->hasParameter('apisimulator.headers_removal.response'));
        $this->assertTrue($container->hasParameter('apisimulator.warnings.request_headers'));
        $this->assertTrue($container->hasParameter('apisimulator.warnings.request_body'));

        $this->assertSame($expectedParams['guards.methods'], $container->getParameter('apisimulator.guards.methods'));
        $this->assertSame($expectedParams['guards.status_codes'], $container->getParameter('apisimulator.guards.status_codes'));
        $this->assertSame($expectedParams['guards.paths'], $container->getParameter('apisimulator.guards.paths'));
        $this->assertSame($expectedParams['headers_removal.request'], $container->getParameter('apisimulator.headers_removal.request'));
        $this->assertSame($expectedParams['headers_removal.response'], $container->getParameter('apisimulator.headers_removal.response'));
        $this->assertSame($expectedParams['warnings.request_headers'], $container->getParameter('apisimulator.warnings.request_headers'));
        $this->assertSame($expectedParams['warnings.request_body'], $container->getParameter('apisimulator.warnings.request_body'));
    }

    public function loadDataProvider()
    {
        // Configure nothing
        yield [
            static::getNoConfiguration(),
            static::getExpectedNoParameters()  
        ];

        // Configure everything
        yield [
            static::getFullConfiguration(),
            static::getExpectedFullParameters()  
        ];
    }

    protected static function getFullConfiguration(): array
    {
        $conf = '[{"guards":{"methods":["OPTIONS"],"status_codes":["300-308",404,"501-511"],"paths":["^/api/doc$"]},"headers_removal":{"request":["user-agent","content-length","accept","accept-encoding","accept-language","cookie","origin","referer","x-forwarded-for","x-forwarded-for","mod-rewrite","connection","x-php-ob-level"],"response":["cache-control","x-debug-token","date","set-cookie"]},"warnings":{"request_headers":[{"name":"Authorization","message":"Possibly irrelevant for simulation"}],"request_body":[{"name":"token","message":"Change tokens to dummy values before committing"}]}}]';

        return json_decode($conf, true);
    }

    protected static function getExpectedFullParameters(): array
    {
        return [
            'guards.methods' => [
                'OPTIONS'
            ],
            'guards.status_codes' => [
                '300-308',
                404,
                '501-511'
            ],
            'guards.paths' => [
                '^/api/doc$'
            ],
            'headers_removal.request' => [
                'user-agent',
                'content-length',
                'accept',
                'accept-encoding',
                'accept-language',
                'cookie',
                'origin',
                'referer',
                'x-forwarded-for',
                'x-forwarded-for',
                'mod-rewrite',
                'connection',
                'x-php-ob-level' 
            ],
            'headers_removal.response' => [
                'cache-control',
                'x-debug-token',
                'date',
                'set-cookie'
            ],
            'warnings.request_headers' => [
                'Authorization' => [
                    'message' => 'Possibly irrelevant for simulation',
                ]
            ],
            'warnings.request_body' => [
                'token' => [
                    'message' => 'Change tokens to dummy values before committing',
                ]
            ],
        ];
    }

    protected static function getNoConfiguration(): array
    {
        return [];
    }

    protected static function getExpectedNoParameters(): array
    {
        return [
            'guards.methods'           => [],
            'guards.status_codes'      => [],
            'guards.paths'             => [],
            'headers_removal.request'  => [],
            'headers_removal.response' => [],
            'warnings.request_headers' => [],
            'warnings.request_body'    => [],
        ];
    }
}
