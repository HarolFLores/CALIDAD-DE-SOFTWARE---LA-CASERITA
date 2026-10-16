<?php
$dir = new RecursiveDirectoryIterator(__DIR__ . '/../Views');
$iterator = new RecursiveIteratorIterator($dir);
$count = 0;

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());
        $updated = preg_replace('/(<link[^>]*rel=[\'"](?:shortcut )?icon[\'"][^>]*href=[\'"])Images\/(?:logo|icono)\.png([\'"])/i', '$1Images/logo_backup.png$2', $content);
        if ($updated !== $content) {
            file_put_contents($file->getPathname(), $updated);
            $count++;
            echo "Updated favicon in: " . $file->getBasename() . PHP_EOL;
        }
    }
}
echo "Total views updated with favicon: $count\n";
