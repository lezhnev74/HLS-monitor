# HLS-monitor
Console script to monitor that HLS links are accessible (supports adaptive streaming)

## Install via Composer
```
composer require lezhnev74/hls-monitor
```

## Usage
Package comes with a console file located at `vendor/bin/hls-monitor`. Check that playlist is available and all it's streams are accessible from the Internet (in 10 simultaneous connections)


```
vendor/bin/hls-monitor playlists --log log.txt --concurrency=10 http://akamai.streamroot.edgesuite.net/vodorigin/tos.mp4/playlist.m3u8
```

Output will be something like this:

```
#---------------------------------------------------
Executed on node: <YOUR_PUBLIC_IP>
Date of execution: 29.11.2016, 10:04 UTC
#---------------------------------------------------

Concurrency level:50
Playlists fetching is over in 0.7s
Streams fetching is over in 0.68s
Chunks fetching is over in 255.22s
```

The console command will return code `0` if no problems found. 
In case of some stream is not accessible, you will see someting like this:
 
```
#---------------------------------------------------
Executed on node: <YOU_PUBLIC_IP>
Date of execution: 29.11.2016, 11:11 UTC
#---------------------------------------------------

Downloading playlist...DONE
Playlist is located on: 127.0.0.1
Found streams in playlist: 3
Started checking streams
Stream is not accessible: http://m3u8provider.dev.com/stream2.m3u8
Stream has unaccessible chunks:
|--Chunk: http://m3u8provider.dev.com/04.ts
|--Chunk: http://m3u8provider.dev.com/05.ts
|---
Checking Streams is DONE
```

## Reporting
At this version library has no reporting capabilities. 
 
 You will have to write a wrapper for this tool which will capture all of the failed output and will email it to you or report to somewhere.
 
 Pseudo code looks like this:
 
 ```php
 
$playlists = [...many playlists to check...];
$failed_reports = [];

// for each given playlist - make checking
$output = [];
$return_code = 0;
exec('vendor/bin/hls-monitor playlists '.implode(",",$playlists), $output, $return_code);   
    
if($return_code) {
    // there were problems with this palylist
    $failed_reports[] = $output;
}

 
 // then report if anything is not good
 if(count($failed_reports)) {
    // mail reports to somewhere
 }
 
 ```