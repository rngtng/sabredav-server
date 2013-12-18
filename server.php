<?php

use
    Sabre\DAV;

require_once 'vendor/autoload.php';

// Now we're creating a whole bunch of objects

// Change public to something else, if you are using a different directory for your files
$rootDirectory = new DAV\FS\Directory('public');

// The server object is responsible for making sense out of the WebDAV protocol
$server = new DAV\Server($rootDirectory);
$server->setBaseUri('/'); // ideally, SabreDAV lives on a root directory with mod_rewrite sending every request to server.php

// The lock manager is reponsible for making sure users don't overwrite each others changes. Change 'data' to a different
// directory, if you're storing your data somewhere else.
$lockBackend = new DAV\Locks\Backend\File('data/locks');
$lockPlugin = new DAV\Locks\Plugin($lockBackend);
$server->addPlugin($lockPlugin);

$plugin = new DAV\Browser\Plugin();
$server->addPlugin($plugin);

// All we need to do now, is to fire up the server
$server->exec();

?>
