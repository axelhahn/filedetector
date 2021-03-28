<?php

declare(strict_types=1);
namespace axelhahn;

// class Exception extends Exception { }

/**
 * ----------------------------------------------------------------------
 * F I L E D E T E C T O R  class
 * 
 * Class detect filetyp by its signature data
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
 * ----- performance test for signature data:
 * 9.35 ms - generate array from raw file (588 lines)
 * 1.22 ms - read from pre generated json file
 * 0.57 ms - read from pre generated serialized file
 * 
 * ----------------------------------------------------------------------
 * @package filedetctor
 * @version 0.00
 * @author Axel Hahn (https://www.axel-hahn.de/)
 * @license GNU GPL v 3.0
 * @link https://github.com/axelhahn/ahcli
 */
class filedetector {

    var $_iFromStart=20;
    var $_iLastChars=20;
    
    var $_sFilename = false;
    var $_sFileext = false;
    var $_sFileStart = '';
    var $_sFileEnd = '';
    
    var $_sHex = '';
    var $_sAsc = '';

    
    // data for file signatures
    var $_aSig = array();
    var $_aSigIndex = array();
    
    var $_sSignatureFile2='filedetector.sigs.txt';
    
    // ----------------------------------------------------------------------
    // 
    // ----------------------------------------------------------------------
    public function __construct() {
        $this->loadSignatures();
        return true;
    }

    // ----------------------------------------------------------------------
    // PROTECTED FUNCTIONS
    // ----------------------------------------------------------------------
    
    
    /**
     * load signature data
     * 
     * performance test for signature data:
     * 9.35 ms - generate array from raw file (588 lines)
     * 1.22 ms - read from pre generated json file
     * 0.57 ms - read from pre generated serialized file
     * 
     * --> I use the php serialize function :-)
     * 
     * @return boolean
     */
    protected function loadSignatures(){
        if(!file_exists(__DIR__.'/'.$this->_sSignatureFile2)){
            die(__METHOD__. ' CRITICAL ERROR: signatures file does not exist: '.$this->_sSignatureFile2);
        }
        $this->_aSig = unserialize(file_get_contents(__DIR__.'/'.$this->_sSignatureFile2));
        return true;
    }

    /**
     * generate string of pretty hex code from a given string;
     * hex code is uppercase with a space after 2 chars
     * 
     * @param string $sText
     * @return string
     */
    protected function _strToHex($sText){
        return $this->_prettyHexcode(bin2hex($sText));
    }
    protected function _strToAsciiDump($sText){
        return trim(preg_replace('/(.)/', "$1 |", $sText));
    }
    
    /**
     * prettify given hex code
     * 
     * @param string $sHexcode  hexcode
     * @return string
     * @throws Exception
     */
    protected function _prettyHexcode($sHexcode){
        $sReturn=strtoupper($sHexcode);
        
        if(!preg_match('/[0-9A-F\ ]*/', $sReturn)){
            throw new Exception('Param must be hex code.');
        }
        return trim(preg_replace('/(.{2})/', "$1 ", str_replace(' ', '', $sReturn)));
    }

    
    // ----------------------------------------------------------------------
    // PUBLIC functions :: dumps
    // ----------------------------------------------------------------------
    
    /**
     * dump information to scan; returns a string with filename, extension,
     * fiirst and last bytes.
     * You need to echo the return value to display it.
     * 
     * @return string
     */
    public function dump(){
        $sReturn='';
        $sReturn.='--- DUMP'.PHP_EOL
            . 'Filename    : '.$this->_sFilename.PHP_EOL
            . 'Extension   : '.$this->_sFileext.PHP_EOL
            . 'First bytes : '.$this->_sFileStart.PHP_EOL
            . 'Last bytes  : '.$this->_sFileEnd.PHP_EOL
            ;
        return $sReturn;
    }
    
    /**
     * get the array of signature data source
     * 
     * @return array
     */
    public function getSignatures(){
        return $this->_aSig;
    }


    // ----------------------------------------------------------------------
    // PUBLIC functions :: setter
    // ----------------------------------------------------------------------

    /**
     * set a filename to read
     * @param string  $filename  a filename to read
     * @return boolean
     * @throws Exception
     */
    public function setFile($filename){
        if(!file_exists($filename) || !is_file($filename)){
            throw new Exception('A filename of an existing file is required.');
        }
        

        // prepare some infos:
        $ext= pathinfo($filename, PATHINFO_EXTENSION);
        
        $handle = fopen($filename, "rb");
        $sFilestart = fread($handle, $this->_iFromStart);
        
        fseek($handle, -($this->_iLastChars), SEEK_END);
        $sFileend = fread($handle, $this->_iLastChars);

        fclose($handle);

        $this->setContent($sFilestart, $sFileend, $ext);

        // set filename
        $this->_sFilename=$filename;
        
        return true;
    }


    /**
     * set file content (or its first byte only) to detect its type
     * @param type $sFilestart
     * @return boolean
     * @throws Exception
     */
    public function setContent($sFilestart='', $sFileend='', $ext=''){
        $sFilestart=(string)$sFilestart;
        if(strlen($sFilestart)<4){
            throw new Exception('Data size of file is too small - it must be minimum 4 bytes.');
        }
        
        $this->_sFilename=false;
        $this->_sFileext= strtoupper((string)$ext);
        
        $this->_sFileStart=$this->_strToHex($sFilestart);
        $this->_sFileEnd=$this->_strToHex((string)$sFileend);

        return true;
    }
    
    /**
     * set a signature 
     * @param string  $sHex  Hexcode "NN NN NN" with or without spaces
     * @return boolean
     * @throws Exception
     */
    public function setSignature($sHexStart='', $sHexEnd='', $ext=''){
        $this->_sFilename=false;
        $this->_sFileext=(string)$ext;
        $this->_sFileStart=$this->_prettyHexcode($sHexStart);
        $this->_sFileEnd=$this->_prettyHexcode($sHexEnd);

        return true;
    }
 

    // ----------------------------------------------------------------------
    // PUBLIC functions :: getter
    // ----------------------------------------------------------------------

    /**
     * get type of set file (or signature)
     * @return array
     */
    public function getType($bBestMatchOnly=false){
        $aReturn=array();
        foreach ($this->_aSig as $aItems){
            foreach ($aItems as $aItem){
                $iRanking=0;
                $aMatches=array();
                
                $aMatches['signature']=(stripos((string)substr($this->_sFileStart, (int)$aItem['offset']*3), $aItem['header'])===0) ? 5 : 0;
                $aMatches['extension']=(stripos($aItem['extension'], $this->_sFileext)!==false) ? 1 : 0;
                $aMatches['trailer']= ($aItem['trailer'] && strpos($this->_sFileEnd, (string)$aItem['trailer'])) ? 3 : 0;
                
                if ($aMatches['signature']>0){
                    $iRanking=$aMatches['signature'] + $aMatches['extension'] + $aMatches['trailer'];
                    if(!isset($aReturn[$iRanking])){
                        $aReturn[$iRanking]=array();
                    }
                    $aItem['_scoring']=$aMatches;
                    /*
                    $aItem['_tested']=array(
                        'firstbytes'=>$this->_sFileStart,
                        'lastbytes'=>$this->_sFileEnd,
                    );
                     */
                    $aReturn[$iRanking][]=$aItem;
                }
            }
        }
        krsort($aReturn);
        if($bBestMatchOnly && count($aReturn)){
            $aTmp=reset($aReturn);
            return reset($aTmp);
        }
        return $aReturn;
    }
    
}
