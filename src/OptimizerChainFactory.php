<?php

namespace Spatie\LaravelImageOptimizer;

use Psr\Log\LoggerInterface;
use Spatie\ImageOptimizer\DummyLogger;
use Spatie\ImageOptimizer\OptimizerChain;
use Spatie\LaravelImageOptimizer\Exceptions\InvalidConfiguration;

class OptimizerChainFactory
{
    public static function create(array $config)
    {
        return (new OptimizerChain())
            ->useLogger()
            ->setTimeout($config['timeout'])
            ->setOptimizers(static::getOptimizers($config));
    }

    protected static function getLogger($config): LoggerInterface
    {
        $configuredLogger = $config['logOptimizerActivity'];

        if ($configuredLogger === true) {
            return app('log');
        }

        if ($configuredLogger === false) {
            return new DummyLogger();
        }

        if (! $configuredLogger instanceof LoggerInterface) {
            throw InvalidConfiguration::notAnLogger($configuredLogger);
        }

        return new $configuredLogger;
    }

    protected static function getOptimizers(array $config)
    {
        return collect($config['optimizers'])
            ->mapWithKeys(function (array $options, string $optimizerClass) {
                if (! $optimizerClass instanceof Optimizer) {
                    throw InvalidConfiguration::notAnOptimizer($optimizerClass);
                }

                return (new $optimizerClass)->setOptions($options);
            });
    }
}