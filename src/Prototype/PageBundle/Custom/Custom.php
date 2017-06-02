<?php

namespace Prototype\PageBundle\Custom;

/**
 * Generic functions to be used by any controller
 * These functions are for renaming file uploads in an SEO friendly way
 *
 * BUTTY: System now uses a service container and injected into controllers - these function should really be moved
 *
 */

class Custom
{
    public function generateFilename($path, $filename){

        if ($pos = strrpos($filename, '.')) {
            $name = substr($filename, 0, $pos);
            $ext = substr($filename, $pos);
        } else {
            $name = $filename;
        }

        $name = $this->slugify($name);

        $newpath = $path.'/'.$name.$ext;
        $newname = $name.$ext;
        $counter = 0;

        while (file_exists($newpath)) {
            $newname = $name .'-'. $counter . $ext;
            $newpath = $path.'/'.$newname.$ext;
            $counter++;
        }

        return $newname;
    }


    public function slugify($text){
        $text = str_replace("'", "", $text);
        $text = preg_replace('~[^\\pL\d]+~u', '-', $text);
        $text = trim($text, '-');
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = strtolower($text);
        $text = preg_replace('~[^-\w]+~', '', $text);

        if (empty($text))  { return 'n-a'; }

        return $text;
    }


}
