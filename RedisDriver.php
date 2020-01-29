<?php
/**
 * Date: 2020/1/29
 * Time: 13:52
 */

namespace Moon\Session;


use Predis\Client;

class RedisDriver implements DriverInterface
{
    /** @var string $id session_id */
    protected $id;

    protected $lifetime;

    /** @var Client $client */
    protected $client;

    protected $keyPrefix = 'sess_';

    public function __construct(Client $client, array $options)
    {
        $this->client = $client;

        foreach ($options as $option => $value) {
            if (property_exists($this, $option)) {
                $this->$option = $value;
            }
        }
    }

    protected function getKey()
    {
        return $this->keyPrefix . $this->id;
    }

    public function write($data)
    {
        $data = serialize($data);
        $this->client->setex($this->getKey(), $this->lifetime, $data);
    }

    public function load()
    {
        $data = $this->client->get($this->getKey());

        return unserialize($data);
    }

    public function destroy()
    {
        $this->client->del([$this->getKey()]);
    }
}