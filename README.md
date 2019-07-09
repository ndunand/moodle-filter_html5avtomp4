# Moodle HTML5 audio/video to MP4 filter

This filter allows the automated creation of MP4 sources for HTML5 `<audio>` and `<video>` elements.

This is particularly useful if some users are using [atto_recordrtc](https://docs.moodle.org/37/en/RecordRTC) because most browsers create `ogg` files for audio and `webm` files for video, which are not playable in Safari.

This plugin then checks for the existence of an `mp4` source in the same file area and provides it automatically if it exists. In the case the `mp4` source does not exist, it is created via a scheduled task.

## Requirements

A working `ffmpeg` installation, and the correct path to be set in the plugin settings.

In Debian-like GNU/Linux environments, `ffmpeg` can be installed by issuing the following command:

```
apt-get install ffmpeg
```

## Installation

You need to at least:

1. install `ffmpeg` (see above),
2. set the `pathtoffmpeg` in the plugin settings.

Optionnally you can:

* change the other plugin settings as required,
* tweak the execution times of the scheduled task running the file conversions.

## Issues

Please report issues on https://github.com/ndunand/moodle-filter_html5avtomp4/issues .

