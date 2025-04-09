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

class FsNotifyStoppingTest extends TestCase {
  
  public function setUp (): void {
  
    $name = str_rand(20);
    $this->event_q = new IPCMsgQueue( IPCInfo::ipc_key( $name.'-event-queue') );
    $this->pid_q = new IPCMsgQueue( IPCInfo::ipc_key( $name.'-pid-queue') );
  
    $this->cpid = null;
    $this->dir = $dir = temp_dir();
  }
  public function startWatch($timeout,$events){
    $dir = $this->dir;
    $pid = proc_fork( function($pid) use ($timeout,$dir,$events) {
      $this->pid_q->push($pid);
      $fsnotify = new FsNotifyWrap($dir);
      $fsnotify->events = $events;
      $fsnotify->timeout_sec = $timeout;
      $fsnotify->interval=0.01;
      $fsnotify->addObserver($observer = new FsEventObserver());
      $observer->watch(fn($ev)=>var_dump($ev));
      $fsnotify->listen();
      exit(0);
    } );
  
    $this->cpid = $this->pid_q->pop();
    while(ProcOpen::ps_stat($this->cpid,'S+')==false){
      usleep(10);
    }
    return $pid;
  }
  
  public function tearDown (): void {
    $this->event_q->destroy();
    $this->pid_q->destroy();
  }
  public function test_stop_fsnofifywait_from_php(){
    $timeout_sec = 10;
    $cpid = $this->startWatch($timeout_sec,'create');
    $this->assertProcessAlive($cpid);
    $this->kill_forked($cpid);
    dump($this->list_pid($this->cpid));
    dump($this->list_pid($cpid));
    $this->assertEmpty($this->list_pid($this->cpid));
    $this->assertEmpty($this->list_pid($cpid));
    
  }
}

