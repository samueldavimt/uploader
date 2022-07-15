<?php

use Uploader\Uploader\Image;

require __DIR__ . "/../vendor/autoload.php";

// Image Upload

$image = new Image("../storage", "images");

if(isset($_FILES['file'])){

    try {
        $u = $image->upload($_FILES['file'], $_POST['name'], 900, 90);
        echo $u;
    } catch (Exception $e) {
        echo $e->getMessage();
    }
    echo "<br><br>";
}


require __DIR__ . "/form.php";