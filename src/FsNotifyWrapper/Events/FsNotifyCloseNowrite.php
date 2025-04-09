<?php

namespace Takuya\FsNotifyWrapper\Events;

use Takuya\FsNotifyWrapper\FsEventEnum;

class FsNotifyCloseNowrite extends FanEvent {
  public const string NAME = FsEventEnum::CLOSE_NOWRITE->name;
  public const int VALUE = FsEventEnum::CLOSE_NOWRITE->value;
}