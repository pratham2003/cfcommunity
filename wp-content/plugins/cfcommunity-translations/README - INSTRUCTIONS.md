## TRANSLATION WORKFLOW

- Add new string to template + strings.php

- Generate a new .pot file
    $ grunt pot

- push the new pot file to Transifex
    $ tx push -s

- When translations for new strings are complete pull the updated PO files
    $ tx pull -a

- Generate the .mo files from the updated po files
    $ grunt po2mo

- Push the new changes to GitHub
    $ git push

- Pull  changes from live git install 
    $ git pull





