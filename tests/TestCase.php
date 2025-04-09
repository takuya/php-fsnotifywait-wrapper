<?php


namespace Tests\FsNotifyWrapperTest;

use PHPUnit\Framework\TestCase as BaseTestCase;
use function Takuya\Helpers\process_exists;

abstract class TestCase extends BaseTestCase {
  public function list_pid($ppid){
    $ret = (`ps  -o pid -h --ppid $ppid`);
    $ret = trim($ret);
    $ret = preg_split('/\n/',$ret);
    $ret = array_map('trim',$ret);
    $ret = array_filter($ret);
    $ret = array_map('intval',$ret);
    return $ret;
  }
  public function kill_proc($pid){
    while(process_exists($pid)){
      posix_kill($pid,2);
      usleep(1000);
    }
  }
  public function kill_forked($ppid){
    foreach ( $this->list_pid($ppid) as $pid ) {
      process_exists($pid) && $this->kill_proc($pid);
    }
  }
  public function assertProcessAlive($pid){
    $this->assertTrue(posix_kill($pid,0));
  }

}
