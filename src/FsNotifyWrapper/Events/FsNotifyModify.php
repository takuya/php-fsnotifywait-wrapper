<?php

namespace Takuya\FsNotifyWrapper\Events;

use Takuya\FsNotifyWrapper\FsEventEnum;

class FsNotifyModify extends FanEvent {
  public const string NAME = FsEventEnum::MODIFY->name;
  public const int VALUE  = FsEventEnum::MODIFY->value;
}