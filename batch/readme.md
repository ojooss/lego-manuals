# Stapelverarbeitung von LEGO Sets
1) Set-Nummern in `set-numbers-txt` eintragen. (Es muss eine leere Zeile am Ende der Datei bleiben.)
2) Durch Ausführen von `build-and-run.bat` werden die HTML-Seiten der Sets von LEGO.com geladen und im Ordner `results` abgelegt.
3) Zuletzt noch `php parse.php` aufrufen. Dieses Skript analysiert die HTML-Seiten und sucht nach dem Namen des Sets und den URLs zu den PDF-Dateien der Anleitungen. Diese werden dann der Datenbank hinzugefügt.
