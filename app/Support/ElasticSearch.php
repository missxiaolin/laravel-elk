<?php

namespace App\Support;

use Elasticsearch\ClientBuilder;

/**
 * Description of ElasticSearch
 *
 * @author dehua
 */
class ElasticSearch
{

    protected $index = 'php-';

    protected $config;

    protected $env;

    protected $host;

    protected $name;

    protected $body;

    protected $options;

    /**
     *
     * @var \Elasticsearch\Client
     */
    protected $elastic;

    public function __construct($name)
    {
        $this->env = env('APP_ENV');
        $this->config = config('elasticsearch.' . $this->env);
        $this->host = array_get($this->config, 'host');
        $this->name = $name;
        $this->index = $this->index . $this->env . '-';
        $this->index = $this->index . env('APP_ELASTIC_NAME', 'default');
        $this->index = $this->index . '-' . date('Y.m.d');
        $this->elastic = ClientBuilder::create()
            ->setHosts($this->host)
            ->setRetries(2)
            ->build();
    }


    /**
     * @param array $body
     * @param array $options
     * @return array
     * @throws \Exception
     */
    public function sync($body = [], $options = [])
    {
        $this->options = $options;
        $time = array_get($options, 'time', time());
        if (!isset($options['timestamp'])) {
            $body['@timestamp'] = date('Y-m-d\TH:i:s.u\Z', $time - 28800);
        } else {
            $body['@timestamp'] = $options['timestamp'];
        }

        $data = [
            'index' => $this->index,
            'type' => $this->name,
            'timeout' => '100ms',
            'body' => $body,
        ];


        try {
            $ret = $this->elastic->index($data);
            return $ret;
        } catch (\Exception $ex) {
            $error = [
                'code' => $ex->getCode(),
                'message' => $ex->getMessage(),
            ];
            if ($ex->getCode() == 404) {
                $this->createIndex();

            }
            logger('elasticsearch', $error);
//            throw new \Exception($ex->getMessage(), $ex->getCode());
        }

    }

    /**
     * 创建索引
     */
    protected function createIndex()
    {

        $params = [
            'index' => $this->index,
        ];

        $result = $this->elastic->indices()->create($params);

    }

    protected function getUserName()
    {
        return array_get($this->config, 'username');
    }

    protected function getPassword()
    {
        return array_get($this->config, 'password');
    }
}
