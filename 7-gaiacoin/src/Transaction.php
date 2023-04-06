<?php

class Transaction
{
  public $from;
  public $to;
  public $amount;
  public $signature;

  public function __construct(?string $from, string $to, int $amount, string $privKey) {
    $this->from = $from;
    $this->to = $to;
    $this->amount = $amount;
    $this->signature = Pki::encrypt($this->message(), $privKey);
  }

  public function message() {
    return Pow::hash($this->from.$this->to.$this->amount);
  }

  public function __toString() {
    return ($this->from ? substr($this->from, 72, 7) : 'NONE').' -> '.substr($this->to, 72, 7).': '.substr($this->amount, 72, 7);
  }

  public function isValid() {
    return !$this->from || Pki::isValid($this->message(), $this->signature, $this->from);
  }
}
