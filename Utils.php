<?php

/**
 * Write json data
 * @param string $filename File name
 * @param string $data encode array in string type
 */
function write_json_data($filename, $data)
{
    $fp = fopen($filename, "w", true);
    fwrite($fp, $data);
}

/**
 * Read json data
 * @param string $filename File name
 * @return bool|string
 */
function read_json_data($filename)
{
    $fp = fopen($filename, "r", true);
    return fread($fp, 512);
}

function parse_direct_message($message_text)
{
    preg_match("/^<@(.*)/", $message_text, $matches);
    if ($matches) {
        $command = explode(" ", $matches[0]);;
        return $command[1];
    }
    return null;
}
