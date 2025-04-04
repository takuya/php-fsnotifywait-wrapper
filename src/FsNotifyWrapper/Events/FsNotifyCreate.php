<?php

namespace Takuya\FsNotifyWrapper\Events;

use Takuya\FsNotifyWrapper\FsEventEnum;

class FsNotifyCreate extends FanEvent {
  public const string NAME = FsEventEnum::CREATE->name;
  public const int VALUE = FsEventEnum::CREATE->value;
  
  public function __construct ( mixed $var ) {
    parent::__construct( (object)$var );
  }
  
}