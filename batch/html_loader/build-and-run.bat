@echo off
REM Wechselt in das Verzeichnis, in dem das Skript liegt
cd /d %~dp0

docker build  . --tag=dld_lgo_pag:tmp
docker run --rm -v %cd%:/app dld_lgo_pag:tmp
