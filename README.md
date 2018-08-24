
# beyond_importer  
GM Forge mod for importing characters from D&amp;D Beyond  
  
## Prerequisites    
* [Docker](https://www.docker.com/get-started) is installed
* GM Forge is installed and the beyond_importer mod is subscribed.
* Using the "Foundry Tactics" mod for your world.

## Getting the mod working
* Open the command line / Powershell / bash / terminal.
* Navigate to the GM Forge folder and then the beyond_importer mod folder.
  * \Steam\steamapps\common\GM Forge - Virtual Tabletop\public\workshop\beyond_importer
 * Run `docker-compose build`
 * Run `docker-compose up -d`
 * Run GM Forge and select a world.
 * Enable the beyond_importer mod from within GM Forge.

To kill the docker container later, you can run `docker-compose down`

## Importing D&D Beyond characters
For beyond_importer to work, the character must be public.  This is configured on the Edit Character page in D&D Beyond.  On the Home tab, ensure Character Privacy is set to Public.

To import characters, open the actor page in GM Forge.  The "Beyond Import" button will appear in the top menu bar.  Clicking the button will show an input box for a Character ID.  The character ID refers to the numerical ID in the address bar when viewing your D&D Beyond character.

https://<i></i>www.dndbeyond<i></i>.com/profile/Username/characters/**1122345**
