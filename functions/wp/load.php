
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
 * Checks whether the given variable is a WordPress Error.
 *
 * Returns whether `$thing` is an instance of the `WP_Error` class.
 *
 * @since 2.1.0
 *
 * @param mixed $thing The variable to check.
 * @return bool Whether the variable is an instance of WP_Error.
 */
function is_wp_error( $thing ) {
    $is_wp_error = ( $thing instanceof WP_Error );
 
    if ( $is_wp_error ) {
        /**
         * Fires when `is_wp_error()` is called and its parameter is an instance of `WP_Error`.
         *
         * @since 5.6.0
         *
         * @param WP_Error $thing The error object passed to `is_wp_error()`.
         */
        do_action( 'is_wp_error_instance', $thing );
    }
 
    return $is_wp_error;
}
