<?php

namespace Pmld\Foundation\Asset;

use Pmld\Contracts\Assets\Asset as AssetContract;

class Asset implements AssetContract
{
    /** @var string */
    public $dist;

    /** @var array */
    public $manifest;

    /** @var string */
    protected $manifest_path;

	/**
	 * Assets constructor
	 *
	 * @param string $manifest_path Local filesystem path to JSON-encoded manifest
	 * @param string $dist_uri Remote URI to assets root
	 */
    public function __construct($manifest_path, $dist_uri)
    {
        $this->manifest_path = dirname($manifest_path);

        $this->manifest = file_exists($manifest_path)
            ? json_decode(file_get_contents($manifest_path), true)
            : [];
        $this->dist = $dist_uri;
    }

    /**
     * Get the cache-busted URI
     *
     * If the manifest does not have an entry for $asset, then return URI for $asset
     *
     * @param string $asset The original name of the file before cache-busting
     * @return string
     */
    public function get($asset)
    {
        return $this->isHotReplacement()
            ? "//localhost:8080{$this->getKey($asset)}"
            : "{$this->dist}{$this->getKey($asset)}";
    }

    /**
     * Get the cache-busted filename
     *
     * If the manifest does not have an entry for `$asset`, then return `$asset`
     *
     * @param string $asset The original name of the file before cache-busting
     * @return string
     */
	public function getKey($asset)
    {
        // Resolve the slash e.g. js/app.js -> /js/app.js
        $asset = '/' . ltrim($asset, '\/');

        return isset( $this->manifest[ $asset ] ) ? $this->manifest[ $asset ] : $asset;
    }

    /**
     * @return boolean
     *
     * @author rumur
     */
    protected function isHotReplacement()
    {
        return file_exists($this->manifest_path.'/hot');
    }
}
