<?php

namespace Takuya\FsNotifyWrapper;


use Takuya\Event\EventEmitter;
use Takuya\Event\EventObserver;
use Takuya\Event\GenericEvent;

class FsEventEmitter {
  use EventEmitter;
  public function emit($data){
    $this->fireEvent( FsEventEnum::mapToEvent($data) );
  }
}