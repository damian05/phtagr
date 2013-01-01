<?php
/**
 * PHP versions 5
 *
 * phTagr : Tag, Browse, and Share Your Photos.
 * Copyright 2006-2012, Sebastian Felis (sebastian@phtagr.org)
 *
 * Licensed under The GPL-2.0 License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2006-2012, Sebastian Felis (sebastian@phtagr.org)
 * @link          http://www.phtagr.org phTagr
 * @package       Phtagr
 * @since         phTagr 2.2b3
 * @license       GPL-2.0 (http://www.opensource.org/licenses/GPL-2.0)
 */


App::uses('BaseFilter', 'Component');

class PicasaFilterComponent extends BaseFilterComponent {
  var $controller = null;
  var $components = array('FileManager');
  var $picasaContactsCache = array();

  public function getName() {
    return "Picasa";
  }

  public function getExtensions() {
    return array('ini' => array('priority' => 4));
  }

  /**
   * Read picasa contacts.xml file.
   *
   * @return array of Picasa contacts
   */
  private function _readPicasaContacts() {
    //path to picasa contacts.xml file
    $path = '/media/black/FOTO/2012 test picasa/picasa contacts/contacts.xml';

    if (!isset($this->picasaContactsCache[$path])) {
      $xml = Xml::build($path);   
      //http://book.cakephp.org/2.0/en/core-utility-libraries/xml.html
      $xmlArray = Xml::toArray($xml);
      if (isset($xmlArray['contacts']['contact'])) {
        $this->picasaContactsCache[$path] = $xmlArray['contacts']['contact'];
      } else {
        $this->picasaContactsCache[$path] = false;
      }
    }
    return $this->picasaContactsCache[$path];
  }
  
  public function read($file, $media = null){
    $filename = $this->MyFile->getFilename($file);

    $readed = strtotime($file['File']['readed']);

    //TODO: change FilterManager in order to skip files readed, not changed and without media?? and delete next section
    // Check changes
    $fileTime = filemtime($filename);
    $dbTime = strtotime($file['File']['time']);
    //Logger::debug("db $dbTime file $fileTime");
    $forceRead = false;
    if ($fileTime > $dbTime || $fileTime > $readed) {
      Logger::warn("File '$filename' was changed without notice of phTagr! Read the file again.");
      // @todo Action if file is newer than phtagr. Eg. force rereada
      $forceRead = true;
    }
    if ($readed && !$forceRead) {
      Logger::verbose("File '$filename' already readed. Skip reading!");
      $this->FilterManager->skipped[$filename] = 'skipped';
      //return $media;//overload memory with 20kb for each file
      return $filename;//around 0.2kb
    }

    $contacts = $this->_readPicasaContacts();
    $picasaFiles = $this->_readPicasaIni($filename, $contacts);
    if (!$contacts || !count($picasaFiles)) {
      return false;
    }
    $path = $file['File']['path'];

    foreach ($picasaFiles as $picasaFile=>$faces) {
      $media = $this->FilterManager->_findMediaInPath($path, $path.$picasaFile);
      if (!$media) {
        continue;
      }

      $picasaFacesNames = array();
      foreach ($faces as $face) {
        if (isset($face['name'])) {
          //keep only faces with names in contacts.xml, i.e. known faces
          $picasaFacesNames[]= $face['name'];
        }
      }
      $picasaFacesNamesUnique = array_unique($picasaFacesNames);
      $dbfaces = Set::extract("/Field[name=face]/data", $media);
      if (count(array_diff($picasaFacesNamesUnique, $dbfaces)) or count(array_diff($dbfaces, $picasaFacesNamesUnique))) {
        $media['Field']['face'] = $picasaFacesNamesUnique;

        if (!$this->Media->save($media)) {
          Logger::err("Could not save Media");
          Logger::trace($media);
          $this->FilterManager->addError($path.$picasaFile, 'MediaSaveError');
        } else {
          Logger::verbose("Updated media (id ".$media['Media']['id'].")");
        }
      }
      $importLog = $this->FilterManager->_importlog($importLog, $path.$picasaFile);
    }
    
    $this->controller->MyFile->update($file);
    $this->controller->MyFile->updateReaded($file);
    //$this->controller->MyFile->setFlag($file, FILE_FLAG_DEPENDENT);
    return $filename;
  }

  private function _readPicasaIni($picasaIni, $contacts){
    $picasaFiles = array();
    foreach (file($picasaIni) as $line) {
      $line = preg_replace("/\r\n|\r|\n/", '', $line);

      //[7K0_2092.JPG]
      if (substr($line,0,1)=='[' && substr($line,strlen($line)-1,1)==']') {
        $file=substr($line,1,strlen($line)-2);
      }
      
      //faces=rect64(ab0b5d94b7d374c2),5d6c030e3c574c0e;rect64(72db827f7dc69652),d613c673ab75a637
      if (substr($line,0,6)=='faces=') {
        $faces = substr($line,6,strlen($line)-6);
        $faces = split( ';', $faces);
        foreach ($faces as $face) {
          $face = split( ',', $face);
          $crtface = array();
          $crtface['id'] = $face[1];
          $crtface['rect64'] = substr($face[0],7,strlen($face[0])-8);
          foreach ($contacts as $contact) {
            if ($crtface['id'] == $contact['@id']) {
              $crtface['name'] = $contact['@name'];
            }
          }
          $picasaFiles[$file][] = $crtface;
        }
      }
    }
    return $picasaFiles;
  }
  
}