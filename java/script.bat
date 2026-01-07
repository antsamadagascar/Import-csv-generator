@echo off

REM Set the bin directory
set bin=.\bin

REM Create the temp directory if it doesn't exist
mkdir temp

REM Copy Java files to temp directory
xcopy /E /I /Y source\phpCrud\*.java "temp"
xcopy /E /I /Y source\connection\*.java "temp"

REM Compile the Java files in the temp directory
javac --release 8 -d "%bin%" temp\*.java

REM Check if the compilation was successful
if errorlevel 1 (
    echo Compilation failed.
    exit /b 1
)

REM Change to the bin directory to execute the class
cd %bin%

REM Run the PhpCodeGenerator class
java connection.MysqlConnection
java phpCrud.PhpCodeGenerator 
java phpCrud.PhpGeneratePage

REM Change back to the original directory
cd ..

REM Clean up the temp directory
rmdir /S /Q temp
