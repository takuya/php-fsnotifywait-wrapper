<?php

namespace Takuya\FsNotifyWrapper\Traits;

use UnexpectedValueException;

trait EnumClass {
  public static function names (): array {
    return array_column( static::cases(), 'name' );
  }
  
  public static function tryFromName ( $name ) {
    try {
      return self::fromName( $name );
    } catch (UnexpectedValueException $e) {
      return null;
    }
  }
  
  protected static function find ( $val, $prop ) {
    $found = array_filter( static::cases(), fn( $e ) => $e->{$prop} == $val );
    if ( sizeof( $found ) != 1 ) {
      throw new UnexpectedValueException( $val );
    }
    return $found[0];
  }
  
  public static function fromValue ( $value ) {
    return static::find( $value, 'value' );
  }
  
  public static function fromName ( $name ) {
    return static::find( $name, 'name' );
  }
  
  public static function values (): array {
    return array_column( self::cases(), 'value' );
  }
  
}