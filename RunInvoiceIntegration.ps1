#Powershell sucks.... Different encoding on all machines. Setting to UTF8. 
Copy-Item -Path "C:\tmp\nk01intma*" -Destination "D:\UBWIntegration"
Move-Item -Path "C:\tmp\nk01intma*" -Destination "C:\tmp\Fakturerat"
D:\php-7.3.6\php.exe index.php > results.txt
Add-Content results.txt "`n
Skicka ovan som en ett E-post meddelande till:
`ngeorgios.katsikiotis@nacka.se,
`nekonomisupport@nacka.se,
`nmalin.ahlstrom@nacka.se,
`n`nmed ämnesraden: Faktura filer från Kundserviceenheten"

notepad results.txt