<?php

use Sabre\DAV;

date_default_timezone_set('UTC');

require_once 'vendor/autoload.php';

$u = 'tobi';
$p = '1234';

$auth = new \Sabre\HTTP\DigestAuth();
$auth->init();

if ($auth->getUsername() != $u || !$auth->validatePassword($p)) {
    $auth->requireLogin();
    echo "Authentication required\n";
    die();
}

$rootDirectory = new DAV\FS\Directory('data');

$server = new DAV\Server($rootDirectory);
$server->setBaseUri('/');

// The lock manager is reponsible for making sure users don't overwrite each others changes. Change 'data' to a different
// directory, if you're storing your data somewhere else.
$lockBackend = new DAV\Locks\Backend\File('data/locks');
$lockPlugin = new DAV\Locks\Plugin($lockBackend);
$server->addPlugin($lockPlugin);

$plugin = new DAV\Browser\Plugin();
$server->addPlugin($plugin);


$server->exec();

?>
