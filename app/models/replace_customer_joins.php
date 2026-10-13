<?php
$modelsDir = 'c:\xampp\htdocs\Kravyo\app\models';
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($modelsDir));

$replacements = [
    'JOIN customers u ON o.customer_id = u.id' => 'JOIN customers u ON o.customer_id = u.id',
    'JOIN customers u    ON o.customer_id = u.id' => 'JOIN customers u    ON o.customer_id = u.id',
    'JOIN customers u ON r.customer_id = u.id' => 'JOIN customers u ON r.customer_id = u.id',
    'JOIN customers u ON cs.customer_id = u.id' => 'JOIN customers u ON cs.customer_id = u.id',
];

$count = 0;
foreach ($files as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $path = $file->getRealPath();
        $content = file_get_contents($path);
        
        $newContent = str_replace(array_keys($replacements), array_values($replacements), $content);
        
        if ($newContent !== $content) {
            file_put_contents($path, $newContent);
            echo "Updated {$file->getFilename()}\n";
            $count++;
        }
    }
}

echo "Successfully updated $count model files to use 'customers' table for customer joins.\n";
