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
	 * Appends a trailing slash.
	 *
	 * Will remove trailing forward and backslashes if it exists already before adding
	 * a trailing forward slash. This prevents double slashing a string or path.
	 *
	 * The primary use of this is for paths and thus should be used for paths. It is
	 * not restricted to paths and offers no specific path support.
	 *
	 * @since 1.2.0
	 *
	 * @param string $string What to add the trailing slash to.
	 * @return string String with trailing slash added.
	 */
	function trailingslashit( $string ) {
	        return untrailingslashit( $string ) . '/';
	}
	
	/**
	 * Removes trailing forward slashes and backslashes if they exist.
	 *
	 * The primary use of this is for paths and thus should be used for paths. It is
	 * not restricted to paths and offers no specific path support.
	 *
	 * @since 2.2.0
	 *
	 * @param string $string What to remove the trailing slashes from.
	 * @return string String without the trailing slashes.
	 */
	function untrailingslashit( $string ) {
	        return rtrim( $string, '/\\' );
	}


 /**
  * i18n friendly version of basename()
  *
  * @since 3.1.0
  *
  * @param string $path   A path.
  * @param string $suffix If the filename ends in suffix this will also be cut off.
  * @return string
  */
  function wp_basename( $path, $suffix = '' ) {
          return urldecode( basename( str_replace( array( '%2F', '%5C' ), '/', urlencode( $path ) ), $suffix ) );
  }
