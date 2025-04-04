<?php

namespace Takuya\FsNotifyWrapper\Events;

use Takuya\FsNotifyWrapper\FsEventEnum;

class FsNotifyMoved extends FanEvent {
  public const string NAME = FsEventEnum::MOVED->name;
  public const int VALUE  = FsEventEnum::MOVED->value;
}