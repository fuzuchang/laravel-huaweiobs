<?php
namespace Goodgay\HuaweiOBS;

use Illuminate\Contracts\Container\Container;

class Manager {
    
    /**
     * The application instance.
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $app;

    /**
     * The OBS bucket factory instance.
     *
     * @var \Goodgay\HuaweiOBS\Factory
     */
    protected $factory;

    /**
     * The active bucket instances.
     *
     * @var array
     */
    protected $buckets = [];

    /**
     * @param \Illuminate\Contracts\Container\Container $app
     * @param \Goodgay\HuaweiOBS\Factory $factory
     */
    public function __construct(Container $app, Factory $factory)
    {
        $this->app = $app;
        $this->factory = $factory;
    }


    /**
     * Retrieve or build the named bucket.
     *
     * @param string|null $name
     *
     * @return Client
     */
    public function bucket(string $name = null): Client
    {
        return $this->makeBucket($name?:'');
    }

    /**
     * Get the default Bucket.
     *
     * @return array
     */
    public function getDefaultBucket(): array
    {
        return $this->app['config']['hwobs'];
    }

    /**
     * Set the default Bucket.
     *
     * @param string $bucket
     */
    public function setDefaultBucket(string $bucket): void
    {
        $this->app['config']['hwobs'] = $bucket;
    }

    /**
     * Make a new bucket.
     *
     * @param string $name
     *
     * @return \Obs\ObsClient
     */
    protected function makeBucket(string $name): Client
    {
        $config = $this->getConfig($name);

        $client = $this->factory->make($config);

        return new Client($client);
    }

    /**
     * Get the configuration for a named bucket.
     *
     * @param $name
     *
     * @return mixed
     * @throws \InvalidArgumentException
     */
    protected function getConfig(string $name)
    {
        return $this->app['config']['hwobs'];
    }

    /**
     * Return all of the created buckets.
     *
     * @return array
     */
    public function getBuckets(): array
    {
        return $this->buckets;
    }

    /**
     * Dynamically pass methods to the default bucket.
     *
     * @param  string $method
     * @param  array $parameters
     *
     * @return mixed
     */
    public function __call(string $method, array $parameters)
    {
        return call_user_func_array([$this->bucket(), $method], $parameters);
    }

}