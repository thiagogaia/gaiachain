<?php

$db = 'data/balances.json';
if (file_exists($db)) {
  $balances = json_decode(file_get_contents($db), true);
} else {
  $balances = ['gaia' => 1000000];
  file_put_contents($db, json_encode($balances));
}
if ('/balance' == $_SERVER['PATH_INFO']) {
  if (!isset($_GET['user'])) {
    http_response_code(404);
    print "Favor Digitar o usuário";
    return;
  }
  $user = strtolower($_GET['user']);
  printf("Usuário %s tem %d gaiacoins.", $user, $balances[$user] ?? 0);
  return;
}

/**
 * Quando insere usuário, ele tem 0 gaiacoins
 */
if ('/users' == $_SERVER['PATH_INFO'] && 'POST' == $_SERVER['REQUEST_METHOD']) {
  $user = strtolower($_POST['user']);
  if (isset($balances[$user])) {
    http_response_code(404);
    print 'não pode inserir um usuário já registrado.';
    return;
  }

  $balances[$user] = 0;
  file_put_contents($db, json_encode($balances));
  print 'OK';
  return;
}

if ('/transfer'  == $_SERVER['PATH_INFO'] && 'POST' == $_SERVER['REQUEST_METHOD']) {
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