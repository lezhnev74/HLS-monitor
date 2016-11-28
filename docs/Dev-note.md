# Overview
This project helps me to monitor HLS streams of our websites. It will eat some HLS links and will check that the stream is accessible. It will also check that all the adaptive variants are good.
 
## Bandwidth considerations
The goal of the script is only to make sure stream can be accessible, so it will not download all the available stream. Instead it will only download few bytes from the stream.

## Proxy
Sometimes we need to make sure that HLS stream is accessible from different countries. In other words that different servers serve streams for different countries.

We can put this script on many servers and run from there. Or we can proxy the traffic through given HTTP anonymous proxies to imitate access from different countries. 

This feature has low priority for now.

## Website crawling
This script can be run in single mode, so it will only check given link. Or it can crawl all available links from given page.
 
 The crawling and gathering all the pages and links is also a good feature but it doesn't have top priority for now.
 
 ## Reporting
 
 Whenever script is unable to download the stream via the link - it will log this event and report to admin via different channels.
 
 Currently main channel is E-mail. But additional channels are to be developed (Slack, and other notifications). 