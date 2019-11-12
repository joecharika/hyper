<?php

namespace Hyper\SocketIO;

use Hyper\Application\HyperApp;

class SocketIOHyperApp extends HyperApp
{
    public $chatHandler,
        $hostName = "localhost",
        $port = "8090";

    private $socketResource, $clientSocketArray;

    public function __construct(string $name, string $routingMode = "auto", array $sections = [])
    {
        parent::__construct($name, $routingMode, $sections);
        $this->chatHandler = new ChatHandler();
        $this->socketResource = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

        $this->__socketInit();

        $this->clientSocketArray = array($this->socketResource);

        $this->__run();
    }

    private function __socketInit()
    {
        $socketResource = $this->socketResource;
        socket_set_option($socketResource, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_bind($socketResource, 0, $this->port);
        socket_listen($socketResource);
    }

    private function __run()
    {
        while (true) {
            $newSocketArray = $this->clientSocketArray;
            socket_select($newSocketArray, $null, $null, 0, 10);

            if (in_array($this->socketResource, $newSocketArray)) {
                $newSocket = socket_accept($this->socketResource);
                $clientSocketArray[] = $newSocket;

                $header = socket_read($newSocket, 1024);
                $this->chatHandler->doHandshake($header, $newSocket, $this->hostName, $this->port);

                socket_getpeername($newSocket, $client_ip_address);
                $connectionACK = $this->chatHandler->newConnectionACK($client_ip_address);

                $this->chatHandler->send($connectionACK);

                $newSocketIndex = array_search($this->socketResource, $newSocketArray);
                unset($newSocketArray[$newSocketIndex]);
            }

            foreach ($newSocketArray as $newSocketArrayResource) {
                while (socket_recv($newSocketArrayResource, $socketData, 1024, 0) >= 1) {
                    $socketMessage = $this->chatHandler->unseal($socketData);
                    $messageObj = (object)json_decode($socketMessage);

                    $chat_box_message = $this->chatHandler->createChatBoxMessage($messageObj->chat_user,
                        $messageObj->chat_message);
                    $this->chatHandler->send($chat_box_message);
                    break 2;
                }

                $socketData = @socket_read($newSocketArrayResource, 1024, PHP_NORMAL_READ);
                if ($socketData === false) {
                    socket_getpeername($newSocketArrayResource, $client_ip_address);
                    $connectionACK = $this->chatHandler->connectionDisconnectACK($client_ip_address);
                    $this->chatHandler->send($connectionACK);
                    $newSocketIndex = array_search($newSocketArrayResource, $this->clientSocketArray);
                    unset($this->clientSocketArray[$newSocketIndex]);
                }
            }
        }

        socket_close($this->socketResource);
    }

}

new SocketIOHyperApp("socket_app");