<?php

class Gossip
{
  private $name;
  private $key;
  private $state;
  private $port;

  public function __construct(string $name, int $port, ?int $peerPort) {
    $this->name = $name;
    $this->port = $port;
    $this->key = new Key($name);
    $peers = [$port => true];
    if (!$peerPort) {
      $blockchain = new Blockchain($this->key->pubKey, $this->key->privKey, 1000000);
    } else {
      $blockchain = null;
      $peers[$peerPort] = true;
    }
    $this->state = new State($name, $blockchain, $peers);
  }

  public function loop() 
  {
    while (true) {
      print "\x1b[100A\x1b[0J\033[37;40m Network \033[39;49m\n";
      foreach (array_keys($this->state->peers) as $port) {
        if ($this->port == $port) {
          echo "port igual".PHP_EOL;
          continue;
        }

        printf("  Gossip with %d\n", $port);
        $this->withPeer($port);
      }
      $this->displayState();
      $this->state->reload();
      usleep(rand(30000, 3000000));
    }

    
  }

  public function withPeer($port) {
    $peerState = $this->gossip($port);
    if (!$peerState) {
      unset($this->state->peers[$port]);
      $this->state->save();
    } else {
      $this->state->update($peerState);
    }
  }

  public function displayState() {
    echo (!empty($this->state->blockchain) ? $this->state->blockchain->balancesAsString() : 'Balances');
  }

  

  private function gossip($port): ?State
  {
    $data = base64_encode(serialize($this->state));
    // exit('eita');
    $peerState = @file_get_contents('http://localhost:'.$p.'/gossip', null, stream_context_create([
      'http' => [
        'method' => 'POST',
        'header' => 'Content-type: application/json\r\nContent-length: '.strlen($data)."\r\n",
        'content' => $data
      ]
    ]));
    exit('asdf');
    $state = null;
    var_dump($peerState); die;
    if ($peerState) {
      $state = unserialize(base64_decode($peerState));
      if (!$state instanceof State) {
        var_dump($state);
        echo 'nao é uma instancia de state'.PHP_EOL;
        $state = null;
      }
    }
    return $state;
  }
}
