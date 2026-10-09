<?php
$c = file_get_contents('C:/Users/smt2d/.gemini/antigravity-ide/brain/497d6693-964d-4807-9b3d-de4fcc6a7653/.system_generated/steps/10296/content.md');
if (preg_match('/"title":{"runs":\[{"text":"(.*?)"/i', $c, $m)) {
    echo "Found title: " . $m[1] . "\n";
}
if (preg_match('/"description":{"runs":\[{"text":"(.*?)"/i', $c, $m)) {
    echo "Found desc: " . $m[1] . "\n";
}
if (preg_match('/<title>(.*?)<\/title>/i', $c, $m)) {
    echo "HTML title: " . $m[1] . "\n";
}
