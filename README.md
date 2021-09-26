# WP_Image_Editor_GD
WordPress Image Editor Class for Image Manipulation through GD (clean standalone implementation)   


### Some important functionalities have been commented out and / or changed:  
   
function __( $text, $domain = 'default' ) {   
   
    // [ Changed to facilitate extrapolaation of the code ]   
   
    //return translate( $text, $domain );   
    
    return $text;   
}   
   
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
