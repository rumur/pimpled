<?php

namespace Rumur\Pimpled\Contracts\Assets;

interface Asset
{
	/**
	 * Get the cache-busted filename
	 *
	 * If the manifest does not have an entry for $asset, then return $asset
	 *
	 * @param string $asset The original name of the file before cache-busting
	 *
	 * @return string
	 */
	public function getKey( $asset );

	/**
	 * Get the cache-busted URI
	 *
	 * If the manifest does not have an entry for $asset, then return URI for $asset
	 *
	 * @param string $asset The original name of the file before cache-busting
	 *
	 * @return string
	 */
	public function get( $asset );
}
