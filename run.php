<?php

define('FCPATH', str_replace(pathinfo(__FILE__, PATHINFO_BASENAME), '', __FILE__));

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
        return include FCPATH."config.php";
    }

    public function main()
    {
        if (! $this->ws->connect()) {
            echo "Connection error! Check ip, pass and port.\n";
            return;
        }

        $this->updateTitles();

        $this->ws->disconnect();
    }


    protected function updateTitles()
    {
        $db_content = @file_get_contents($this->config['db_path']);
        $bfs_content = @file_get_contents($this->config['bfs_path']);

        $awards = json_decode($db_content, true)['awards'];

        if ($bfs_content) {
            $bfs = json_decode($bfs_content, true);
        } else {
            $bfs = [];
        }
        $isset = [];
        foreach ($awards as $id => $award) {
            $uuid = $award['best']['uuid'] ?? false;
            $_uuid = $bfs['titles'][$id] ?? false;

            if ($uuid === $_uuid) {
                $isset[] = $uuid;
                continue;
            }

            if ($_uuid !== false) {
                $this->setTitle($_uuid, ''); // clear old title
                unset($bfs['titles'][$id]);
            }
            if (! in_array($uuid, $isset)) {
                $k = array_search($uuid, $bfs['titles'] ?? []);
                if ($k !== $id) {
                    $this->setTitle($uuid, $award['title']);
                    $isset[] = $uuid;

                    unset($bfs['titles'][$k]);
                    $bfs['titles'][$id] = $uuid;
                }
            }
        }

        @file_put_contents($this->config['bfs_path'], json_encode($bfs));

        return $this;
    }

    public function setTitle($uuid, $title = null)
    {
        $suffix = (empty($title) ? '""' : ('" &2'.$title.'"'));
        $cmd = "pex user $uuid suffix $suffix";
        $this->send($cmd);
        echo "$cmd\n";
    }

    public function send($cmd)
    {
        $this->ws->sendCommand($cmd);
    }
}

(new Run())->main();
