
<?php
  
require "init.inc";  
  
$img_path = APP_PATH . '/file.jpg';
  
$gd_image_editor = new WP_Image_Editor_GD($img_path); 
$gd_image_editor->load(); 
//$gd_image_editor->resize(300,450,false); 
$gd_image_editor->rotate(90);
$gd_image_editor->save($img_path);
echo("<br>&nbsp;&nbsp;Image rotated!");
?>
