<?php

namespace App\Services;

use InvalidArgumentException;
use RuntimeException;

/**
 * Kirim paket Wake-on-LAN (magic packet) ke PC lab.
 *
 * Paket = 6x 0xFF diikuti MAC address sebanyak 16 kali, dikirim via UDP
 * broadcast port 9 & 7 ke seluruh segmen jaringan, plus unicast langsung
 * ke IP PC bila diketahui. Memakai stream socket UDP + SO_BROADCAST
 * (tanpa ext-sockets, portabel termasuk di Windows).
 */
class WakeOnLanService
{
    private const BROADCAST_PORTS = [9, 7];

    /**
     * @throws InvalidArgumentException bila MAC address tidak valid
     * @throws RuntimeException bila paket gagal dikirim
     */
    public function send(string $macAddress, ?string $ipAddress = null): void
    {
        $packet = $this->magicPacket($macAddress);

        $targets = [];
        foreach (self::BROADCAST_PORTS as $port) {
            $targets[] = "255.255.255.255:{$port}";
        }
        if ($ipAddress && filter_var($ipAddress, FILTER_VALIDATE_IP)) {
            foreach (self::BROADCAST_PORTS as $port) {
                $targets[] = "{$ipAddress}:{$port}";
            }
        }

        $sent = false;
        $lastError = '';

        foreach (array_unique($targets) as $target) {
            $context = stream_context_create(['socket' => ['broadcast' => true]]);
            $socket = @stream_socket_client(
                'udp://'.$target,
                $errno,
                $errstr,
                5,
                STREAM_CLIENT_CONNECT,
                $context
            );

            if (! $socket) {
                $lastError = $errstr ?: "error {$errno}";

                continue;
            }

            $written = @fwrite($socket, $packet);
            fclose($socket);

            if ($written !== false && $written === strlen($packet)) {
                $sent = true;
            } elseif (! $lastError) {
                $lastError = $errstr ?: 'paket tidak terkirim';
            }
        }

        if (! $sent) {
            throw new RuntimeException("Gagal mengirim paket Wake-on-LAN: {$lastError}");
        }
    }

    /**
     * @throws InvalidArgumentException bila MAC address tidak valid
     */
    public function magicPacket(string $macAddress): string
    {
        $mac = str_replace(['-', '.'], ':', trim($macAddress));

        if (! preg_match('/^([0-9A-Fa-f]{2}:){5}[0-9A-Fa-f]{2}$/', $mac)) {
            throw new InvalidArgumentException("MAC address tidak valid: {$macAddress}");
        }

        $macBytes = pack('H*', str_replace(':', '', $mac));

        return "\xFF".str_repeat("\xFF", 5).str_repeat($macBytes, 16);
    }
}
