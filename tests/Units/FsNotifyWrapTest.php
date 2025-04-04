<?php

namespace Tests\PhpPlocateWrapper\Units;

use Tests\PhpPlocateWrapper\TestCase;
use function Takuya\Helpers\temp_dir;
use Takuya\FsNotifyWrapper\FsNotifyWrap;
use Takuya\FsNotifyWrapper\FsEventObserver;
use Takuya\FsNotifyWrapper\Events\FanEvent;
use function Takuya\Helpers\proc_fork;
use Takuya\SysV\IPCInfo;
use Takuya\PhpSysvMessageQueue\IPCMsgQueue;

class FsNotifyWrapTest extends TestCase {
  
  public function setUp (): void {
    $uniq_name = __FILE__.__METHOD__.time().'queue';
    $this->queue = new IPCMsgQueue(IPCInfo::ipc_key($uniq_name));
    $this->cpid = null;
  }
  
  public function tearDown (): void {
    $this->queue->destroy();
    posix_kill(-$this->cpid,2);
  }
  
  public function test_build_command_and_watch () {
    $dir = temp_dir();

    proc_fork( function($pid) use ($dir) {
      $this->queue->push($pid);
      $fsnotify = new FsNotifyWrap($dir);
      $fsnotify->timeout_sec = 5;
      $fsnotify->interval=0.01;
      $fsnotify->addObserver($observer = new FsEventObserver());
      $observer->watch(function(FanEvent $ev ){
        $this->queue->push($ev->getEventSource());
      });
      $fsnotify->listen();
      exit(0);
    } ,);
    $this->cpid = $this->queue->pop();
    //
    usleep(10*1000);
    $f_name = $dir.'/'.bin2hex(random_bytes(3)).'.txt';
    //
    touch($f_name);
    $mes = $this->queue->pop();
    $this->assertEquals($f_name,$mes->file);
    $this->assertEquals('CREATE',$mes->type);
    //
    unlink($f_name);
    $mes = $this->queue->pop();
    $this->assertEquals($f_name,$mes->file);
    $this->assertEquals('DELETE',$mes->type);
    //
    $this->assertEmpty($this->queue->all());
  }
}

