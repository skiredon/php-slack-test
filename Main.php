<?php

require __DIR__.'/vendor/autoload.php';
require "Utils.php";

use Slack\User;
use Slack\ChannelInterface;

#https://hooks.slack.com/services/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
$loop = React\EventLoop\Factory::create();

#$client = new \Slack\ApiClient($loop);
$client = new Slack\RealTimeClient($loop);
$client->setToken('xoxb-349247732690-XXXXXXXXXXXXXXXX');
date_default_timezone_set('Europe/Kiev');


$client->on('message', function ($data) use ($client) {
    $user_id = $data['user'];
    $msg = $data['text'];
    $message_data = array($msg, date("Y-m-d H:i:s"));
    $wdata = null;
    $real_name = null;
    $user_name = null;
    $client->getUserById($data['user'])->then(function (User $user) use (&$real_name, &$user_name) {
            $real_name = $user->getRealName();
            $user_name = $user->getUsername();
        });
    try {
        $wdata = json_decode(read_json_data("data.json"), true);
        if ($wdata == null) {
            throw new Exception;
        }
        try {
            $msgs = $wdata[(string)$user_id]["messages"];
            $msgs[count($msgs)] = $message_data;
        }
        catch (Exception $e) {
            $msgs = array($message_data);
        }
        $dest = array( $user_id=> array("_name"=>$user_name, "_real_name"=>$real_name, "messages"=>$msgs));
        $wdata = array_merge($wdata, $dest);
        write_json_data("data.json", json_encode($wdata));
    }
    catch (Exception $e) {
        echo $e;
        $wdata = array();
        $msgs = array($message_data);
        $dest = array( $user_id=> array("_name"=>$user_name, "_real_name"=>$real_name, "messages"=>$msgs));
        $wdata = array_merge($wdata, $dest);
        write_json_data("data.json", json_encode($wdata));
    }
    $command = parse_direct_message($msg);

    if ($command == "send_messages") {
        #send command
        $client->getChannelGroupOrDMByID($data["channel"])->then(function (ChannelInterface $channel) use ($client, $wdata) {
            return $client->send(read_json_data("data.json"), $channel)->then(null);
        });
    }
});

$client->connect()->then(function () use ($client) {
    echo "Connected! Start bot...\n";
    $client->getAuthedUser()->then(function (User $user) {
        echo "bot_id ".$user->getId()."\n";
    });
});
$loop->run();