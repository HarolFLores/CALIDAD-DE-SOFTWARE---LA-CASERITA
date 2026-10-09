<?php
$adb = getenv('LOCALAPPDATA') . '\\Android\\Sdk\\platform-tools\\adb.exe';
exec("\"$adb\" shell dumpsys window", $lines);
foreach ($lines as $line) {
    if (strpos($line, 'mCurrentFocus') !== false || strpos($line, 'mFocusedApp') !== false) {
        echo trim($line) . "\n";
    }
}
