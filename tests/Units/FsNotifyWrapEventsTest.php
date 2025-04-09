<?php

namespace Tests\FsNotifyWrapperTest\Units;

use Tests\FsNotifyWrapperTest\TestCase;
use function Takuya\Helpers\temp_dir;
use Takuya\FsNotifyWrapper\FsNotifyWrap;
use Takuya\FsNotifyWrapper\FsEventObserver;
use Takuya\FsNotifyWrapper\Events\FanEvent;
use function Takuya\Helpers\proc_fork;
use Takuya\SysV\IPCInfo;
use Takuya\PhpSysvMessageQueue\IPCMsgQueue;
use Takuya\FsNotifyWrapper\Events\FsNotifyDelete;
use Takuya\FsNotifyWrapper\Events\FsNotifyCreate;
use Takuya\FsNotifyWrapper\Events\FsNotifyClose;
use Takuya\ProcOpen\ProcOpen;
use function Symfony\Component\String\u;
use function Takuya\Helpers\str_rand;

class FsNotifyWrapEventsTest extends TestCase {
  
  public function setUp (): void {
    $name = str_rand(20);
    $this->event_q = new IPCMsgQueue( IPCInfo::ipc_key( $name.'-event-queue') );
    $this->pid_q = new IPCMsgQueue( IPCInfo::ipc_key( $name.'-pid-queue') );
    $this->cpid = null;
    $this->dir = $dir = temp_dir();
  }
  
  public function startWatch ( $observer, $events = 'create,delete,move' ) {
    $dir = $this->dir;
    $pid = proc_fork( function( $pid ) use ( $dir, $observer, $events ) {
      $this->pid_q->push( $pid );
      $fsnotify = new FsNotifyWrap( $dir );
      $fsnotify->events = $events;
      $fsnotify->timeout_sec = 1;
      $fsnotify->interval = 0.01;
      $fsnotify->addObserver( $observer );
      $fsnotify->listen();
      exit( 0 );
    } );
    //ensure forked.
    $this->cpid = $this->pid_q->pop();
    while ( ProcOpen::ps_stat( $this->cpid, 'S+' ) == false ) {
      usleep( 10 );
    }
  }
  
  public function tearDown (): void {
    $this->event_q->destroy();
    $this->pid_q->destroy();
    $this->kill_forked($this->cpid);
  }
  
  public function test_build_command_and_watch_delete () {
    $dir = $this->dir;
    $observer = new FsEventObserver();
    $observer->addEventListener( FsNotifyDelete::class, function( FanEvent $ev ) {
      $this->event_q->push( $ev->getEventSource() );
    } );
    $this->startWatch( $observer );
    //
    $f_name = $dir.'/'.bin2hex( random_bytes( 3 ) ).'.txt';
    touch( $f_name );
    usleep( 10 );
    unlink( $f_name );
    $mes = $this->event_q->pop();
    $this->assertEquals( $f_name, $mes->file );
    $this->assertEquals( 'DELETE', $mes->type );
    ////
    $this->assertEmpty( $this->event_q->all() );
  }
  
  public function test_build_command_and_watch_create () {
    $dir = $this->dir;
    $observer = new FsEventObserver();
    $observer->addEventListener( FsNotifyCreate::class, function( FanEvent $ev ) {
      $this->event_q->push( $ev->getEventSource() );
    } );
    $this->startWatch( $observer );
    //
    $f_name = $dir.'/'.bin2hex( random_bytes( 3 ) ).'.txt';
    touch( $f_name );
    $mes = $this->event_q->pop();
    $this->assertEquals( $f_name, $mes->file );
    $this->assertEquals( 'CREATE', $mes->type );
    ////
    $this->assertEmpty( $this->event_q->all() );
  }
  
  public function test_build_command_and_watch_close () {
    $dir = $this->dir;
    $observer = new FsEventObserver();
    $observer->addEventListener( FsNotifyClose::class, function( FanEvent $ev ) {
      $this->event_q->push( $ev->getEventSource() );
    } );
    $this->startWatch( $observer, 'close' );
    //
    $f_name = $dir.'/'.bin2hex( random_bytes( 3 ) ).'.txt';
    file_put_contents( $f_name, random_bytes( 1 ) );
    $mes = $this->event_q->pop();
    $this->assertEquals( $f_name, $mes->file );
    $this->assertEquals( 'CLOSE', $mes->type );
    ////
    $this->assertEmpty( $this->event_q->all() );
  }
  
  public function test_build_command_and_watch_rename () {
    $dir = $this->dir;
    $observer = new FsEventObserver();
    $observer->watch( function( FanEvent $ev ) {
      $this->event_q->push( $ev->getEventSource() );
    } );
    $this->startWatch( $observer, 'move' );
    //
    $f_name = $dir.'/'.bin2hex( random_bytes( 3 ) ).'.txt';
    $moved_name = $dir.'/'.bin2hex( random_bytes( 3 ) ).'.txt';
    file_put_contents( $f_name, random_bytes( 1 ) );
    usleep( 1 );
    rename( $f_name, $moved_name );
    $mes = $this->event_q->pop();
    $this->assertEquals( $f_name, $mes->file );
    $this->assertEquals( 'MOVED_FROM', $mes->type );
    ////
    $mes = $this->event_q->pop();
    $this->assertEquals( $moved_name, $mes->file );
    $this->assertEquals( 'MOVED_TO', $mes->type );
    //
    $this->assertEmpty( $this->event_q->all() );
  }
  
  public function test_build_command_and_watch_modify () {
    $dir = $this->dir;
    $observer = new FsEventObserver();
    $observer->watch( function( FanEvent $ev ) {
      $this->event_q->push( $ev->getEventSource() );
    } );
    $this->startWatch( $observer, 'create,modify,close' );
    $f_name = $dir.'/'.bin2hex( random_bytes( 3 ) ).'.txt';
    touch( $f_name );
    usleep( 1 );
    $mess[] = $this->event_q->pop();// CREATE
    $mess[] = $this->event_q->pop();// CLOSE_WRITE
    $mess[] = $this->event_q->pop();// CLOSE
    $fp = fopen( $f_name, 'w+' );
    
    foreach ( range( 1, $cnt = 3 ) as $idx ) {
      fwrite( $fp, random_bytes( 64 ) );
      fflush( $fp );
      $mess[] = $this->event_q->pop();// MODIFY
    }
    fclose( $fp );
    $mess[] = $this->event_q->pop();// CLOSE_WRITE
    $mess[] = $this->event_q->pop();// CLOSE
    
    $this->assertEquals(0,$this->event_q->size());
    $this->assertEquals([],$this->event_q->all());
    $this->assertEquals([
      'CREATE',
      'CLOSE_WRITE',
      'CLOSE',
      'MODIFY',
      'MODIFY',
      'MODIFY',
      'CLOSE_WRITE',
      'CLOSE',
    ],array_map(fn($e)=>$e->type,$mess));
  }
}

