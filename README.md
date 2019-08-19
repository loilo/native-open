<div align="center">
  <img alt="native-open logo: A mouse pointer, presented as a hand with extended index finger, floating above a colored circle representing an interactive area." src="native-open.svg" width="200" height="200">
</div>

# Native Open
<!-- [![Test status on Travis](https://badgen.net/travis/loilo/native-open?label=tests&icon=travis)](https://travis-ci.org/loilo/native-open) -->
<!-- [![Version on packagist.org](https://badgen.net/packagist/v/loilo/native-open)](https://packagist.org/packages/loilo/native-open) -->

This package enables you to open a file/URL/app from inside PHP, cross-platform.

Note that it opens the app on *the machine running the PHP code*, so you can *not* use it to open apps on behalf of your website's users.

> Most of the credit for this package goes to [Sindre Sorhus](https://sindresorhus.com) as this is mostly a port of his [`open`](https://www.npmjs.com/package/open) package for Node.js.

## Installation
```bash
composer require loilo/native-open
```

## Usage
```php
use Loilo\NativeOpen\NativeOpen;

// Opens the image in the default image viewer
NativeOpen::open('picture.jpg'); 

// Opens the URL in the default browser
NativeOpen::open('https://github.com/loilo');

// Opens the URL in a specified browser
NativeOpen::open('https://github.com/loilo', 'firefox');

// Specify app arguments
NativeOpen::open('https://github.com/loilo', 'google chrome', ['--incognito']);
```

This package uses the `open` command on macOS, `start` on Windows and `xdg-open` on other platforms. Note that, if you define a specific app to open a target in, the app name is platform dependent. Don't hard code it in reusable modules. For example, Chrome is `google chrome` on macOS, `google-chrome` on Linux and `chrome` on Windows.
