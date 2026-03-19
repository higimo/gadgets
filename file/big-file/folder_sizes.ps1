# ===== НАСТРОЙКИ =====
$RootPath = "C:\"
$MaxDepth = 7
$MinSizeMB = 5
$ShowProgress = $true

# ИГНОР-ЛИСТ папок, которые не попадут в вывод
$IgnoreList = @(
    "node_modules",
    "\.git",
    "\.svn",
    "\.vs",
    "tmp",
    "temp",
    "cache",
    "logs",
    "Windows"
)

# ===== ФУНКЦИЯ ПРОВЕРКИ ИГНОР-ЛИСТА =====
function Should-Ignore {
    param([string]$Path)
    
    # Получаем только имя папки, а не полный путь
    $folderName = [System.IO.Path]::GetFileName($Path)
    
    foreach ($pattern in $IgnoreList) {
        # Проверяем только имя папки, а не полный путь
        if ($folderName -like $pattern -or $folderName -match $pattern) {
            return $true
        }
    }
    return $false
}

# ===== ФУНКЦИЯ ФОРМАТИРОВАНИЯ РАЗМЕРА =====
function Format-Size {
    param([long]$Size)
    
    if ($Size -ge 1GB) {
        $gb = [math]::Round($Size / 1GB, 1)
        return "$gb Gb"
    } elseif ($Size -ge 1MB) {
        $mb = [math]::Round($Size / 1MB, 1)
        return "$mb Mb"
    } elseif ($Size -ge 1KB) {
        $kb = [math]::Round($Size / 1KB, 1)
        return "$kb Kb"
    } else {
        return "$Size b"
    }
}

# ===== ФУНКЦИЯ РЕКУРСИВНОГО СКАНИРОВАНИЯ =====
function Scan-Folder {
    param(
        [string]$FolderPath,
        [int]$CurrentDepth = 0
    )
    
    $results = @()
    
    # Пропускаем если превышена глубина или папка в игнор-листе
    if ($CurrentDepth -gt $MaxDepth -or (Should-Ignore -Path $FolderPath)) {
        return $results
    }
    
    try {
        # ВОТ ИСПРАВЛЕНИЕ: рекурсивно получаем размер всей папки с подпапками
        $size = (Get-ChildItem $FolderPath -Recurse -File -ErrorAction SilentlyContinue | 
                Measure-Object -Property Length -Sum).Sum
        
        # Добавляем текущую папку в результаты если она достаточно большая
        if ($size -gt ($MinSizeMB * 1MB)) {
            $results += [PSCustomObject]@{
                Path = $FolderPath
                Size = $size
                FormattedSize = Format-Size -Size $size
                Depth = $CurrentDepth
            }
        }
        
        # Рекурсивно сканируем подпапки
        if ($CurrentDepth -lt $MaxDepth) {
            $subFolders = [System.IO.Directory]::GetDirectories($FolderPath)
            foreach ($subFolder in $subFolders) {
                # Рекурсивно вызываем для подпапок
                $subResults = Scan-Folder -FolderPath $subFolder -CurrentDepth ($CurrentDepth + 1)
                $results += $subResults
            }
        }
    } catch {
        # Игнорируем ошибки доступа
    }
    
    return $results
}

# ===== ОСНОВНОЙ СКРИПТ =====
Write-Host "Scanning $RootPath (max depth: $MaxDepth)..." -ForegroundColor Yellow
Write-Host "Ignore list: $($IgnoreList -join ', ')" -ForegroundColor Cyan
Write-Host ""

$startTime = Get-Date

# Сканируем папки первого уровня
$topFolders = [System.IO.Directory]::GetDirectories($RootPath)
$allResults = @()

$i = 0
foreach ($folder in $topFolders) {
    $i++
    if ($ShowProgress) {
        Write-Progress -Activity "Scanning folders" -Status "$i/$($topFolders.Count) - $([System.IO.Path]::GetFileName($folder))" -PercentComplete (($i / $topFolders.Count) * 100)
    }
    
    # Пропускаем папки из игнор-листа
    if (-not (Should-Ignore -Path $folder)) {
        $folderResults = Scan-Folder -FolderPath $folder -CurrentDepth 1
        $allResults += $folderResults
    }
}

if ($ShowProgress) {
    Write-Progress -Activity "Scanning folders" -Completed
}

# ===== ВЫВОД РЕЗУЛЬТАТОВ =====
$endTime = Get-Date
$duration = $endTime - $startTime

Write-Host ""
Write-Host "Scan completed in $([math]::Round($duration.TotalSeconds, 1)) seconds" -ForegroundColor Green
Write-Host "Found $($allResults.Count) folders larger than ${MinSizeMB}MB" -ForegroundColor Green
Write-Host ""

# Сортируем по размеру (по убыванию) и выводим
$sortedResults = $allResults | Sort-Object Size -Descending

foreach ($result in $sortedResults) {
    # Добавляем отступы для визуализации глубины
    $indent = "  " * ($result.Depth - 1)
    Write-Host "$indent$($result.Path)" -NoNewline
    Write-Host " .... " -NoNewline
    Write-Host $result.FormattedSize -ForegroundColor Cyan
}

Write-Host ""
Write-Host "Total scan time: $([math]::Round($duration.TotalSeconds, 1)) seconds" -ForegroundColor Yellow
