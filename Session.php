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

    protected $savePath;

    protected $maxLifetime;

    public function __construct($name, $id, $cookieParams, $config)
    {
        $this->name = $name;
        $this->id = $id;
        $this->cookieParams = $cookieParams;
        if (isset($config['save_path'])) {
            $this->savePath = $config['save_path'];
        } else {
            $this->savePath = \App::$instance->getRootPath() . '/runtime/sessions';
        }
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

    public function getData()
    {
        return $this->data;
    }

    public function get($key)
    {
        return $this->data[$key] ?? null;
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
        @unlink($this->savePath . '/' . $this->getId());
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
        if (mt_rand(1, 1000) == 1) {
            $this->gc();
        }

        //todo other driver
        if (!is_dir($this->savePath)) {
            mkdir($this->savePath, 0777, true);
        }
        $filename = $this->savePath . '/' . $this->getId();
        if (file_exists($filename)) {
            $data = file_get_contents($filename);
            $this->data = unserialize($data);
        }
    }

    public function write()
    {
        if (is_dir($this->savePath)) {
            @mkdir($this->savePath, 0777, true);
        }
        $filename = $this->savePath . '/' . $this->getId();
        file_put_contents($filename, serialize($this->data));
    }

    function gc()
    {
        foreach (glob("$this->savePath/*") as $file) {
            if (filemtime($file) + $this->maxLifetime < time() && file_exists($file)) {
                unlink($file);
            }
        }

        return true;
    }
}