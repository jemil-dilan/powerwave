<?php

// middleware/RateLimit.php
class RateLimit {
    private $redis;

    public function check($ip, $limit = 100, $period = 3600) {
        $key = "rate:$ip";
        $current = $this->redis->incr($key);
        if ($current === 1) {
            $this->redis->expire($key, $period);
        }
        return $current <= $limit;
    }
}
