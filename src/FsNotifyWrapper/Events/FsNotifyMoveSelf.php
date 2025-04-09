<?php

namespace Takuya\FsNotifyWrapper\Events;

use Takuya\FsNotifyWrapper\FsEventEnum;

class FsNotifyMoveSelf extends FanEvent {
  public const string NAME = FsEventEnum::MOVE_SELF->name;
  public const int VALUE = FsEventEnum::MOVE_SELF->value;
}