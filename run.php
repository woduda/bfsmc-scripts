<?php

require_once("WebsenderAPI.php");

class Run
{
    private $config;

    private $ws;

    public function __construct()
    {
        $this->config = $this->config();

        $this->ws = new WebsenderAPI(
            $this->config['host'],
            $this->config['password'],
            $this->config['port']
        );
    }

    protected function config()
    {
        return include "./config.php";
    }

    public function main()
    {
        if ($this->ws->connect()) {
            $this->send("say test");
        } else {
            echo "Connection error! Check ip, pass and port.\n";
            return;
        }
        $this->ws->disconnect();
    }

    public function send($cmd)
    {
        $this->ws->sendCommand($cmd);
    }
}

(new Run())->main();
