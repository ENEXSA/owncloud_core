<?php
/**
 * Copyright (c) 2014 Robin Appelman <icewind@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace OC\Files\Cache\Wrapper;

use OC\Files\Cache\Cache;

class CacheWrapper extends Cache {
	/**
	 * @var \OC\Files\Cache\Cache
	 */
	protected $cache;

	/**
	 * @param \OC\Files\Cache\Cache $cache
	 */
	public function __construct($cache) {
		$this->cache = $cache;
	}

	/**
	 * Make it easy for wrappers to modify every returned cache entry
	 *
	 * @param array $entry
	 * @return array
	 */
	protected function formatCacheEntry($entry) {
		return $entry;
	}

	/**
	 * get the stored metadata of a file or folder
	 *
	 * @param string /int $file
	 * @return array|false
	 */
	public function get($file) {
		$result = $this->cache->get($file);
		if ($result) {
			$result = $this->formatCacheEntry($result);
		}
		return $result;
	}

	/**
	 * get the metadata of all files stored in $folder
	 *
	 * @param string $folder
	 * @return array
	 */
	public function getFolderContents($folder) {
		// cant do a simple $this->cache->.... call here since getFolderContentsById needs to be called on this
		// and not the wrapped cache
		$fileId = $this->getId($folder);
		return $this->getFolderContentsById($fileId);
	}

	/**
	 * get the metadata of all files stored in $folder
	 *
	 * @param int $fileId the file id of the folder
	 * @return array
	 */
	public function getFolderContentsById($fileId) {
		$results = $this->cache->getFolderContentsById($fileId);
		return array_map(array($this, 'formatCacheEntry'), $results);
	}

	/**
	 * store meta data for a file or folder
	 *
	 * @param string $file
	 * @param array $data
	 *
	 * @return int file id
	 */
	public function put($file, array $data) {
		return $this->cache->put($file, $data);
	}

	/**
	 * update the metadata in the cache
	 *
	 * @param int $id
	 * @param array $data
	 */
	public function update($id, array $data) {
		$this->cache->update($id, $data);
	}

	/**
	 * get the file id for a file
	 *
	 * @param string $file
	 * @return int
	 */
	public function getId($file) {
		return $this->cache->getId($file);
	}

	/**
	 * get the id of the parent folder of a file
	 *
	 * @param string $file
	 * @return int
	 */
	public function getParentId($file) {
		return $this->cache->getParentId($file);
	}

	/**
	 * check if a file is available in the cache
	 *
	 * @param string $file
	 * @return bool
	 */
	public function inCache($file) {
		return $this->cache->inCache($file);
	}

	/**
	 * remove a file or folder from the cache
	 *
	 * @param string $file
	 */
	public function remove($file) {
		$this->cache->remove($file);
	}

	/**
	 * Move a file or folder in the cache
	 *
	 * @param string $source
	 * @param string $target
	 */
	public function move($source, $target) {
		$this->cache->move($source, $target);
	}

	/**
	 * remove all entries for files that are stored on the storage from the cache
	 */
	public function clear() {
		$this->cache->clear();
	}

	/**
	 * @param string $file
	 *
	 * @return int, Cache::NOT_FOUND, Cache::PARTIAL, Cache::SHALLOW or Cache::COMPLETE
	 */
	public function getStatus($file) {
		return $this->cache->getStatus($file);
	}

	/**
	 * search for files matching $pattern
	 *
	 * @param string $pattern
	 * @return array an array of file data
	 */
	public function search($pattern) {
		$results = $this->cache->search($pattern);
		return array_map(array($this, 'formatCacheEntry'), $results);
	}

	/**
	 * search for files by mimetype
	 *
	 * @param string $mimetype
	 * @return array
	 */
	public function searchByMime($mimetype) {
		$results = $this->cache->searchByMime($mimetype);
		return array_map(array($this, 'formatCacheEntry'), $results);
	}

	/**
	 * update the folder size and the size of all parent folders
	 *
	 * @param string|boolean $path
	 * @param array $data (optional) meta data of the folder
	 */
	public function correctFolderSize($path, $data = null) {
		$this->cache->correctFolderSize($path, $data);
	}

	/**
	 * get the size of a folder and set it in the cache
	 *
	 * @param string $path
	 * @param array $entry (optional) meta data of the folder
	 * @return int
	 */
	public function calculateFolderSize($path, $entry = null) {
		return $this->cache->calculateFolderSize($path, $entry);
	}

	/**
	 * get all file ids on the files on the storage
	 *
	 * @return int[]
	 */
	public function getAll() {
		return $this->cache->getAll();
	}

	/**
	 * find a folder in the cache which has not been fully scanned
	 *
	 * If multiply incomplete folders are in the cache, the one with the highest id will be returned,
	 * use the one with the highest id gives the best result with the background scanner, since that is most
	 * likely the folder where we stopped scanning previously
	 *
	 * @return string|bool the path of the folder or false when no folder matched
	 */
	public function getIncomplete() {
		return $this->cache->getIncomplete();
	}

	/**
	 * get the path of a file on this storage by it's id
	 *
	 * @param int $id
	 * @return string|null
	 */
	public function getPathById($id) {
		return $this->cache->getPathById($id);
	}

	/**
	 * get the storage id of the storage for a file and the internal path of the file
	 * unlike getPathById this does not limit the search to files on this storage and
	 * instead does a global search in the cache table
	 *
	 * @param int $id
	 * @return array, first element holding the storage id, second the path
	 */
	static public function getById($id) {
		return parent::getById($id);
	}
}
