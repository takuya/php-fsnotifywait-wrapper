<?php

namespace Takuya\FsNotifyWrapper\Events;

use Takuya\FsNotifyWrapper\FsEventEnum;

class FsNotifyDelete extends FanEvent {
  public const string NAME = FsEventEnum::DELETE->name;
  public const int VALUE = FsEventEnum::DELETE->value;
}