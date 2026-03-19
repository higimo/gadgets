@echo off
chcp 65001 >nul
echo Starting folder scanner...
powershell -ExecutionPolicy Bypass -File "folder_sizes.ps1" > scan.txt
@REM pause