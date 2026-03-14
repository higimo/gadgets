#!/bin/bash

BASE_DIR="/Users/higimo/home"

# Проверяем существование директории
if [ ! -d "$BASE_DIR" ]; then
    echo "Error: Directory $BASE_DIR does not exist!"
    exit 1
fi

cd "$BASE_DIR"

# Проходим по всем подпапкам
for dir in */; do
    # Убираем слеш в конце имени папки
    dir=${dir%*/}
    
    # Проверяем наличие .git директории
    if [ -d "$dir/.git" ]; then
        echo "========================================"
        echo "Processing repository: $dir"
        echo "========================================"
        
        cd "$dir"
        
        # Выполняем git pull
        git pull
        
        # Проверяем код возврата
        if [ $? -ne 0 ]; then
            echo "WARNING: Git pull failed in \"$dir\""
        fi
        
        cd "$BASE_DIR"
        echo
    fi
done

echo "========================================"
echo "Git pull completed for all repositories"
echo "========================================"