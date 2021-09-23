# PHP CLASS FILEDETECTOR

## Description

This class can detect the filetype by its content (header and trailer).
A file can match several file definitions. Zip is a container used in zip 
archives, office documents, software packages and other. Then you get the 
results in order with the highest rankings on top.

The base for the decision are the signatures from
<https://www.garykessler.net/software/index.html#filesigs> 

  * Author: Axel Hahn
  * License: GNU GPL 3.0 (free software and open source)
  * Source: https://github.com/axelhahn/class-csv2hash

## Examples

Initialization:

```php
require './src/filedetector.class.php';
$oFD=new axelhahn\filedetector();
```

Example: reference an existing local file

```php
// set a file to analyze
$oFD->setFile($sMyfile); 
```

With the dump method you get some detected internal details:

```php
// show extension, header and trailer bytes of the file
echo $oFD->dump(); 
```

```
Filename    : build/FileSigs_20200424/FTK_sigs_GCK.zip
Extension   : ZIP
First bytes : 50 4B 03 04 0A 00 00 00 00 00 3A 77 98 50 00 00 00 00 00 00
Last bytes  : 05 06 00 00 00 00 99 04 99 04 BF DA 01 00 53 44 05 00 00 00
```

The method getType returns the array of all file types matching first and last bytes.

```php
// get all matching filetypes
print_r($oFD->getType());
```

It returns the score in the first subkey (here: "6") containing an array with details 
about filetype and detection values.

```
Array                                                                     
(                                                                       
    [6] => Array                                                        
        (                                                               
            [0] => Array                                                
                (                                                       
                    [description] => PKZIP archive_1                    
                    [extension] => ZIP                                  
                    [fileclass] => Compressed archive                   
                    [offset] => 0                                       
                    [header] => 50 4B 03 04                             
                    [trailer] => 50 4B ????????????????? 00 00 00       
                    [_scoring] => Array                                 
                        (                                               
                            [signature] => 5                            
                            [extension] => 1                            
                            [trailer] => 0                              
                        )                                               
                                                                        
                )                                                       
                                                                        
            [1] => Array                                                
                (                                                       
                    [description] => MacOS X Dashboard Widget           
                    [extension] => ZIP                                  
                    [fileclass] => MacOS                                
                    [offset] => 0                                       
                    [header] => 50 4B 03 04                             
                    [trailer] => (null)                                 
                    [_scoring] => Array                                 
                        (                                               
                            [signature] => 5                            
                            [extension] => 1                            
                            [trailer] => 0                              
                        )                                               
                                                                        
                )                                                       
                                                                        
        )                                                               
                                                                        
    [5] => Array                                                        
        (                                                               
            [0] => Array                                                
                (                                                       
                    [description] => Android package                    
					(...)
```

To return just the best match you can use the parameter true

```php
// get all matching filetypes
print_r($oFD->getType(true));
```

```
Array
(
    [description] => PKZIP archive_1
    [extension] => ZIP
    [fileclass] => Compressed archive
    [offset] => 0
    [header] => 50 4B 03 04
    [trailer] => 50 4B ????????????????? 00 00 00
    [_scoring] => Array
        (
            [signature] => 5
            [extension] => 1
            [trailer] => 0
        )

)
```


Example: you can try to detect a file type by a few first bytes
as hexadecimal values - with or without spaces:

```php
$oFD->setSignature('11 22 33 44 66 74 79 70 69 73 6F 6D');
echo $oFD->dump();
```

```
Filename    :                                    
Extension   :                                    
First bytes : 11 22 33 44 66 74 79 70 69 73 6F 6D
Last bytes  :                                    
```

```php
print_r($oFD->getType());
```

```
Array                                                         
(                                                                   ```
    [5] => Array                                                    
        (                                                           
            [0] => Array                                            
                (                                                   
                    [description] => ISO Base Media file (MPEG-4) v1
                    [extension] => MP4                              
                    [fileclass] => Multimedia                       
                    [offset] => 4                                   
                    [header] => 66 74 79 70 69 73 6F 6D             
                    [trailer] => (null)                             
                    [_scoring] => Array                             
                        (                                           
                            [signature] => 5                        
                            [extension] => 0                        
                            [trailer] => 0                          
                        )                                           
                                                                    
                )                                                   
                                                                    
        )                                                           
                                                                    
)
```
