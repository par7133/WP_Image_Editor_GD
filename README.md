# WP_Image_Editor_GD
WordPress Image Editor Class for Image Manipulation through GD (clean standalone implementation)   


### Some important functionalities have been commented out and / or changed:  

### // l10n.php
function __( $text, $domain = 'default' ) {   
   
    // [ Changed to facilitate extrapolaation of the code ]   
   
    //return translate( $text, $domain );   
    
    return $text;   
}   
   
### // class-wp-image-editor-gd.php   
public function load() {   

    if ( $this->image ) {   
        return true;   
    }   
   
    if ( ! is_file( $this->file ) && ! preg_match( '|^https?://|', $this->file ) ) {   
        return new WP_Error( 'error_loading_image', __( 'File doesn&#8217;t exist?' ), $this->file );   
    }   
    
    // Set artificially high because GD uses uncompressed images in memory.   
		
    // [ Commented to facilitate extrapolaation of the code ]   
    
    //wp_raise_memory_limit( 'image' );   

    [...]


WordPress - Web publishing software   
   
Copyright 2001-2002 Michel Valdrighi - https://cafelog.com   
Copyright 2011-2021 WordPress, and contributors   
Copyright 2021 5 Mode    
   
This program is free software; you can redistribute it and/or modify   
it under the terms of the GNU General Public License as published by   
the Free Software Foundation; either version 2 of the License, or   
(at your option) any later version.   
