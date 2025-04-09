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
  case CLOSE  = (self::CLOSE_WRITE->value | self::CLOSE_NOWRITE->value); /* close */
  case MOVE_SELF  = 0x00000800;  /* Self was moved */ // A watched file or directory was moved.
  case CLOSE_WRITE = 0x00000008;/* Writtable file closed */ //  A file that was opened for writing (O_WRONLY or O_RDWR) was closed.
  case CLOSE_NOWRITE = 0x00000010;  /* Unwrittable file closed */ //A file or directory that was opened read-only (O_RDONLY)was closed.
  
  public static function mapToEvent ( $data ) {
    $class = FsEventEnum::events_with_names()[$data['type']];
    return new $class( (object)$data );
  }
  
  
}

