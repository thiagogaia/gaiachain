<?php

class State
{
  public $name;
  public $peers;
  public $blockchain;

  public function __construct($name, ?Blockchain $blockchain, array $peers = []) 
  {
    $this->name = $name;
    $this->blockchain = $blockchain;
    $this->peers = $peers;
    $this->save();
  }

  public function save() {
    file_put_contents(self::file($this->name), serialize($this));
  }

  public function load($name): self
  {
    return unserialize(file_get_contents(self::file($name)));
  }

  private static function file($name) {
    return __DIR__.'/../data/'.$name.'.key';
  }

  /* public function loop() 
  {
    $i = 0;
    while (true) {
      printf("\033[37;40m Current state \033[39;49m\n%s\n", $this);
      foreach ($this->state as $p => $data) {
        if ($p == $this->port) {
          continue;
        }

        $data = json_encode($this->state);
        $peerState = @file_get_contents('http://localhost:'.$p.'/gossip', null);
        if (!$peerState) {
          unset($this->state[$p]);
          $this->save();
        } else {
          $this->update(json_decode($peerState, true));
        }
      }
      $this->reload();
      usleep(rand(30000, 3000000));
      if (++$i % 2) {
        $this->updateMine();
        printf("\033[37;40m Fav session updated \033[39;49m\n");
      }
    }
  } */

  public function updateMine() {
    $session = $this->randomSession();
    $version = $this->incrementVersion();
    $this->state[$this->port] = ['user' => $this->user, 'session' => $session, 'version' => $version];
  }

  public function update(State $state) {
    if ($this->blockchain) {
      $this->blockchain->update($state->blockchain);
    } else {
      $this->blockchain = $state->blockchain;
    }

    foreach (array_keys($state->peers) as $peer) {
      $this->peers[$peer] = true;
    }

    $this->save();
  }

  public function reload() {
    if ($state = self::load($this->name)) {
      $this->blockchain = $state->blockchain;
      $this->peers = $state->peers;
    }
  }

  public function __toString() {
    $data = [];
    foreach($this->state as $port => $d) {
      $data[] = sprintf("%s/%s -- %d/%s", $port, $d['user'], $d['version'], substr($d['session'], 0, 40));
    }
    return implode("\n", $data);    
  }
}
