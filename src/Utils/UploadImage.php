<?php


namespace App\Utils;


use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadImage
{
    public function save(String $name, ?UploadedFile $file, String $dir){
        $newFilename='';

        /**
         * @var UploadedFile $file
         */
        if($file){
            $newFilename = $name.'-'.uniqid().'.'.$file->guessExtension();
            $file->move($dir,$newFilename);
        }else {
            $default = rand(0,1);
            if($default === 0){
                $newFilename='generic-woman-heroe.jpeg';
            }else{
                $newFilename='generic-man-heroe.jpeg';
            }
        }

        return $newFilename;
    }
}