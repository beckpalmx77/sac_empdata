@echo off
setlocal enabledelayedexpansion
:loop
echo เริ่มรัน script: %date% %time%
php import_data_to_att_time.php
echo เสร็จสิ้น script: %date% %time%
timeout /t 2000 /nobreak > NUL
goto :loop