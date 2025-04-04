## php fsnotify wrapper

Utilize `fsnotifywait` command.
```php
<?php
$fsnotify = new FsNotifyWrap('/etc');
$fsnotify->addObserver($observer = new FsEventObserver());
$observer->watch(function(FanEvent $ev ){
  $this->queue->push($ev->getEventSource());
});
$fsnotify->listen();
```

Watch delete event
```php
$fsnotify = new FsNotifyWrap('/etc');
$fsnotify->addObserver($observer = new FsEventObserver());
$observer->addEventListener(
   FsNotifyDelete:class,
  fn($ev)=>dump($ev->getEventSource());
);
$fsnotify->listen();
```

EventSource is a simple object like This.
```php
{#372
  +"time": "2025-04-04 23:50:31"
  +"type": "CREATE"
  +"file": "/tmp/php-tmpdir-gUEKhP8s/sample.txt"
}
```


## about fsnotifywait

`fsnotifywait` is added Linux Command. That can watch file changed by `fanotify` API

`fanotify` is added Linux Kernel 5.10.

Compare `inotifywatch`, `fsnotifywait` uses FAN( fanotify API not inotify).

`fanotify` API can watch arbitrarily chosen dir, and recursively too many file in it which inotify cannot watch.


```shell
fsnotifywait -q --format '#%w%f %e ::::' -m -r -S -e create,delete,move,modify /mnt/sample
```

```
fsnotifywait -q --timefmt '%F %T' --format '{"time":"%T","type":"%e"}:%w%f%0' -m -r -F -e create,delete,move,modify,moved_from,moved_to /opt/work/sample/
```

This command will output this.
```shell
{"time":"2025-04-02 03:55:29","type":"CREATE"}:/opt/work/sample/aTooc8Eeph.txt
{"time":"2025-04-02 03:55:29","type":"MODIFY"}:/opt/work/sample/aTooc8Eeph.txt
{"time":"2025-04-02 03:55:29","type":"CREATE"}:/opt/work/sample/oFam1Aivoh.txt
{"time":"2025-04-02 03:55:29","type":"MODIFY"}:/opt/work/sample/oFam1Aivoh.txt
{"time":"2025-04-02 03:55:29","type":"CREATE"}:/opt/work/sample/jumaiPuSh2.txt
{"time":"2025-04-02 03:57:42","type":"CREATE,ISDIR"}:/opt/work/sample/sub

```

## see 

- man fanotify
- man fsnotifywatch
- man fsnotifywait




