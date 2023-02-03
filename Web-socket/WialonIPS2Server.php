<?php

namespace App\Library\Tracking\WialonIPS2;

use App\Library\Tracking\Server;
use Workerman\Connection\ConnectionInterface;


class WialonIPS2Server extends Server
{
    protected function protocol(): string
    {
        return config('tracking.wialon-ips-2.protocol');
    }

    protected function host(): string
    {
        return config('tracking.wialon-ips-2.host');
    }

    protected function port(): int
    {
        return config('tracking.wialon-ips-2.port');
    }

    protected function listeners(): void
    {
        $this->worker->onConnect = function (ConnectionInterface $connection) {
            $remoteAddress = $connection->getRemoteAddress();

            app('log')->debug('New connection', [
                'remote_address' => $remoteAddress,
            ]);

            $this->devices[$remoteAddress] = new WialonDevice();
        };

        $this->worker->onMessage = function (ConnectionInterface $connection, $data) {

                if ($this->devices[$connection->getRemoteAddress()]) {
                    $device   = $this->devices[$connection->getRemoteAddress()];
                    $response = $device->handle($data);

                    if ($device->isInitialized) {
                        app('log')->debug($data, [
                            'external_id' => $device->getExternalID(),
                            'response'    => trim($response),
                        ]);
                    } else {
                        app('log')->debug('Device is not initialized.', [
                            'data'           => $data,
                            'remote_address' => $connection->getRemoteAddress(),
                        ]);
                    }

                    $connection->send($response);
                } else {
                    app('log')->debug('Device is not registered.', [
                        'data'           => $data,
                        'remote_address' => $connection->getRemoteAddress(),
                    ]);
                }
        };

        $this->worker->onClose = function (ConnectionInterface $connection) {
            if (isset($this->devices[$connection->getRemoteAddress()])) {
                unset($this->devices[$connection->getRemoteAddress()]);
            }
        };
    }

    public static function init(): void
    {
        new WialonIPS2Server();
    }
}
