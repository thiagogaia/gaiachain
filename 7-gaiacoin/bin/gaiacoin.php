<?php

require_once __DIR__.'/../src/Gossip.php';
require_once __DIR__.'/../src/Key.php';
require_once __DIR__.'/../src/Pki.php';
require_once __DIR__.'/../src/Blockchain.php';
require_once __DIR__.'/../src/Block.php';
require_once __DIR__.'/../src/Transaction.php';
require_once __DIR__.'/../src/Pow.php';
require_once __DIR__.'/../src/State.php';

$user = strtolower(getenv('USER'));
$port = (int) $argv[1];
$peerPort = isset($argv[2]) ? $argv[2] : null;
printf("Listening for %s on port %d\n", $user, $port);
if ($peerPort) {
  printf("Connecting to %d\n", $peerPort);
}

(new Gossip($user, $port, $peerPort))->loop();