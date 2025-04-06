<?php

namespace Takuya\FsNotifyWrapper;

use Takuya\ProcessExec\ProcessExecutor;
use Takuya\ProcessExec\ExecArgStruct;
use Takuya\Event\EventObserver;
use Takuya\ProcessExec\ProcessObserver;
use Takuya\ProcessExec\ProcessEvents\Events\ProcessStarted;
use Takuya\ProcessExec\ProcessEvents\ProcessEvent;
use Takuya\ProcessExec\ProcessEvents\Events\ProcessRunning;
use Takuya\ProcessExec\ProcessEvents\Events\ProcessErrorOccurred;


class FsNotifyWrap {
  protected string $cmd = 'fsnotifywait';
  protected string $time_format = '%F %T';
  protected string $output_format = '{"time":"%T","type":"%e"}:%w%f';
  protected array $opts = ['-q', '-m', '-r', '-F'];
  protected FsEventEmitter $emitter;
  protected \Closure $on_change;
  protected ProcessExecutor $proc;
  
  public function __construct (
    public string $target,
    public string $events = 'create,delete,move,modify',
    public float  $interval = 1,
    public int    $timeout_sec = 10
  ) {
    $this->emitter = new FsEventEmitter();
    $this->on_change = function( $data ) {
      $this->emitter->emit( $data );
    };
  }
  public function addObserver(FsEventObserver $observer){
    $this->emitter->addObserver($observer);
  }
  
  public function setOnChange ( $fn ) {
    $this->on_change = $fn;
  }
  public function addOpts($opt){
    $this->opts[] = $opt;
  }
  
  protected function parse ( $line ) {
    preg_match( '/^(?<json>\{.+?}):(?<file>.+)$/', $line, $m );
    [$json_str, $file] = [$m['json'] ?? '{}', $m['file'] ?? ''];
    $ev = json_decode( $json_str );
    $file = preg_replace('|\(deleted\)$|','',$file);
    $file = preg_replace('|\(deleted\)/|','',$file);
    $ev->type = preg_replace('|,ISDIR|','',$ev->type);
    $events = [];
    foreach (explode(',',$ev->type) as $type){
      $events[]=[
        'time' => $ev->time,
        'type' => $type,
        'file' => $file,
      ];
    }
      return $events;
  }
  
  protected function changed ( string $line ): void {
    $ret = $this->parse( $line );
    foreach ( $ret as $ev ) {
      call_user_func( $this->on_change, $ev );
    }
  }
  
  protected function addTimeOut () {
    $observer = new ProcessObserver();
    $start_at = time();
    $observer->addEventListener( ProcessStarted::class, function() use ( &$start_at ) { $start_at = time(); } );
    $observer->addEventListener( ProcessRunning::class, function( ProcessEvent $ev ) use ( $start_at ) {
      if ( time() > $start_at + $this->timeout_sec ) {
        $ev->getExecutor()->signal( 2 );
      }
    } );
    return $observer;
  }
  
  protected function fsnotifyCommand (): array {
    return [
      'fsnotifywait',
      ...$this->opts,
      '--format', $this->output_format,
      '--timefmt', $this->time_format,
      '-e', $this->events,
      $this->target,
    ];
  }
  
  public function stop () {
    !empty( $this->proc ) && $this->proc->getProcess()->info->running && $this->proc->stop();
  }
  public function pid(){
    return $this->proc->getProcess()->info->pid;
  }
  
  public function listen () {
    $proc = $this->proc = new ProcessExecutor( new ExecArgStruct( $this->fsnotifyCommand() ) );
    $proc->watch_interval = $this->interval;
    $this->timeout_sec && $proc->addObserver( $this->addTimeOut() );
    $proc->onStdout( fn( $line ) => $this->changed( $line ) );
    $proc->start();
    //
    fwrite(STDERR,$proc->getErrout());
  }
}