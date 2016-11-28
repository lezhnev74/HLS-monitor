# Notes on implementation

## Flow
### Download playlist


```
Example: playlist.m3u8

#EXTM3U
#EXT-X-VERSION:3
#EXT-X-STREAM-INF:BANDWIDTH=4345315,RESOLUTION=1280x720
en__720p.mp4_chunk.m3u8?nimblesessionid=728188
#EXT-X-STREAM-INF:BANDWIDTH=2329918,RESOLUTION=854x480
en__480p.mp4_chunk.m3u8?nimblesessionid=728188
#EXT-X-STREAM-INF:BANDWIDTH=719190,RESOLUTION=480x270
en__270p.mp4_chunk.m3u8?nimblesessionid=728188
```

* Get stream URLs
* Download stream's few bytes