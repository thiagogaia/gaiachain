<?php

class Pow
{
  public static function hash($message) {
    return hash('sha256', $message);
  }

  public static function findNonce($message) {
    $nonce = 0;
    while(!self::isValidNonce($message, $nonce)) {
      ++$nonce;
    }
    return $nonce;
  }

  public static function isValidNonce($message, $nonce) {
    //difficulty is the number of zeros we want
    $zeros = '0000';
    return 0 === strpos(hash('sha256', $message.$nonce), $zeros);
  }
}

class Block
{
  public $previous;
  public $hash;
  public $message;

  public function __construct($message, ?Block $previous) {
    $this->previous = $previous ? $previous->hash : null;
    $this->message = $message;
    $this->mine();
  }

  public function mine() {
    $data = $this->message.$this->previous;
    $this->nonce = Pow::findNonce($data);
    $this->hash = Pow::hash($data.$this->nonce);
  }

  public function isValid(): bool
  {
    return Pow::isValidNonce($this->message.$this->previous, $this->nonce);
  }

  public function __toString(): string 
  {
    //var_dump($this->previous);
    //return '';
    return sprintf("Previous: %s\nNonce: %s\nHash: %s\nMessage: %s", $this->previous, $this->nonce, $this->hash, $this->message);
  }
}

class Blockchain
{
  public $blocks = [];

  public function __construct($message) {
    $this->blocks[] = new Block($message, null);
  }

  public function add($message) {
    $this->blocks[] = new Block($message, $this->blocks[count($this->blocks) - 1]);
  }

  public function isValid(): bool
  {
    foreach ($this->blocks as $i => $block) {
      if (!$block->isValid()) {
        return false;
      }

      if ($i != 0 && $this->blocks[$i-1]->hash != $block->previous) {
        return false;
      }
    }
    return true;
  }

  public function __toString() {
    return implode("\n\n", $this->blocks);
  }
}

$b = new Blockchain('bloco Genesis');
$b->add('outro bloco');
$b->add('de novo outro');
$b->add('Outra VEz');
print $b."\n";
var_export($b->isValid());