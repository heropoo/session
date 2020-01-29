<?php
/**
 * Date: 2020/1/29
 * Time: 12:17
 */

namespace Moon\Session;


interface DriverInterface
{
    public function load();

    public function write($data);

    public function gc();

    public function destroy();
}