<?php

namespace Square1\Laravel\PwnedCheck;

use Cache;
use Square1\Pwned\Pwned;

class PwnedCheck extends Pwned
{
    /**
     * Constructor
     *
     * @param array $config Config options
     */
    public function __construct($config = [])
    {
        parent::__construct([]);

        $config = array_merge(
            [
                'endpoint' => config('pwned-check.range_endpoint'),
                'user_agent' => config('pwned-check.user_agent'),
                'connection_timeout' => config('pwned-check.connection_timeout'),
                'remote_processing_timeout' => config('pwned-check.remote_processing_timeout'),
            ],
            $config
        );

        $this->setConfig($config);
    }


    /**
     * Make api call for password range
     * Wrap parent model call in laravel cache
     *
     * @param string $range
     *
     * @return array
     *
     * @throws ConnectionFailedException
     * @throws GeneralException
     */
    public function getApiResultsForRange($range)
    {
        return Cache::remember('pwned-check-'.$range.rand(), config('pwned-check.cache_default_ttl'), function () use ($range) {
            return parent::getApiResultsForRange($range);
        });
    }
}