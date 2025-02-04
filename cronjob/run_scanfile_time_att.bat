@echo off
setlocal enabledelayedexpansion
:loop
echo เริ่มรัน script: %date% %time%
php scan_import_file_time_attendance.php
echo เสร็จสิ้น script: %date% %time%
timeout /t 10800 /nobreak > NUL
goto :loop