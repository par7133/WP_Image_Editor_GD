
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
 * Retrieves calculated resize dimensions for use in WP_Image_Editor.
 *
 * Calculates dimensions and coordinates for a resized image that fits
 * within a specified width and height.
 *
 * Cropping behavior is dependent on the value of $crop:
 * 1. If false (default), images will not be cropped.
 * 2. If an array in the form of array( x_crop_position, y_crop_position ):
 *    - x_crop_position accepts 'left' 'center', or 'right'.
 *    - y_crop_position accepts 'top', 'center', or 'bottom'.
 *    Images will be cropped to the specified dimensions within the defined crop area.
 * 3. If true, images will be cropped to the specified dimensions using center positions.
 *
 * @since 2.5.0
 *
 * @param int        $orig_w Original width in pixels.
 * @param int        $orig_h Original height in pixels.
 * @param int        $dest_w New width in pixels.
 * @param int        $dest_h New height in pixels.
 * @param bool|array $crop   Optional. Whether to crop image to specified width and height or resize.
 *                           An array can specify positioning of the crop area. Default false.
 * @return array|false Returned array matches parameters for `imagecopyresampled()`. False on failure.
 */
function image_resize_dimensions( $orig_w, $orig_h, $dest_w, $dest_h, $crop = false ) {
 
    if ( $orig_w <= 0 || $orig_h <= 0 ) {
        return false;
    }
    // At least one of $dest_w or $dest_h must be specific.
    if ( $dest_w <= 0 && $dest_h <= 0 ) {
        return false;
    }
 
    /**
     * Filters whether to preempt calculating the image resize dimensions.
     *
     * Returning a non-null value from the filter will effectively short-circuit
     * image_resize_dimensions(), returning that value instead.
     *
     * @since 3.4.0
     *
     * @param null|mixed $null   Whether to preempt output of the resize dimensions.
     * @param int        $orig_w Original width in pixels.
     * @param int        $orig_h Original height in pixels.
     * @param int        $dest_w New width in pixels.
     * @param int        $dest_h New height in pixels.
     * @param bool|array $crop   Whether to crop image to specified width and height or resize.
     *                           An array can specify positioning of the crop area. Default false.
     */
    $output = apply_filters( 'image_resize_dimensions', null, $orig_w, $orig_h, $dest_w, $dest_h, $crop );
 
    if ( null !== $output ) {
        return $output;
    }
 
    // Stop if the destination size is larger than the original image dimensions.
    if ( empty( $dest_h ) ) {
        if ( $orig_w < $dest_w ) {
            return false;
        }
    } elseif ( empty( $dest_w ) ) {
        if ( $orig_h < $dest_h ) {
            return false;
        }
    } else {
        if ( $orig_w < $dest_w && $orig_h < $dest_h ) {
            return false;
        }
    }
 
    if ( $crop ) {
        /*
         * Crop the largest possible portion of the original image that we can size to $dest_w x $dest_h.
         * Note that the requested crop dimensions are used as a maximum bounding box for the original image.
         * If the original image's width or height is less than the requested width or height
         * only the greater one will be cropped.
         * For example when the original image is 600x300, and the requested crop dimensions are 400x400,
         * the resulting image will be 400x300.
         */
        $aspect_ratio = $orig_w / $orig_h;
        $new_w        = min( $dest_w, $orig_w );
        $new_h        = min( $dest_h, $orig_h );
 
        if ( ! $new_w ) {
            $new_w = (int) round( $new_h * $aspect_ratio );
        }
 
        if ( ! $new_h ) {
            $new_h = (int) round( $new_w / $aspect_ratio );
        }
 
        $size_ratio = max( $new_w / $orig_w, $new_h / $orig_h );
 
        $crop_w = round( $new_w / $size_ratio );
        $crop_h = round( $new_h / $size_ratio );
 
        if ( ! is_array( $crop ) || count( $crop ) !== 2 ) {
            $crop = array( 'center', 'center' );
        }
 
        list( $x, $y ) = $crop;
 
        if ( 'left' === $x ) {
            $s_x = 0;
        } elseif ( 'right' === $x ) {
            $s_x = $orig_w - $crop_w;
        } else {
            $s_x = floor( ( $orig_w - $crop_w ) / 2 );
        }
 
        if ( 'top' === $y ) {
            $s_y = 0;
        } elseif ( 'bottom' === $y ) {
            $s_y = $orig_h - $crop_h;
        } else {
            $s_y = floor( ( $orig_h - $crop_h ) / 2 );
        }
    } else {
        // Resize using $dest_w x $dest_h as a maximum bounding box.
        $crop_w = $orig_w;
        $crop_h = $orig_h;
 
        $s_x = 0;
        $s_y = 0;
 
        list( $new_w, $new_h ) = wp_constrain_dimensions( $orig_w, $orig_h, $dest_w, $dest_h );
    }
 
    if ( wp_fuzzy_number_match( $new_w, $orig_w ) && wp_fuzzy_number_match( $new_h, $orig_h ) ) {
        // The new size has virtually the same dimensions as the original image.
 
        /**
         * Filters whether to proceed with making an image sub-size with identical dimensions
         * with the original/source image. Differences of 1px may be due to rounding and are ignored.
         *
         * @since 5.3.0
         *
         * @param bool $proceed The filtered value.
         * @param int  $orig_w  Original image width.
         * @param int  $orig_h  Original image height.
         */
        $proceed = (bool) apply_filters( 'wp_image_resize_identical_dimensions', false, $orig_w, $orig_h );
 
        if ( ! $proceed ) {
            return false;
        }
    }
 
    // The return array matches the parameters to imagecopyresampled().
    // int dst_x, int dst_y, int src_x, int src_y, int dst_w, int dst_h, int src_w, int src_h
    return array( 0, 0, (int) $s_x, (int) $s_y, (int) $new_w, (int) $new_h, (int) $crop_w, (int) $crop_h );
}


/**
 * Determines whether the value is an acceptable type for GD image functions.
 *
 * In PHP 8.0, the GD extension uses GdImage objects for its data structures.
 * This function checks if the passed value is either a resource of type `gd`
 * or a GdImage object instance. Any other type will return false.
 *
 * @since 5.6.0
 *
 * @param resource|GdImage|false $image A value to check the type for.
 * @return bool True if $image is either a GD image resource or GdImage instance,
 *              false otherwise.
 */
function is_gd_image( $image ) {
    if ( is_resource( $image ) && 'gd' === get_resource_type( $image )
        || is_object( $image ) && $image instanceof GdImage
    ) {
        return true;
    }
 
    return false;
}


/**
 * Calculates the new dimensions for a down-sampled image.
 *
 * If either width or height are empty, no constraint is applied on
 * that dimension.
 *
 * @since 2.5.0
 *
 * @param int $current_width  Current width of the image.
 * @param int $current_height Current height of the image.
 * @param int $max_width      Optional. Max width in pixels to constrain to. Default 0.
 * @param int $max_height     Optional. Max height in pixels to constrain to. Default 0.
 * @return int[] {
 *     An array of width and height values.
 *
 *     @type int $0 The width in pixels.
 *     @type int $1 The height in pixels.
 * }
 */
function wp_constrain_dimensions( $current_width, $current_height, $max_width = 0, $max_height = 0 ) {
    if ( ! $max_width && ! $max_height ) {
        return array( $current_width, $current_height );
    }
 
    $width_ratio  = 1.0;
    $height_ratio = 1.0;
    $did_width    = false;
    $did_height   = false;
 
    if ( $max_width > 0 && $current_width > 0 && $current_width > $max_width ) {
        $width_ratio = $max_width / $current_width;
        $did_width   = true;
    }
 
    if ( $max_height > 0 && $current_height > 0 && $current_height > $max_height ) {
        $height_ratio = $max_height / $current_height;
        $did_height   = true;
    }
 
    // Calculate the larger/smaller ratios.
    $smaller_ratio = min( $width_ratio, $height_ratio );
    $larger_ratio  = max( $width_ratio, $height_ratio );
 
    if ( (int) round( $current_width * $larger_ratio ) > $max_width || (int) round( $current_height * $larger_ratio ) > $max_height ) {
        // The larger ratio is too big. It would result in an overflow.
        $ratio = $smaller_ratio;
    } else {
        // The larger ratio fits, and is likely to be a more "snug" fit.
        $ratio = $larger_ratio;
    }
 
    // Very small dimensions may result in 0, 1 should be the minimum.
    $w = max( 1, (int) round( $current_width * $ratio ) );
    $h = max( 1, (int) round( $current_height * $ratio ) );
 
    /*
     * Sometimes, due to rounding, we'll end up with a result like this:
     * 465x700 in a 177x177 box is 117x176... a pixel short.
     * We also have issues with recursive calls resulting in an ever-changing result.
     * Constraining to the result of a constraint should yield the original result.
     * Thus we look for dimensions that are one pixel shy of the max value and bump them up.
     */
 
    // Note: $did_width means it is possible $smaller_ratio == $width_ratio.
    if ( $did_width && $w === $max_width - 1 ) {
        $w = $max_width; // Round it up.
    }
 
    // Note: $did_height means it is possible $smaller_ratio == $height_ratio.
    if ( $did_height && $h === $max_height - 1 ) {
        $h = $max_height; // Round it up.
    }
 
    /**
     * Filters dimensions to constrain down-sampled images to.
     *
     * @since 4.1.0
     *
     * @param int[] $dimensions     {
     *     An array of width and height values.
     *
     *     @type int $0 The width in pixels.
     *     @type int $1 The height in pixels.
     * }
     * @param int   $current_width  The current width of the image.
     * @param int   $current_height The current height of the image.
     * @param int   $max_width      The maximum width permitted.
     * @param int   $max_height     The maximum height permitted.
     */
    return apply_filters( 'wp_constrain_dimensions', array( $w, $h ), $current_width, $current_height, $max_width, $max_height );
}

/**
 * Allows PHP's getimagesize() to be debuggable when necessary.
 *
 * @since 5.7.0
 * @since 5.8.0 Added support for WebP images.
 *
 * @param string $filename   The file path.
 * @param array  $image_info Optional. Extended image information (passed by reference).
 * @return array|false Array of image information or false on failure.
 */
function wp_getimagesize( $filename, array &$image_info = null ) {
    // Don't silence errors when in debug mode, unless running unit tests.
    if ( defined( 'WP_DEBUG' ) && WP_DEBUG
        && ! defined( 'WP_RUN_CORE_TESTS' )
    ) {
        if ( 2 === func_num_args() ) {
            $info = getimagesize( $filename, $image_info );
        } else {
            $info = getimagesize( $filename );
        }
    } else {
        /*
         * Silencing notice and warning is intentional.
         *
         * getimagesize() has a tendency to generate errors, such as
         * "corrupt JPEG data: 7191 extraneous bytes before marker",
         * even when it's able to provide image size information.
         *
         * See https://core.trac.wordpress.org/ticket/42480
         */
        if ( 2 === func_num_args() ) {
            // phpcs:ignore WordPress.PHP.NoSilencedErrors
            $info = @getimagesize( $filename, $image_info );
        } else {
            // phpcs:ignore WordPress.PHP.NoSilencedErrors
            $info = @getimagesize( $filename );
        }
    }
 
    if ( false !== $info ) {
        return $info;
    }
 
    // For PHP versions that don't support WebP images,
    // extract the image size info from the file headers.
    if ( 'image/webp' === wp_get_image_mime( $filename ) ) {
        $webp_info = wp_get_webp_info( $filename );
        $width     = $webp_info['width'];
        $height    = $webp_info['height'];
 
        // Mimic the native return format.
        if ( $width && $height ) {
            return array(
                $width,
                $height,
                IMAGETYPE_WEBP, // phpcs:ignore PHPCompatibility.Constants.NewConstants.imagetype_webpFound
                sprintf(
                    'width="%d" height="%d"',
                    $width,
                    $height
                ),
                'mime' => 'image/webp',
            );
        }
    }
 
    // The image could not be parsed.
    return false;
}


/**
 * Create new GD image resource with transparency support
 *
 * @todo Deprecate if possible.
 *
 * @since 2.9.0
 *
 * @param int $width  Image width in pixels.
 * @param int $height Image height in pixels.
 * @return resource|GdImage|false The GD image resource or GdImage instance on success.
 *                                False on failure.
 */
function wp_imagecreatetruecolor( $width, $height ) {
    $img = imagecreatetruecolor( $width, $height );
 
    if ( is_gd_image( $img )
        && function_exists( 'imagealphablending' ) && function_exists( 'imagesavealpha' )
    ) {
        imagealphablending( $img, false );
        imagesavealpha( $img, true );
    }
 
    return $img;
}
