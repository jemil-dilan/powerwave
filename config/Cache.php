<?php

// config/Cache.php
class Cache {
    private $cache_path = 'cache/';

    public function set($key, $data, $ttl = 3600) {
        $file = $this->cache_path . md5($key);
        $content = [
            'expires' => time() + $ttl,
            'data' => $data
        ];
        return file_put_contents($file, serialize($content));
    }

    public function get($key) {
        $file = $this->cache_path . md5($key);
        if (file_exists($file)) {
            $content = unserialize(file_get_contents($file));
            if ($content['expires'] > time()) {
                return $content['data'];
            }
        }
        return null;
    }
}