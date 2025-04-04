<?php

namespace Tests\PhpPlocateWrapper\Units;

use Tests\PhpPlocateWrapper\TestCase;
use Takuya\FsNotifyWrapper\LocateWrap;
use Takuya\FsNotifyWrapper\LocateDbBuilder;
use function Takuya\Helpers\str_rand;
use Takuya\FsNotifyWrapper\FsNotifyWrap;
use Takuya\FsNotifyWrapper\FsEventObserver;
use Takuya\FsNotifyWrapper\Events\FsNotifyCreate;
use Takuya\FsNotifyWrapper\FsEventEmitter;
use Takuya\FsNotifyWrapper\Events\FanEvent;
use Takuya\FsNotifyWrapper\FsEventEnum;

class FsEventEmitTest extends TestCase {
  
  public function setUp (): void {
  }
  
  public function tearDown (): void {
  }
  
  public function test_emit_event () {
    $data = [
      "time" => "2025-04-03 15:14:08",
      "type"  => "CREATE",
      "file" => "/opt/work/sample/sub/a.txt",
    ];
    $observer = new FsEventObserver();
    $observer->addEventListener( FsNotifyCreate::class, function( FanEvent $ev ) use($data) {
      $this->assertEquals($data['time'], $ev->getEventSource()->time);
      $this->assertEquals($data['file'], $ev->getEventSource()->file);
      $this->assertEquals($data['type'], $ev->getEventSource()->type);
    } );
    //
    $emitter = new FsEventEmitter();
    $emitter->addObserver( $observer );
    $emitter->fireEvent( FsEventEnum::mapToEvent($data) );
  }
}

