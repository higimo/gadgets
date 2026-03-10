<?php

if ($argc < 3) {
    echo
        "Скрипт для добавления ссылки на MOC во все markdown-файлы внутри папки\n"
        . "Использовать так\n"
        . "php -f obsidian.add_moc_in_folder.php /obsidian/folder '!Проекты'\n\n"
    ;
    exit(1);
}

$directory = $argv[1];
$mocName = $argv[2];
$mockLink = "[[$mocName]]";
$stats = ['processed' => 0, 'skipped' => 0, 'errors' => []];

if (!is_dir($directory)) {
    echo "Папки '$directory' не существует";
    exit(1);
}

$isRecursive = false;

switch ($isRecursive) {
    case true:
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        break;

    default:
        $iterator = new FilesystemIterator($directory, FilesystemIterator::SKIP_DOTS);
        break;
}

foreach ($iterator as $file) {
    if ($file->getExtension() !== 'md') {
        continue;
    }
    
    $filePath = $file->getPathname();
    
    // Скипаем сам мок
    if ($file->getFilename() === $mocName . ".md") {
        $stats['skipped']++;
        continue;
    }

    try {
        $content = file_get_contents($filePath);
        
        // Скипаем то, что уже ссылается
        if (strpos($content, $mocName) !== false) {
            $stats['skipped']++;
            continue;
        }

        // Удаляем чужие моки
        $hasEnemyMoc = preg_match('/\[\[!.*(?:\|.*?)?\]\]/', $content, $matchesEnemyMoc, PREG_OFFSET_CAPTURE);
        if ($hasEnemyMoc) {
            $enemyMocContent = $matchesEnemyMoc[0][1];
            $start = $matchesEnemyMoc[0][1];
            $length = strlen($matchesEnemyMoc[0][0]);

            $content = substr($content, 0, $start) . substr($content, $length, strlen($content));
        }

        $start = 0;

        $hasFrontmatter = preg_match('/^---\s*\n(.*?\n)---\s*\n/s', $content, $matchesFrontMatter, PREG_OFFSET_CAPTURE);
        if ($hasFrontmatter) {
            $start = strlen($matchesFrontMatter[0][0]);
        }

        $mocContent = ($start > 0 ? "\n\n" : '') . "$mockLink\n\n";
        $content = substr($content, 0, $start) . $mocContent . substr($content, $start, strlen($content));

        if (file_put_contents($filePath, $content)) {
            $stats['processed']++;
        } else {
            echo "❌ ошибка записи\n";
            $stats['errors'][] = $file->getFilename();
        }
    } catch (Exception $error) {
        $stats['errors'][] = $error->getMessage();
    }
}

echo "\n" . str_repeat('-', 40) . "\n";
echo "Обработано файлов: {$stats['processed']}\n";
echo "Пропущено: {$stats['skipped']}\n";
echo "Ошибок: {" . join("\n", $stats['errors']) . "}\n";
