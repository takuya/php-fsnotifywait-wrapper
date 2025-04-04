<?php

namespace Takuya\FsNotifyWrapper\Events;

use Takuya\FsNotifyWrapper\FsEventEnum;

class FsNotifyMovedFrom extends FanEvent {
  public const string NAME = FsEventEnum::MOVED_FROM->name;
  public const int VALUE = FsEventEnum::MOVED_FROM->value;
}