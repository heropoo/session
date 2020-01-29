<?php
/**
 * Date: 2020-01-14
 * Time: 13:43
 */

namespace Moon\Session;


class Session
{
    protected $name;
    protected $id;
    protected $data;
    protected $cookieParams;

    protected $isStarted = false;

    protected $maxLifetime;

    /** @var DriverInterface $driver */
    protected $driver;

    public function __construct($name, $id, $cookieParams, $config)
    {
        $this->name = $name;
        $this->id = $id;
        $this->cookieParams = $cookieParams;
        if (isset($config['cookie_lifetime'])) {
            $this->cookieParams['lifetime'] = time() + $config['cookie_lifetime'];
            $this->maxLifetime = $config['cookie_lifetime'];
            unset($config['cookie_lifetime']);
        } else {
            $this->cookieParams['lifetime'] = time() + 3600;
            $this->maxLifetime = 3600;
        }

        foreach ($config as $key => $value) {
            $cookieKey = substr($key, 7);
            if (in_array($cookieKey, ['path', 'domain', 'secure', 'httponly', 'samesite'])) {
                $this->cookieParams[$cookieKey] = $value;
            }
        }

        if ($config['driver'] == 'redis') {
//            $this->driver = new RedisDriver([
//                'id' => $this->id,
            //'savePath' => $config['savePath'],
            //'maxLifetime' => $this->maxLifetime
//            ]);
        } else {
            $this->driver = new FileDriver([
                'id' => $this->id,
                'savePath' => $config['savePath'],
                'maxLifetime' => $this->maxLifetime
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
        $this->driver->gc();
    }
}