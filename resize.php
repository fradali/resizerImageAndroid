<?php

$res_dir = 'res/';

$src_dir = 'src/';

$resolutions = array(
    'ldpi' => array('width' => 240, 'height' => 320),
    'mdpi' => array('width' => 320, 'height' => 480),
    'hdpi' => array('width' => 480, 'height' => 800),
    'xhdpi' => array('width' => 720, 'height' => 1280),
    'xxhdpi' => array('width' => 960, 'height' => 1600),
    'xxxhdpi' => array('width' => 1280, 'height' => 1920),
);

foreach ($resolutions as $key => $resolution) {
    $dst_dir = $res_dir.'drawable-'.$key.'/';
    if (!is_dir($dst_dir)) {
        mkdir($dst_dir);
    }
    if ($handle = opendir($src_dir)) {
        while (false !== ($entry = readdir($handle))) {
            if ($entry != '.' && $entry != '..') {
                $new_name   = $entry;
                $new_width  = $resolution['width'];
                $new_height = $resolution['height'];
		if (strpos($entry, '%') !== false) {
			list($rate, $new_name) = explode('%', $entry);
                        $new_width  = $resolution['width']*$rate/100;
                        $new_height = $resolution['height']*$rate/100;
		}
                createThumbnail($entry, $new_width, $new_height, $src_dir, $dst_dir, $new_name);
            }
        }
    }
}

function createThumbnail($image_name, $new_width, $new_height, $src_dir, $dst_dir, $new_name) {

    $path = $src_dir . '/' . $image_name;

    $mime = getimagesize($path);

    if ($mime['mime'] == 'image/png') { 
        $src_img = ImageCreateFromPng($path);
    }

    if ($mime['mime'] == 'image/jpg' || $mime['mime'] == 'image/jpeg' || $mime['mime'] == 'image/pjpeg') {
        $src_img = ImageCreateFromJpeg($path);
    }

    $width_orig       =   imageSX($src_img);
    $height_orig      =   imageSY($src_img);

    $ratio_orig = $width_orig/$height_orig;

    if ($width_orig > $height_orig) {
        $width_new    =   $new_width;
        $height_new   =   $height_orig*($new_height/$width_orig);
    }

    if ($width_orig < $height_orig) {
        $width_new    =   $width_orig*($new_width/$height_orig);
        $height_new   =   $new_height;
    }

    if ($width_orig == $height_orig) {
        $width_new    =   $new_width;
        $height_new   =   $new_height;
    }  

    if ($width_new/$height_new > $ratio_orig) {
        $width_new   =    $height_new*$ratio_orig;
    } else {
        $height_new  =    $width_new/$ratio_orig;
    }

    $dst_img         =   ImageCreateTrueColor($width_new, $height_new);

    if ($mime['mime'] == 'image/png') {
        imagealphablending($dst_img, false);
        imagesavealpha($dst_img, true);
        $transparent = imagecolorallocatealpha($dst_img, 255, 255, 255, 127);
        imagefilledrectangle($dst_img, 0, 0, $width_new, $height_new, $transparent);
    }

    imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $width_new, $height_new, $width_orig, $height_orig); 

    // New save location
    $new_thumb_loc = $dst_dir . $new_name;

    if ($mime['mime'] == 'image/png') {
        $result = imagepng($dst_img, $new_thumb_loc, 8);
    }

    if ($mime['mime'] == 'image/jpg' || $mime['mime'] == 'image/jpeg' || $mime['mime'] == 'image/pjpeg') {
        $result = imagejpeg($dst_img, $new_thumb_loc, 80);
    }

    imagedestroy($dst_img); 
    imagedestroy($src_img);

    return $result;
}
