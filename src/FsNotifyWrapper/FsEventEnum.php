<?php

namespace Takuya\FsNotifyWrapper;

use Takuya\FsNotifyWrapper\Traits\EnumClass;
use Takuya\FsNotifyWrapper\Events\FanEvent;
use ReflectionClass;
use Takuya\FsNotifyWrapper\Traits\MapToEventClass;

/**
 * @see <linux/fanotify.h>
 */
enum FsEventEnum: int {
  use EnumClass;
  use MapToEventClass;
  
  case CREATE = 0x00000100;      /* Subfile was created */
  case DELETE = 0x00000200;      /* Subfile was deleted */
  case MOVED_FROM = 0x00000040;      /* File was moved from X */
  case MOVED_TO = 0x00000080;      /* File was moved to Y */
  case MODIFY = 0x00000002;      /* File was modified */
  case MOVED = ( self::MOVED_FROM->value | self::MOVED_TO->value ); /* moves */
  
  public static function mapToEvent ( $data ) {
    $class = FsEventEnum::events_with_names()[$data['type']];
    return new $class( (object)$data );
  }
  
  
}

