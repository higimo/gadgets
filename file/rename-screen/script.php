<?php

/**
 * Переименовывает файлы Screenshot_*.png в формат screen_YYYY_MM_DD.png
 * используя дату создания файла
 */
function renameScreenshots($folderPath) {
    if (!is_dir($folderPath)) {
        throw new Exception("Папка '$folderPath' не существует");
    }
    if (!is_writable($folderPath)) {
        throw new Exception("Нет прав на запись в папку '$folderPath'");
    }
    
    $files = glob($folderPath . "/Screenshot_*.png");
    
    if (empty($files)) {
        echo "Файлы по маске Screenshot_*.png не найдены\n";
        return 0;
    }
    
    echo "Найдено файлов: " . count($files) . "\n";
    $renamedCount = 0;
    
    foreach ($files as $filePath) {
        if (!file_exists($filePath)) {
            echo "Предупреждение: Файл " . basename($filePath) . " больше не существует\n";
            continue;
        }
        
        $creationTime = filectime($filePath);
        $creationDate = date('Y_m_d', $creationTime);
        
        $fileInfo = pathinfo($filePath);
        $newFileName = "screen_{$creationDate}.png";
        $newFilePath = $fileInfo['dirname'] . '/' . $newFileName;
        
        $counter = 1;
        while (file_exists($newFilePath)) {
            $newFileName = "screen_{$creationDate}_{$counter}.png";
            $newFilePath = $fileInfo['dirname'] . '/' . $newFileName;
            $counter++;
        }
        
        if (rename($filePath, $newFilePath)) {
            echo "✓ " . basename($filePath) . " -> " . $newFileName . "\n";
            $renamedCount++;
        } else {
            echo "✗ Ошибка: Не удалось переименовать " . basename($filePath) . "\n";
        }
    }
    
    echo "Обработано файлов: $renamedCount из " . count($files) . "\n";
    return $renamedCount;
}

try {
    $folderPath = '.';
    
    if (isset($argv[1])) {
        $folderPath = $argv[1];
    }
    
    echo "Обработка файлов в папке: " . realpath($folderPath) . "\n";
    renameScreenshots($folderPath);
    
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
    exit(1);
}
