<?php
require_once __DIR__.'/src/Gossip.php';
require_once __DIR__.'/src/Key.php';
require_once __DIR__.'/src/Pki.php';
require_once __DIR__.'/src/Blockchain.php';
require_once __DIR__.'/src/Block.php';
require_once __DIR__.'/src/Transaction.php';
require_once __DIR__.'/src/Pow.php';
require_once __DIR__.'/src/State.php';

if('/gossip' == $_SERVER['PATH_INFO'] && 'POST' == $_SERVER['REQUEST_METHOD']) {
  exit('a');
  $state = new State(strtolower(getenv('USER')));
  $state->update(json_decode(file_get_contents('php://input'), true));
  print json_encode($state->state);
  return;
}

if ('/transfer'  == $_SERVER['PATH_INFO'] && 'POST' == $_SERVER['REQUEST_METHOD']) {
  $to = $_POST['to'];
  $portTo = $_POST['from'];
  $amount = $_POST['amount'];
  require_once __DIR__.'/src/Key.php';
  // exit('asdf');
  $key = Key::load('noob');
  exit(var_dump($key));
  exit(var_dump($key->privKey, $key->pubKey));
  require_once __DIR__.'/src/Blockchain.php';
  $bc = new Blockchain();
  $from = strtolower($_POST['from']);
  if (!isset($balances[$from])) {
    http_response_code(404);
    print 'from não encontrado.';
    return;
  }

  $to = strtolower($_POST['to']);
  if (!isset($balances[$to])) {
    http_response_code(404);
    print 'to não encontrado.';
    return;
  }

  $amount = (int) $_POST['amount'];
  if ($amount > $balances[$from]) {
    http_response_code(404);
    print 'sem saldo.';
    return;
  }

  $balances[$from] -= $amount;
  $balances[$to] += $amount;
  file_put_contents($db, json_encode($balances));
  print 'OK';
  return;
}