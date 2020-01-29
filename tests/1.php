<?php
/**
 * Date: 2020/1/29
 * Time: 12:44
 */

require_once __DIR__ . '/../vendor/autoload.php';

$config = [
    'driver' => 'file',
    'name' => 'test-session-id',
    'cookie_lifetime' => 60,
    'cookie_httponly' => true,
    'savePath' => __DIR__ . '/tmp',
];

if (isset($config['name'])) {
    $sessionName = $config['name'];
} else {
    $sessionName = session_name();
}

if (isset($_COOKIE[$sessionName])) {
    $sessionId = $_COOKIE[$sessionName];
} else {
    $sessionId = session_create_id();
}
$cookieParams = session_get_cookie_params();

$session = new \Moon\Session\Session($sessionName, $sessionId, $cookieParams, $config);
$session->load();
$session->set('name', 'xiaoming');
$session->set('age', 21);
$session->set('sex', 1);
$session->set('money', 188.11);
var_dump($session->all());

$session->delete('money');
var_dump($session->all());

$session->set('money', 256.22);
var_dump($session->all());
//var_dump($session->get('nnn'));

$session->destroy();
var_dump($session->all());

$session->write();
$cookieParams = $session->getCookieParams();

setcookie($sessionName, $sessionId, $cookieParams['lifetime'], $cookieParams['path'], $cookieParams['domain'],
    $cookieParams['secure'], $cookieParams['httponly']);