<?php
/**
 * Date: 2020-01-14
 * Time: 13:43
 */

namespace Moon\Session;


use Predis\Client;

class Session
{
    protected $name;
    protected $id;
    protected $data;
    protected $cookieParams;

    protected $isStarted = false;

    protected $lifetime;

    /** @var DriverInterface $driver */
    protected $driver;

    public function __construct($name, $id, $cookieParams, $config)
    {
        $this->name = $name;
        $this->id = $id;
        $this->cookieParams = $cookieParams;
        if (isset($config['cookie_lifetime'])) {
            $this->cookieParams['lifetime'] = time() + $config['cookie_lifetime'];
            $this->lifetime = $config['cookie_lifetime'];
            unset($config['cookie_lifetime']);
        } else {
            $this->cookieParams['lifetime'] = time() + 3600;
            $this->lifetime = 3600;
        }

        foreach ($config as $key => $value) {
            $cookieKey = substr($key, 7);
            if (in_array($cookieKey, ['path', 'domain', 'secure', 'httponly', 'samesite'])) {
                $this->cookieParams[$cookieKey] = $value;
            }
        }

        if ($config['driver'] == 'redis') {
            /** @var Client $client */
            $client = $config['client']; //todo
            $this->driver = new RedisDriver($client, [
                'id' => $this->id,
                'lifetime' => $this->lifetime
            ]);
        } else {
            $this->driver = new FileDriver([
                'id' => $this->id,
                'savePath' => $config['savePath'],
                'lifetime' => $this->lifetime
            ]);
        }
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getCookieParams()
    {
        return $this->cookieParams;
    }

    public function all()
    {
        return $this->data;
    }

    public function get($key)
    {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

    public function set($key, $value)
    {
        $this->data[$key] = $value;
    }

    public function delete($key)
    {
        unset($this->data[$key]);
    }

    public function destroy()
    {
        $this->data = null;
        $this->driver->destroy();
    }

    public function start()
    {
        if ($this->isStarted) {
            return;
        }
        $this->load();
        $this->isStarted = true;
    }

    public function load()
    {
        $this->data = $this->driver->load();
    }

    public function write()
    {
        $this->driver->write($this->data);
    }

    function gc()
    {
        if (method_exists($this->driver, 'gc')) {
            $this->driver->gc();
        }
    }
}