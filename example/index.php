<?php


require '../src/filedetector.class.php';

$oFD=new axelhahn\filedetector();

// ----------------------------------------------------------------------
// test 1: set a hex sginature of a file start
// ----------------------------------------------------------------------
$oFD->setSignature('11 22 33 44 66 74 79 70 69 73 6F 6D');
echo $oFD->dump();
echo 'Type: ';
print_r($oFD->getType());
echo '----------'.PHP_EOL;

// ----------------------------------------------------------------------
// test 2: set existing real world files to detect them
// ----------------------------------------------------------------------
try{
    foreach(array(
        '../build/FileSigs_20200424/FTK_sigs_GCK.zip',
        '../build/FileSigs_20200424/GKA_software_license.pdf'
    ) as $sMyfile){
        $oFD->setFile($sMyfile); 
        echo $oFD->dump(); 
        print_r($oFD->getType());
        echo '----------'.PHP_EOL;        
        print_r($oFD->getType(true));
        echo '----------'.PHP_EOL;        
    }
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}


// print_r($oFD->getSignatures());

