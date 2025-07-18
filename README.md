# Smart Video Embed

A powerful WordPress plugin for embedding videos with advanced features and customization options.

## Author

**Srki Mafia**
- GitHub: [@srkis](https://github.com/srkis)
- WordPress Developer specializing in video solutions and custom plugins

## Features

- **Multiple Video Sources**
  - YouTube video support
  - Video.js player integration
  - Custom video file support

- **Advanced Customization**
  - Custom thumbnails
  - Lottie animations for loading states
  - Multiple theme options
  - Aspect ratio control (16:9, 4:3, custom)
  - Maximum width settings
  - Modal/lightbox view

- **Player Controls**
  - Show/hide player controls
  - Autoplay options
  - Mute settings
  - Disable related videos (YouTube)
  - Privacy-enhanced mode using youtube-nocookie.com

## Installation

1. Download the plugin zip file
2. Go to WordPress admin panel → Plugins → Add New
3. Click "Upload Plugin" and select the downloaded zip file
4. Click "Install Now" and then "Activate"

## Usage

### Basic Shortcode

```
[smart_video url="https://www.youtube.com/watch?v=VIDEO_ID"]
```

### Advanced Options

```
[smart_video 
    url="https://www.youtube.com/watch?v=VIDEO_ID"
    thumbnail="path/to/custom-thumbnail.jpg"
    aspect_ratio="16:9"
    max_width="800"
    theme="dark"
    controls="false"
    autoplay="true"
    mute="false"
    modal="true"
]
```

### Video.js Implementation

```
[smart_video 
    url="path/to/video.mp4"
    player="videojs"
    thumbnail="path/to/thumbnail.jpg"
]
```

## Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| url | string | required | Video URL (YouTube) or file path |
| player | string | 'youtube' | Player type ('youtube' or 'videojs') |
| thumbnail | string | null | Custom thumbnail image path |
| aspect_ratio | string | '16:9' | Video aspect ratio ('16:9', '4:3', or custom) |
| max_width | number | null | Maximum width in pixels |
| theme | string | 'default' | Player theme ('default', 'dark', 'light') |
| controls | boolean | true | Show/hide player controls |
| autoplay | boolean | false | Enable/disable autoplay |
| mute | boolean | false | Mute video by default |
| modal | boolean | false | Enable lightbox/modal view |
| animation | string | null | Lottie animation JSON file path |

## Technical Details

The plugin uses:
- WordPress Shortcode API
- YouTube IFrame API
- Video.js player library
- Lottie Web for animations
- Custom JavaScript for modal functionality

## Security

- All user inputs are sanitized and validated
- YouTube videos use privacy-enhanced mode (youtube-nocookie.com)
- No external requests except to YouTube and specified video sources
- Follows WordPress security best practices

## Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher
- Modern web browser with JavaScript enabled

## Support

For support, feature requests, or bug reports, please visit our GitHub repository or contact plugin support.

## License

This plugin is licensed under the GPL v2 or later.

```
Copyright (C) 2024 Smart Video Embed

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
``` 