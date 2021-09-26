<?php

/**
 * 
 * WordPress - Web publishing software
 * 
 * Copyright 2011-2021 by the contributors
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * 
 * This program incorporates work covered by the following copyright and
 * permission notices:
 * 
 *   b2 is (c) 2001, 2002 Michel Valdrighi - https://cafelog.com
 * 
 *   Wherever third party code has been used, credit has been given in the code's
 *   comments.
 * 
 *   b2 is released under the GPL
 * 
 * and
 *   
 *   (c) 2021, 2024 5 Mode - https://5mode.com 
 *
 *   The code has been adapted and changed to standalone version by 5 Mode
 *
 * and
 * 
 *   WordPress - Web publishing software
 * 
 *   Copyright 2003-2021 by the contributors
 * 
 *   WordPress is released under the GPL
 * 
 * =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
 * 
 * Check if two numbers are nearly the same.
 *
 * This is similar to using `round()` but the precision is more fine-grained.
 *
 * @since 5.3.0
 *
 * @param int|float $expected  The expected value.
 * @param int|float $actual    The actual number.
 * @param int|float $precision The allowed variation.
 * @return bool Whether the numbers match whithin the specified precision.
 */
function wp_fuzzy_number_match( $expected, $actual, $precision = 1 ) {
    return abs( (float) $expected - (float) $actual ) <= $precision;
}

/**
 * Returns the real mime type of an image file.
 *
 * This depends on exif_imagetype() or getimagesize() to determine real mime types.
 *
 * @since 4.7.1
 * @since 5.8.0 Added support for WebP images.
 *
 * @param string $file Full path to the file.
 * @return string|false The actual mime type or false if the type cannot be determined.
 */
function wp_get_image_mime( $file ) {
    /*
     * Use exif_imagetype() to check the mimetype if available or fall back to
     * getimagesize() if exif isn't avaialbe. If either function throws an Exception
     * we assume the file could not be validated.
     */
    try {
        if ( is_callable( 'exif_imagetype' ) ) {
            $imagetype = exif_imagetype( $file );
            $mime      = ( $imagetype ) ? image_type_to_mime_type( $imagetype ) : false;
        } elseif ( function_exists( 'getimagesize' ) ) {
            // Don't silence errors when in debug mode, unless running unit tests.
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG
                && ! defined( 'WP_RUN_CORE_TESTS' )
            ) {
                // Not using wp_getimagesize() here to avoid an infinite loop.
                $imagesize = getimagesize( $file );
            } else {
                // phpcs:ignore WordPress.PHP.NoSilencedErrors
                $imagesize = @getimagesize( $file );
            }
 
            $mime = ( isset( $imagesize['mime'] ) ) ? $imagesize['mime'] : false;
        } else {
            $mime = false;
        }
 
        if ( false !== $mime ) {
            return $mime;
        }
 
        $handle = fopen( $file, 'rb' );
        if ( false === $handle ) {
            return false;
        }
 
        $magic = fread( $handle, 12 );
        if ( false === $magic ) {
            return false;
        }
 
        /*
         * Add WebP fallback detection when image library doesn't support WebP.
         * Note: detection values come from LibWebP, see
         * https://github.com/webmproject/libwebp/blob/master/imageio/image_dec.c#L30
         */
        $magic = bin2hex( $magic );
        if (
            // RIFF.
            ( 0 === strpos( $magic, '52494646' ) ) &&
            // WEBP.
            ( 16 === strpos( $magic, '57454250' ) )
        ) {
            $mime = 'image/webp';
        }
 
        fclose( $handle );
    } catch ( Exception $e ) {
        $mime = false;
    }
 
    return $mime;
}


/**
 * Retrieve list of mime types and file extensions.
 *
 * @since 3.5.0
 * @since 4.2.0 Support was added for GIMP (.xcf) files.
 * @since 4.9.2 Support was added for Flac (.flac) files.
 * @since 4.9.6 Support was added for AAC (.aac) files.
 *
 * @return string[] Array of mime types keyed by the file extension regex corresponding to those types.
 */
function wp_get_mime_types() {
    /**
     * Filters the list of mime types and file extensions.
     *
     * This filter should be used to add, not remove, mime types. To remove
     * mime types, use the {@see 'upload_mimes'} filter.
     *
     * @since 3.5.0
     *
     * @param string[] $wp_get_mime_types Mime types keyed by the file extension regex
     *                                 corresponding to those types.
     */
    return apply_filters(
        'mime_types',
        array(
            // Image formats.
            'jpg|jpeg|jpe'                 => 'image/jpeg',
            'gif'                          => 'image/gif',
            'png'                          => 'image/png',
            'bmp'                          => 'image/bmp',
            'tiff|tif'                     => 'image/tiff',
            'webp'                         => 'image/webp',
            'ico'                          => 'image/x-icon',
            'heic'                         => 'image/heic',
            // Video formats.
            'asf|asx'                      => 'video/x-ms-asf',
            'wmv'                          => 'video/x-ms-wmv',
            'wmx'                          => 'video/x-ms-wmx',
            'wm'                           => 'video/x-ms-wm',
            'avi'                          => 'video/avi',
            'divx'                         => 'video/divx',
            'flv'                          => 'video/x-flv',
            'mov|qt'                       => 'video/quicktime',
            'mpeg|mpg|mpe'                 => 'video/mpeg',
            'mp4|m4v'                      => 'video/mp4',
            'ogv'                          => 'video/ogg',
            'webm'                         => 'video/webm',
            'mkv'                          => 'video/x-matroska',
            '3gp|3gpp'                     => 'video/3gpp',  // Can also be audio.
            '3g2|3gp2'                     => 'video/3gpp2', // Can also be audio.
            // Text formats.
            'txt|asc|c|cc|h|srt'           => 'text/plain',
            'csv'                          => 'text/csv',
            'tsv'                          => 'text/tab-separated-values',
            'ics'                          => 'text/calendar',
            'rtx'                          => 'text/richtext',
            'css'                          => 'text/css',
            'htm|html'                     => 'text/html',
            'vtt'                          => 'text/vtt',
            'dfxp'                         => 'application/ttaf+xml',
            // Audio formats.
            'mp3|m4a|m4b'                  => 'audio/mpeg',
            'aac'                          => 'audio/aac',
            'ra|ram'                       => 'audio/x-realaudio',
            'wav'                          => 'audio/wav',
            'ogg|oga'                      => 'audio/ogg',
            'flac'                         => 'audio/flac',
            'mid|midi'                     => 'audio/midi',
            'wma'                          => 'audio/x-ms-wma',
            'wax'                          => 'audio/x-ms-wax',
            'mka'                          => 'audio/x-matroska',
            // Misc application formats.
            'rtf'                          => 'application/rtf',
            'js'                           => 'application/javascript',
            'pdf'                          => 'application/pdf',
            'swf'                          => 'application/x-shockwave-flash',
            'class'                        => 'application/java',
            'tar'                          => 'application/x-tar',
            'zip'                          => 'application/zip',
            'gz|gzip'                      => 'application/x-gzip',
            'rar'                          => 'application/rar',
            '7z'                           => 'application/x-7z-compressed',
            'exe'                          => 'application/x-msdownload',
            'psd'                          => 'application/octet-stream',
            'xcf'                          => 'application/octet-stream',
            // MS Office formats.
            'doc'                          => 'application/msword',
            'pot|pps|ppt'                  => 'application/vnd.ms-powerpoint',
            'wri'                          => 'application/vnd.ms-write',
            'xla|xls|xlt|xlw'              => 'application/vnd.ms-excel',
            'mdb'                          => 'application/vnd.ms-access',
            'mpp'                          => 'application/vnd.ms-project',
            'docx'                         => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'docm'                         => 'application/vnd.ms-word.document.macroEnabled.12',
            'dotx'                         => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
            'dotm'                         => 'application/vnd.ms-word.template.macroEnabled.12',
            'xlsx'                         => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xlsm'                         => 'application/vnd.ms-excel.sheet.macroEnabled.12',
            'xlsb'                         => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
            'xltx'                         => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
            'xltm'                         => 'application/vnd.ms-excel.template.macroEnabled.12',
            'xlam'                         => 'application/vnd.ms-excel.addin.macroEnabled.12',
            'pptx'                         => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'pptm'                         => 'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
            'ppsx'                         => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
            'ppsm'                         => 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12',
            'potx'                         => 'application/vnd.openxmlformats-officedocument.presentationml.template',
            'potm'                         => 'application/vnd.ms-powerpoint.template.macroEnabled.12',
            'ppam'                         => 'application/vnd.ms-powerpoint.addin.macroEnabled.12',
            'sldx'                         => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
            'sldm'                         => 'application/vnd.ms-powerpoint.slide.macroEnabled.12',
            'onetoc|onetoc2|onetmp|onepkg' => 'application/onenote',
            'oxps'                         => 'application/oxps',
            'xps'                          => 'application/vnd.ms-xpsdocument',
            // OpenOffice formats.
            'odt'                          => 'application/vnd.oasis.opendocument.text',
            'odp'                          => 'application/vnd.oasis.opendocument.presentation',
            'ods'                          => 'application/vnd.oasis.opendocument.spreadsheet',
            'odg'                          => 'application/vnd.oasis.opendocument.graphics',
            'odc'                          => 'application/vnd.oasis.opendocument.chart',
            'odb'                          => 'application/vnd.oasis.opendocument.database',
            'odf'                          => 'application/vnd.oasis.opendocument.formula',
            // WordPerfect formats.
            'wp|wpd'                       => 'application/wordperfect',
            // iWork formats.
            'key'                          => 'application/vnd.apple.keynote',
            'numbers'                      => 'application/vnd.apple.numbers',
            'pages'                        => 'application/vnd.apple.pages',
        )
    );
}

/**
 * Test if a given path is a stream URL
 *
 * @since 3.5.0
 *
 * @param string $path The resource path or URL.
 * @return bool True if the path is a stream URL.
 */
function wp_is_stream( $path ) {
      $scheme_separator = strpos( $path, '://' );
	
      if ( false === $scheme_separator ) {
            // $path isn't a stream.
            return false;
      }
	
      $stream = substr( $path, 0, $scheme_separator );
	
      return in_array( $stream, stream_get_wrappers(), true );
}


/**
  * Recursive directory creation based on full path.
  *
  * Will attempt to set permissions on folders.
  *
  * @since 2.0.1
  *
  * @param string $target Full path to attempt to create.
  * @return bool Whether the path was created. True if path already exists.
  */
function wp_mkdir_p( $target ) {
    $wrapper = null;
 
    // Strip the protocol.
    if ( wp_is_stream( $target ) ) {
        list( $wrapper, $target ) = explode( '://', $target, 2 );
    }
 
    // From php.net/mkdir user contributed notes.
    $target = str_replace( '//', '/', $target );
 
    // Put the wrapper back on the target.
    if ( null !== $wrapper ) {
        $target = $wrapper . '://' . $target;
    }
 
    /*
     * Safe mode fails with a trailing slash under certain PHP versions.
     * Use rtrim() instead of untrailingslashit to avoid formatting.php dependency.
     */
    $target = rtrim( $target, '/' );
    if ( empty( $target ) ) {
        $target = '/';
    }
 
    if ( file_exists( $target ) ) {
        return @is_dir( $target );
    }
 
    // Do not allow path traversals.
    if ( false !== strpos( $target, '../' ) || false !== strpos( $target, '..' . DIRECTORY_SEPARATOR ) ) {
        return false;
    }
 
    // We need to find the permissions of the parent folder that exists and inherit that.
    $target_parent = dirname( $target );
    while ( '.' !== $target_parent && ! is_dir( $target_parent ) && dirname( $target_parent ) !== $target_parent ) {
        $target_parent = dirname( $target_parent );
    }
 
    // Get the permission bits.
    $stat = @stat( $target_parent );
    if ( $stat ) {
        $dir_perms = $stat['mode'] & 0007777;
    } else {
        $dir_perms = 0777;
    }
 
    if ( @mkdir( $target, $dir_perms, true ) ) {
 
        /*
         * If a umask is set that modifies $dir_perms, we'll have to re-set
         * the $dir_perms correctly with chmod()
         */
        if ( ( $dir_perms & ~umask() ) != $dir_perms ) {
            $folder_parts = explode( '/', substr( $target, strlen( $target_parent ) + 1 ) );
            for ( $i = 1, $c = count( $folder_parts ); $i <= $c; $i++ ) {
                chmod( $target_parent . '/' . implode( '/', array_slice( $folder_parts, 0, $i ) ), $dir_perms );
            }
        }
 
        return true;
    }
 
    return false;
}
