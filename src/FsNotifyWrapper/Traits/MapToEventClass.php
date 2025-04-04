<?php

namespace Takuya\FsNotifyWrapper\Traits;

use Takuya\FsNotifyWrapper\Events\FanEvent;
use ReflectionClass;

trait MapToEventClass {
  public static function eventClass ( $name ): string {
    $reflectionClass = new ReflectionClass( FanEvent::class );
    $namespace = $reflectionClass->getNamespaceName();
    $snake = static::snakeToCamelCase( $name );
    $class = sprintf( '%s\FsNotify%s', $namespace, $snake );
    return $class;
  }
  
  private static function snakeToCamelCase ( string $snakeCase ): string {
    return implode( '',
      array_map( 'ucfirst',
        array_map( 'strtolower',
          explode( '_', $snakeCase ) ) ) );
  }
  
  public static function events () {
    return array_map( fn( $e ) => static::eventClass( $e ), static::names() );
  }
  public static function events_with_names () {
    return array_reduce(array_map( fn( $e ) => [static::eventClass( $e ),$e], static::names() ), function($carry,$e){
      $carry[$e[1]] = $e[0];
      return $carry;
    },[]);
  }
  
}