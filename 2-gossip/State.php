<?php

class State
{
  public $state;

  private $file;
  private $user;
  private $port;
  private $peerPort;
  private $sessions;

  public function __construct($user, $port = null, $peerPort = null ) 
  {
    $this->user = $user;
    $this->port = $port;
    $this->peerPort = $peerPort;
    $this->sessions = explode("\n", file_get_contents(__DIR__ . '/data/sessions.txt'));
    $this->file = __DIR__ . '/data/' . $user . '.json';
    if ($this->peerPort && !isset($this->state[$this->peerPort])) {
      echo 'peerPort: '.$this->peerPort.PHP_EOL;
      $this->state[$this->peerPort] = ['user' => '', 'session' => '', 'version' => 0];
    }

    if ($this->port && !isset($this->state[$this->port])) {
      echo 'port: '.$this->port.PHP_EOL;
      $this->updateMine();
    }

    $this->reload();
  }

  public function save() {
    file_put_contents($this->file, json_encode($this->state));
  }

  public function loop() 
  {
    $i = 0;
    while (true) {
      printf("\033[37;40m Current state \033[39;49m\n%s\n", $this);
      foreach ($this->state as $p => $data) {
        if ($p == $this->port) {
          /* echo 'this port: '.$this->port.PHP_EOL;
          echo 'p: '.$p.PHP_EOL;
          echo 'p é igual ao this->port'.PHP_EOL;
          var_dump($this->state).PHP_EOL; 
          $this->updateMine();*/
          continue;
        }

        $data = json_encode($this->state);
        $peerState = @file_get_contents('http://localhost:'.$p.'/gossip', null, stream_context_create([
          'http' => [
            'method' => 'POST',
            'header' => 'Content-type: application/json\r\nContent-length: '.strlen($data).'\r\n',
            'content' => $data
          ]
        ]));
        if (!$peerState) {
          unset($this->state[$p]);
          // echo 'salvando'.PHP_EOL;
          $this->save();
        } else {
          // echo 'editando'.PHP_EOL;
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
  }

  public function reload() {
    $this->state = file_exists($this->file) ? json_decode(file_get_contents($this->file), true) : [];
  }

  public function updateMine() {
    echo 'updateMine'.PHP_EOL;
    $session = $this->randomSession();
    $version = $this->incrementVersion();
    $this->state[$this->port] = [
      'user' => $this->user,
      'session' => $session,
      'version' => $version
    ];
    $this->save();
  }

  public function randomSession() {
    return md5(uniqid(rand(), true));
  }

  public function incrementVersion() {
    if (isset($this->state[$this->port]['version'])) {
      return $this->state[$this->port]['version']+1;
    }
    // var_dump($this->state).PHP_EOL;
    return 1;
  }

  public function update($state) {
    if (!$state) {
      return;
    }

    foreach ($state as $port => $data) {
      if ($port == $this->port) {
        continue;
      }

      if (!isset($data['user']) || !isset($data['version']) || !isset($data['session'])) {
        continue;
      }

      if (!isset($this->state[$port]) || $data['version'] > $this->state[$port]['version']) {
        $this->state[$port] = $data;
      }
    }
    $this->save();
  }

  public function __toString() {
    $data = [];
    foreach($this->state as $port => $d) {
      $data[] = sprintf("%s/%s -- %d/%s", $port, $d['user'], $d['version'], substr($d['session'], 0, 40));
    }
    return implode("\n", $data);
  }
}
