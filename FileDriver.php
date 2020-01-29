<?php
/**
 * Date: 2020/1/29
 * Time: 12:14
 */

namespace Moon\Session;


class FileDriver implements DriverInterface
{
    protected $savePath;

    /** @var string $id session_id */
    protected $id;

    protected $lifetime;

    public function __construct(array $options)
    {
        foreach ($options as $option => $value) {
            if (property_exists($this, $option)) {
                $this->$option = $value;
            }
        }
    }

    public function write($data)
    {
        if (is_dir($this->savePath)) {
            @mkdir($this->savePath, 0777, true);
        }
        $filename = $this->savePath . '/' . $this->id;
        file_put_contents($filename, serialize($data));
    }

    public function load()
    {
        if (mt_rand(1, 1000) == 1) { //todo
            $this->gc();
        }

        if (!is_dir($this->savePath)) {
            mkdir($this->savePath, 0777, true);
        }

        $data = null;

        $filename = $this->savePath . '/' . $this->id;
        if (file_exists($filename)) {
            $data = file_get_contents($filename);
            $data = unserialize($data);
        }

        return $data;
    }

    public function destroy()
    {
        @unlink($this->savePath . '/' . $this->id);
    }

    public function gc()
    {
        foreach (glob("$this->savePath/*") as $file) {
            if (filemtime($file) + $this->lifetime < time() && file_exists($file)) {
                unlink($file);
            }
        }
    }
}