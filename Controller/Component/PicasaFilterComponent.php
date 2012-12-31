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

  public function getName() {
    return "Picasa";
  }

  public function getExtensions() {
    return array('ini' => array('priority' => 4));
  }

  public function read($file, $media = null){
    
    $filename = $this->MyFile->getFilename($file);
    
    //path to picasa contacts.xml file
    $xml = Xml::build('/media/black/FOTO/2012 test picasa/picasa contacts/contacts.xml');   
    
    //http://book.cakephp.org/2.0/en/core-utility-libraries/xml.html
    $xmlArray = Xml::toArray($xml);
    $contacts = $xmlArray['contacts']['contact'];
    
    $picasaFiles = $this->_readPicasaIni($filename, $contacts);
    
    foreach ($picasaFiles as $picasaFile=>$faces) {
      $path = $file['File']['path'];
      $media = $this->FilterManager->_findMediaInPath($path, $path.$picasaFile);
      if (!$media) {
        continue;
      }
      $facesnames = array();
      foreach ($faces as $face) {
        $facesnames[]= $face['name'];
      }
      $values = array_unique($facesnames);
      $media['Field']['face'] = $values;
      
      if (!$this->Media->save($media)) {
        Logger::err("Could not save Media");
        Logger::trace($media);
        $this->FilterManager->addError($filename, 'MediaSaveError');
      } else {
        Logger::verbose("Updated media (id ".$media['Media']['id'].")");
      }
     $importLog = $this->FilterManager->_importlog($importLog, $path.$picasaFile);
    }
    
    $this->controller->MyFile->updateReaded($file);
    //$this->controller->MyFile->setFlag($file, FILE_FLAG_DEPENDENT);

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