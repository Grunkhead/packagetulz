<?php

namespace App\DataTransferObjects;

class PackageDto
{
    
    public function __construct(
        public readonly string $id,
        public readonly string $server,
        public readonly string $cpu,
        public readonly string $memory,
        public readonly string $storage,
        public readonly string $traffic,
        public readonly string $os,
        public readonly string $uplink,
        public readonly string $guaranteed_speed,
        public readonly string $ddos_shield,
        public readonly string $remote_management,
        public readonly string $support,
        public readonly string $payment
    ) {}


    public function toJson()
    {
        return json_encode($this, JSON_PRETTY_PRINT);
    }

    public static function fromArray(array $data)
    {
        return new self(
            $data['id'] ?? null,
            $data['server'] ?? null,
            $data['cpu'] ?? null,
            $data['memory'] ?? null,
            $data['storage'] ?? null,
            $data['traffic'] ?? null,
            $data['os'] ?? null,
            $data['uplink'] ?? null,
            $data['guaranteed_speed'] ?? null,
            $data['ddos_shield'] ?? null,
            $data['remote_management'] ?? null,
            $data['support'] ?? null,
            $data['payment'] ?? null
        );
    }
}