<?php

namespace Takuya\FsNotifyWrapper\Events;

use Takuya\FsNotifyWrapper\FsEventEnum;

class FsNotifyCloseWrite extends FanEvent {
  public const string NAME = FsEventEnum::CLOSE_WRITE->name;
  public const int VALUE = FsEventEnum::CLOSE_WRITE->value;
}