<?php namespace Zipzoft\HttpLogger;

use Illuminate\Support\Manager as LaravelManager;

class Manager extends LaravelManager
{
    /**
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->config->get('http-logger.default') ?: 'none';
    }

    /**
     * @return mixed
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function createElasticsearchDriver()
    {
        if (! class_exists('Elasticsearch\Client')) {
            throw new \InvalidArgumentException("Http logger need install elasticsearch/elasticsearch first");
        }

        $config = $this->config->get('http-logger.writers.elasticsearch');

        $writerClass = $config['writer'] ?? ElasticsearchWriter::class;

        return new $writerClass(
            $this->container->make('Elasticsearch\Client'),
            $config,
        );
    }

    /**
     * @return NoneWriter
     */
    protected function createNoneDriver()
    {
        return new NoneWriter();
    }
}
