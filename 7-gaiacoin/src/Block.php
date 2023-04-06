<?php

class Block
{
  public $previous;
  public $nonce;
  public $hash;
  public $transaction;

  public function __construct(Transaction $transaction, ?self $previous) {
    $this->previous = $previous ? $previous->hash : null;
    $this->transaction = $transaction;
    $this->mine();
  }

  public static function createGenesis(string $pubKey, string $privKey, int $amount) {
    return new self(new Transaction(null, $pubKey, $amount, $privKey), null);
  }

  public function mine() {
    $data = $this->transaction->message().$this->previous;
    $this->nonce = Pow::findNonce($data);
    $this->hash = Pow::hash($data.$this->nonce);
  }

  public function isValid(): bool
  {
    return Pow::isValidNonce($this->transaction->message().$this->previous, $this->nonce) && $this->transaction->isValid();
  }

  public function __toString(): string 
  {
    return sprintf("%s\n %s -- %s -- %s", $this->transaction, $this->nonce, substr($this->hash, 0, 10), substr($this->previous, 0, 10) ?? 'NONE');
  }
}