<?php

namespace Pmld\Foundation;

class Compat
{
    /**
     * Check if current WP has minimal required version.
     *
     * @since 0.0.1
     *
     * @param $minVersion string Version in 0.0.0 format.
     *
     * @return bool True if WordPress version is higher or equal to min version, false otherwise.
     */
    public static function checkWordPress($minVersion)
    {
        global $wp_version;
        return self::checkForMinimalVersion($wp_version, $minVersion);
    }

    /**
     * Check if current PHP have required version
     *
     * @since 0.0.1
     *
     * @param $minVersion string Version in 0.0.0 format.
     * @return bool True if the $minVersion is passed and is lower or equal to PHP_VERSION, false otherwise.
     */
    public static function checkPHP($minVersion)
    {
        return self::checkForMinimalVersion(phpversion(), $minVersion);
    }

    /**
     * Check if minimal version is equal or lower than current version.
     *
     * @since 0.0.1
     *
     * @param $currentVersion string Current version.
     * @param $minVersion string Minimal version supported by product.
     *
     * @return bool True if current version is higher or equal to min version, false otherwise.
     */
    public static function checkForMinimalVersion($currentVersion, $minVersion)
    {
        $result = version_compare($currentVersion, $minVersion);

        if ($result >= 0) {
            return true;
        }

        return false;
    }
}
