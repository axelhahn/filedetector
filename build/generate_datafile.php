<?php

/*
 * ----------------------------------------------------------------------
 * F I L E D E T E C T O R  class
 * 
 * Class detect filetype by its signature data
 *
 * signatures are from here:
 * https://www.garykessler.net/software/index.html#filesigs
 * 
 * ----------------------------------------------------------------------
 * 
 *  ***** file_sigs_RAW.txt *****
 * 
 * file_sigs_RAW.txt is the set of all file signatures in a comma-delimited text file. Each file has six fields:
 * 
 *    Description,Header (hex),Extension,FileClass,Header_offset,Trailer (hex)
 * 
 * As an example, a JPEG file entry looks like:
 * 
 *    JPEG/EXIF/SPIFF images,FF D8 FF,JFIF/JPE/JPEG/JPG,Picture,0,FF D9
 * 
 * ----------------------------------------------------------------------
 * 
 * C O N V E R T E R ... 
 * reads raw file and creates a reorganized serialized file that is used by the class.
 *  
 * ----------------------------------------------------------------------
 */
$sInfile='FileSigs_20200424/file_sigs_RAW.txt';
// $sOutfile='../src/filedetector.sigs.json';
$sOutfile2='../src/filedetector.sigs.txt';

$delimiter= ',';
$aSig=array();

// ----------------------------------------------------------------------
// READ
// ----------------------------------------------------------------------
$handle = fopen($sInfile, 'r');
while (($data = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
    $aItem=array(
        'description'=>$data[0],
        'extension'=>$data[2],
        'fileclass'=>$data[3],
        'offset'=>$data[4],
        'header'=>trim($data[1]),
        'trailer'=>isset($data[5]) ? $data[5] : '',
    );

    if(!isset($aSig[$aItem['header']])){
        $aSig[$aItem['header']]=array();
    }
    $aSig[$aItem['header']][]=$aItem;

    /*
    // generate index item
    if(!isset($this->_aSigIndex[$aItem['offset']])){
        $this->_aSigIndex[$aItem['offset']]=array();
    }
    $oRef=&$this->_aSigIndex[$aItem['offset']];
    foreach (explode(' ', $aItem['header']) as $sKey){
        if(!isset($oRef[$sKey])){
            $oRef[$sKey]=array();
        }
        $oRef=&$oRef[$sKey];
    }
    $oRef['signature']=$aItem;
    // $this->_aSigIndex[]=array();
     * 
     */
}
fclose($handle);
krsort($aSig);

// ----------------------------------------------------------------------
// WRITE
// ----------------------------------------------------------------------
// file_put_contents($sOutfile, json_encode($aSig));
file_put_contents($sOutfile2, serialize($aSig));


// ----------------------------------------------------------------------