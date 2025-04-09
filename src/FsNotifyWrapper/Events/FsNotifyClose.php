<?php

namespace Takuya\FsNotifyWrapper\Events;

use Takuya\FsNotifyWrapper\FsEventEnum;

class FsNotifyClose extends FanEvent {
  public const string NAME = FsEventEnum::CLOSE->name;
  public const int VALUE = FsEventEnum::CLOSE->value;
}