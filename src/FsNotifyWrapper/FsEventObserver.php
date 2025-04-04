<?php

namespace Takuya\FsNotifyWrapper;


use Takuya\FsNotifyWrapper\Events\FanEvent;
use Takuya\Event\EventObserver;

class FsEventObserver extends EventObserver {
  public function __construct () {
    $this->events = FsEventEnum::events();
    parent::__construct();
  }
  public function catchAll(callable $fn){
    foreach ($this->events as $class ){
      $this->addEventListener($class,$fn);
    }
  }
  public function watch(callable $fn){
    $this->catchAll($fn);
  }
}
