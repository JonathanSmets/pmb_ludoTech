Information concernant de module SabreDAV :
 -> http://code.google.com/p/sabredav/

Pour le mettre � jour dans PML :
  - T�l�charger la derni�re version
  - Copier les dossiers lib et vendor dans ce dossier (s'assurer que le dossier lib/Sabre/PML n'est pas �cras�)
  - Dans le dossier vendor/sabre/vobject ne garder que le dossier lib
  - Modifier le fichier autoload_namespaces.php du dossier vendor/composer pour ajouter " 'Sabre\\PML' => $baseDir . '/lib/', " � la fin du tableau

Et normalement tout devrait fonctionner...

