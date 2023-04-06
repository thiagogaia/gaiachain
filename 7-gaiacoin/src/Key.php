<?php

class Key
{
  public $name;
  public $privKey;
  public $pubKey;

  public function __construct($name) {
    $this->name = $name;
    [$this->privKey, $this->pubKey] = Pki::generateKeyPair();
    $this->save();
  }

  public function save() {
    file_put_contents(self::file($this->name), serialize($this));
  }

  public static function load($name):self
  {
    return unserialize(file_get_contents(self::file($name)));
  }

  private static function file($name) {
    return __DIR__.'/../data/'.$name.'.key';
  }
}
