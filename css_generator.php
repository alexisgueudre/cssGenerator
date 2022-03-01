#!/usr/bin/env php
<?php

$shortopts = "i:";
$shortopts .= "s:";
$shortopts .= "r:";
$longopts = array(
    "output-image:",
    "output-style:",
    "recursive:"
);
$options = getopt($shortopts,$longopts);
$arrayFiles = array();
function start (&$argv, $options,&$arrayFiles)
{

    $pathDir = $argv;
    foreach ($pathDir as $values)
    {
        if (is_dir($values))
        {
           $handles= opendir($values);
           while (($content = readdir($handles)) !== false)
           {
               if (preg_match('/.png$/',$content))
               {

                   $arrayFiles[] = $values."/".$content;
               }
               if ((!preg_match('/(^[.]$|^[.]{2}$|^.DS_Store$)/',$content) && is_dir($values."/".$content)) && (array_key_exists("r",$options) || array_key_exists("recursive",$options)))
               {
                    pathDir($values."/".$content,$options,$arrayFiles);
               }
           };
        }
    }
    spriteOranginaCoca($arrayFiles,$options);
    cssGenerator($arrayFiles);
    return $arrayFiles;
}
function pathDir($pathDir,$options,&$arrayFiles)
{
        if (is_dir($pathDir))
        {
            $dirOpen = opendir($pathDir);
            $sizeWidth = 0;
            $sizeheight = 0;
            while (($file = readdir($dirOpen)) !== false)
            {
                if (!preg_match('/(^[.]$|^[.]{2}$|^.DS_Store$)/',$file) && is_dir($pathDir."/".$file."/"))
                {
                    $pathDir.="/".$file;
                    pathDir($pathDir,$options,$arrayFiles);

                }
                if (preg_match('/.png$/',$file))
                {
                    $arrayFiles[] = $pathDir."/".$file;
                }
            }
            closedir($dirOpen);

        }
    return $arrayFiles;
}

function spriteOranginaCoca($arrayFiles,$options)
{

    static $height = 0;
    static $width = 0;
    foreach ($arrayFiles as $file)
    {
        $openImg = imagecreatefrompng($file);
        $width += imagesx($openImg);
        $height = imagesy($openImg);
    }
    $newImg = imagecreatetruecolor($width,$height);

       static $sizeSpace = 0;
        foreach($arrayFiles as $value)
        {
            $pathfile = imagecreatefrompng($value);
            $size = getimagesize($value);
            imagecopy($newImg,$pathfile,$sizeSpace,0,0,0, $size[0],$size[1]);
            $sizeSpace += $size[0];
        }
        if (array_key_exists("i",$options))
        {
            imagepng($newImg,$options["i"].".png");;
        }
        elseif (array_key_exists("output-image",$options))
        {
            imagepng($newImg,$options["output-image"].".png");;
        }
        else
            {
                imagepng($newImg,"sprite.png");
            }
        imagedestroy($newImg);
}

function cssGenerator($pathArray)
{

   global $options;

    $iMage = 1;
    $i = 1;
    static $positionX = 0;
    if (array_key_exists("s",$options))
    {
        $handle = fopen($options["s"].".css","x");
    }
    elseif (array_key_exists("output-style",$options))
    {
        $handle = fopen($options["output-style"].".css","x");
    }
    else
    {
        $handle = fopen("style.css","x");
    }
    $string = "" ;
    foreach ($pathArray as $values)
    {

        if ($values == end($pathArray))
        {
            $string .= ".image";
            $string .= $iMage;
            $string .= "\n"." { ";

        }else
            {
                $string .= ".image";
                $string .= $iMage;
                $string .= ", ";
            }
        $iMage++ ;
    }

    if (array_key_exists("i",$options))
    {
        fwrite($handle,$string." \n  background: url('".$options["i"].".png') \n } ");
    }
    if (array_key_exists("output-image",$options))
    {
        fwrite($handle,$string." \n  background: url('".$options["output-image"].".png') \n }");
    }
    if (!array_key_exists("i",$options) && !array_key_exists("output-image",$options))
        {
            fwrite($handle,$string."\n  background: url('sprite.png') \n }");
        }
    foreach ($pathArray as $value)
    {
        $size = getimagesize($value);
        $width = $size[0];
        $height = $size[1];
        fwrite($handle, "\n.image" . $i . "{" . PHP_EOL .
            str_repeat(" ", 2) . "width: " . $width . "px;" . PHP_EOL .
            str_repeat(" ", 2) . "height: " . $height . "px;" . PHP_EOL .
            str_repeat(" ", 2) . "background-position: -" . $positionX ."px"." 0px".PHP_EOL .
            "}"
        );
        $positionX += $width;
        $i++;
    }
    fclose($handle);
}
start($argv,$options,$arrayFiles);