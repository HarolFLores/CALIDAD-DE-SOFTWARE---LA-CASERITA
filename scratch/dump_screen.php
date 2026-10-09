<?php
$adb = getenv('LOCALAPPDATA') . '\\Android\\Sdk\\platform-tools\\adb.exe';
exec("\"$adb\" shell uiautomator dump /data/local/tmp/dump.xml");
exec("\"$adb\" exec-out cat /data/local/tmp/dump.xml", $xmlLines);
$xml = implode("\n", $xmlLines);
preg_match_all('/text="([^"]+)"/', $xml, $matches);
print_r(array_unique($matches[1]));
