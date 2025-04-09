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

class FsNotifyWrapTimeoutTest extends TestCase {
  
  public function setUp (): void {
    $name = str_rand(20);
    $this->event_q = new IPCMsgQueue( IPCInfo::ipc_key( $name.'-event-queue') );
    $this->pid_q = new IPCMsgQueue( IPCInfo::ipc_key( $name.'-pid-queue') );
    $this->cpid = null;
    $this->dir = $dir = temp_dir();
  }
  public function startWatch($timeout,$events,$exit_code){
    $dir = $this->dir;
    $pid = proc_fork( function($pid) use ($timeout,$dir,$events,$exit_code) {
      $this->pid_q->push($pid);
      $fsnotify = new FsNotifyWrap($dir);
      $fsnotify->events = $events;
      $fsnotify->timeout_sec = $timeout;
      $fsnotify->interval=0.1;
      $fsnotify->addObserver($observer = new FsEventObserver());
      $observer->watch(fn($ev)=>var_dump($ev));
      $fsnotify->listen();
      exit($exit_code);
    } );
    $pid==$this->pid_q->pop() && $this->cpid=$pid;
    while ( ProcOpen::ps_stat( $this->cpid, 'S+' ) == false ) {
      usleep( 10 );
    }
    pcntl_wait($st);
    /**
     * 一般的な慣習では、子プロセスがシグナル N で終了した場合、
     * pcntl_wait は N * 256 を返す
     * この場合、SIGINT (シグナル番号 2) であれば 2 * 256 = 512 となります
     */
    return $st;
  }
  
  public function tearDown (): void {
    $this->event_q->destroy();
    $this->pid_q->destroy();
    $this->kill_forked($this->cpid);
  }
  public function test_process_wait_for_timeout(){
    $timeout_sec = random_int(100,500)/1000;
    $expected_exit_code = random_int(1,255);
    $start_at = microtime(true);
    $forked_exit_status = $this->startWatch($timeout_sec,'create',$expected_exit_code);
    $this->assertGreaterThan($timeout_sec,microtime(true)-$start_at);
    $this->assertEquals($expected_exit_code,$forked_exit_status/256);
  }
}

