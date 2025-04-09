<?php

namespace Takuya\Helpers;

if ( !function_exists( 'process_exists' ) ) {
  function process_exists ( $pid ): bool {
    return posix_kill( $pid, 0 );
  }
}