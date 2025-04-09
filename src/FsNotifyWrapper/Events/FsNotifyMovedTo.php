<?php

namespace Takuya\FsNotifyWrapper\Events;

use Takuya\FsNotifyWrapper\FsEventEnum;

class FsNotifyMovedTo extends FanEvent {
  public const string NAME = FsEventEnum::MOVED_TO->name;
  public const int VALUE = FsEventEnum::MOVED_TO->value;
}

