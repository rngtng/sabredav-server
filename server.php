<?php

date_default_timezone_set('UTC');

require_once 'config.php';

require_once 'vendor/autoload.php';

$baseUri = '/dav.warteschlange.de';
$_SERVER['REQUEST_URI'] =  $baseUri.$_SERVER['REQUEST_URI'];

$myFile = "requestslog.txt";
$fh = fopen($myFile, 'a') or die("can't open file");
fwrite($fh, "\n\n---------------------------------------------------------------\n");
foreach($_SERVER as $h => $v) {
  // if(ereg('HTTP_(.+)', $h, $hp)) {
    fwrite($fh, "$h = $v\n");
  // }
}
fwrite($fh, "\r\n");
fwrite($fh, file_get_contents('php://input'));
fclose($fh);



$pdo = new PDO(MYSQL_HOST, MYSQL_USER, MYSQL_PASS);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function exception_error_handler($errno, $errstr, $errfile, $errline ) {
    echo $errstr;
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}
set_error_handler("exception_error_handler");

$authBackend      = new \Sabre\DAV\Auth\Backend\PDO($pdo);
$principalBackend = new \Sabre\DAVACL\PrincipalBackend\PDO($pdo);
$carddavBackend   = new \Sabre\CardDAV\Backend\PDO($pdo);
$caldavBackend    = new \Sabre\CalDAV\Backend\PDO($pdo);

$nodes = array(
    // /principals
    new \Sabre\CalDAV\Principal\Collection($principalBackend),
    // /calendars
    new \Sabre\CalDAV\CalendarRootNode($principalBackend, $caldavBackend),
    // /addressbook
    new \Sabre\CardDAV\AddressBookRoot($principalBackend, $carddavBackend),

    //new Sabre\DAV\FS\Directory('public'),
);

// The object tree needs in turn to be passed to the server class
$server = new \Sabre\DAV\Server($nodes);
$server->setBaseUri($baseUri);

// Plugins
$server->addPlugin(new \Sabre\DAV\Auth\Plugin($authBackend, 'WebDav'));
$server->addPlugin(new \Sabre\DAV\Browser\Plugin());
$server->addPlugin(new \Sabre\CalDAV\Plugin());
$server->addPlugin(new \Sabre\CardDAV\Plugin());
$server->addPlugin(new \Sabre\DAVACL\Plugin());

// The lock manager is reponsible for making sure users don't overwrite each others changes. Change 'data' to a different
// directory, if you're storing your data somewhere else.
// $lockBackend = new \Sabre\DAV\Locks\Backend\File('data/locks');
// $lockPlugin = new \Sabre\DAV\Locks\Plugin($lockBackend);
// $server->addPlugin($lockPlugin);

$server->exec();

?>
