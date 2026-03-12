<?php

if ($argc < 2) {
    echo
        "Скрипт для добавления ссылки на MOC во все markdown-файлы внутри папки\n"
        . "Использовать так\n"
        . "php -f obsidian.add_moc_in_folder.php /obsidian/folder '!Проекты'\n\n"
    ;
    exit(1);
}

// TODO: спамит, что не хватает аргументов
$directory = $argv[1];

// $mockLink = "[[$mocName]]";
$stats = [
    'processed' => 0,
    'skipped' => 0,
    'errors' => []
];

if (!is_dir($directory)) {
    echo "Папки '$directory' не существует";
    exit(1);
}

$isRecursive = true;

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

/**
 * Отдаёт название MOC файла, если не нашёл — бросает исключение (чтоб знать, где надо завести мок)
 */
function getMocName(string $filePath): string {
    $currentDir = dirname($filePath);
    $baseName = basename(dirname($filePath));;

    // Ищем MOC в каждой дирректории начиная с текущей и до уровня выше
    while ($currentDir !== dirname($currentDir)) {
        $mocFile = $currentDir . DIRECTORY_SEPARATOR . '!' . $baseName . '.md';
        
        if (file_exists($mocFile)) {
            return pathinfo($mocFile, PATHINFO_FILENAME);
        }
        
        $currentDir = dirname($currentDir);
        $baseName = basename($currentDir);;
    }
    
    throw new RuntimeException("Нет MOC-файла для '{$filePath}', надо его создать");
}
$i = 0;
foreach ($iterator as $file) {
    if ($file->getExtension() !== 'md') {
        continue;
    }

    $filePath = $file->getPathname();

    try {
        $mocName = getMocName($filePath);

        if (++$i > 50) {
            break;
        }

        // Скипаем сам моки
        if ($file->getFilename() === $mocName . ".md") {
            $stats['skipped']++;
            continue;
        }

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

        $mocContent = ($start > 0 ? "\n\n" : '') . "[[$mocName]]\n\n";
        $content = substr($content, 0, $start) . $mocContent . substr($content, $start, strlen($content));

        if (file_put_contents($filePath, $content)) {
            $stats['processed']++;
        } else {
            echo "❌ ошибка записи\n";
            $stats['errors'][] = $filePath;
        }
    } catch (Exception $error) {
        $stats['errors'][] = $error->getMessage();
    }
}

echo "\n" . str_repeat('-', 40) . "\n";
echo "Обработано файлов: {$stats['processed']}\n";
echo "Пропущено: {$stats['skipped']}\n";
echo "Ошибок: {" . join("\n", $stats['errors']) . "}\n";
