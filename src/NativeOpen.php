<?php namespace Loilo\NativeOpen;

use Exception;
use Loilo\NodePath\Path;
use RuntimeException;

class NativeOpen
{
    /**
     * Open a file or URL in an app of the running machine
     *
     * @param string      $target      The target file or URL to open
     * @param string|null $app         The app to open the target in (default is the system's default app)
     * @param array       $appAguments Arguments passed to the opening app
     * @return void
     */
    public static function open(string $target, ?string $app = null, array $appArguments = []): void
    {
        $cliArguments = [];

        if (PHP_OS === 'Darwin') {
            $command = 'open';

            if (!is_null($app)) {
                array_push($cliArguments, '-a', $app);
            }
        } elseif (PHP_OS === 'Linux') {
            if (!is_null($app)) {
                $command = $app;
            } else {
                // Included xdg-open
                $command = Path::join(__DIR__, '..', 'xdg-open');
            }

            if (sizeof($appArguments) > 0) {
                array_push($cliArguments, ...$appArguments);
            }
        } elseif (DIRECTORY_SEPARATOR === '\\' || static::isWsl()) {
            // Assume Windows

            $command = 'cmd' . (static::isWsl() ? '.exe' : '');
            array_push($cliArguments, '/c', 'start', '""', '/b');
            $target = str_replace($target, '&', '^&');

            if (!is_null($app)) {
                if (static::isWsl() && substr($app, 0, 5) === '/mnt/') {
                    $windowsPath = static::wslToWindowsPath($app);
                    $app = $windowsPath;
                }

                $cliArguments[] = $app;
            }

            if (sizeof($appArguments) > 0) {
                array_push($cliArguments, ...$appArguments);
            }
        } else {
            throw new RuntimeException('Unsupported operating system or unrecognized PHP_OS constant: ' . PHP_OS);
        }

        $cliArguments[] = $target;

        if (PHP_OS === 'Darwin' && sizeof($appArguments) > 0) {
            array_push($cliArguments, '--args', ...$appArguments);
        }

        $fullCommand = $command . ' ' . join(' ', array_map('escapeshellarg', $cliArguments));

        exec(
            $fullCommand,
            $output,
            $status
        );

        if ($status !== 0) {
            throw new RuntimeException(sprintf(
                'Executing open command returned status code %s: %s',
                $status,
                $command
            ));
        }
    }

    /**
     * Detect Windows Subsystem for Linux
     * Ported from https://github.com/sindresorhus/is-wsl
     *
     * @return bool
     */
    protected static function isWsl(): bool
    {
        static $isWsl = null;

        if (is_null($isWsl)) {
            if (PHP_OS !== 'Linux') {
                $isWsl = false;
            } elseif (stripos(php_uname(), 'microsoft') !== false) {
                $isWsl = true;
            } else {
                try {
                    $env = file_get_contents('/proc/version');

                    if (is_string($env)) {
                        $isWsl = stripos($env, 'microsoft');
                    } else {
                        $isWsl = false;
                    }
                } catch (Exception $_e) {
                    $isWsl = false;
                }
            }
        }

        return $isWsl;
    }

    protected static function wslToWindowsPath(string $path): string
    {
        exec('wslpath -w ' . escapeshellarg($path), $output, $statusCode);
        $output = trim(join("\n", $output));

        if ($statusCode !== 0) {
            throw new Exception($output);
        } else {
            return $output;
        }
    }
}
