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
 */ 
 
	/** @var WP_Hook[] $wp_filter */
	global $wp_filter;
	
	/** @var string[] $wp_current_filter */
	global $wp_current_filter;
	
	if ( ! $wp_filter ) {
	    $wp_filter = array();
	}

	if ( ! isset( $wp_current_filter ) ) {
	    $wp_current_filter = array();
	}


/**
  * Calls the 'all' hook, which will process the functions hooked into it.
  *
  * The 'all' hook passes all of the arguments or parameters that were used for
  * the hook, which this function was called for.
  *
  * This function is used internally for apply_filters(), do_action(), and
  * do_action_ref_array() and is not meant to be used from outside those
  * functions. This function does not check for the existence of the all hook, so
  * it will fail unless the all hook exists prior to this function call.
  *
  * @since 2.5.0
  * @access private
  *
  * @global WP_Hook[] $wp_filter Stores all of the filters and actions.
  *
  * @param array $args The collected parameters from the hook that was called.
  */
  function _wp_call_all_hook( $args ) {
      global $wp_filter;
 
      $wp_filter['all']->do_all_hook( $args );
  }

 	/**
	 * Calls the callback functions that have been added to a filter hook.
	 *
	 * This function invokes all functions attached to filter hook `$hook_name`.
	 * It is possible to create new filter hooks by simply calling this function,
	 * specifying the name of the new hook using the `$hook_name` parameter.
	 *
	 * The function also allows for multiple additional arguments to be passed to hooks.
	 *
	 * Example usage:
	 *
	 *     // The filter callback function.
	 *     function example_callback( $string, $arg1, $arg2 ) {
	 *         // (maybe) modify $string.
	 *         return $string;
	 *     }
	 *     add_filter( 'example_filter', 'example_callback', 10, 3 );
	 *
	 *     /*
	 *      * Apply the filters by calling the 'example_callback()' function
	 *      * that's hooked onto `example_filter` above.
	 *      *
	 *      * - 'example_filter' is the filter hook.
	 *      * - 'filter me' is the value being filtered.
	 *      * - $arg1 and $arg2 are the additional arguments passed to the callback.
	 *     $value = apply_filters( 'example_filter', 'filter me', $arg1, $arg2 );
	 *
	 * @since 0.71
	 *
	 * @global WP_Hook[] $wp_filter         Stores all of the filters and actions.
	 * @global string[]  $wp_current_filter Stores the list of current filters with the current one last.
	 *
	 * @param string $hook_name The name of the filter hook.
	 * @param mixed  $value     The value to filter.
	 * @param mixed  ...$args   Additional parameters to pass to the callback functions.
	 * @return mixed The filtered value after all hooked functions are applied to it.
	 */
	function apply_filters( $hook_name, $value ) {
	        global $wp_filter, $wp_current_filter;
	
	        $args = func_get_args();
	
	        // Do 'all' actions first.
	        if ( isset( $wp_filter['all'] ) ) {
	                $wp_current_filter[] = $hook_name;
	                _wp_call_all_hook( $args );
	        }
	
	        if ( ! isset( $wp_filter[ $hook_name ] ) ) {
	                if ( isset( $wp_filter['all'] ) ) {
	                        array_pop( $wp_current_filter );
	                }
	
                return $value;
	        }
	
	        if ( ! isset( $wp_filter['all'] ) ) {
	                $wp_current_filter[] = $hook_name;
	        }
	
	        // Don't pass the tag name to WP_Hook.
	        array_shift( $args );
	
	        $filtered = $wp_filter[ $hook_name ]->apply_filters( $value, $args );
	
	        array_pop( $wp_current_filter );
	
	        return $filtered;
	}


/**
  * Calls the callback functions that have been added to an action hook.
  *
  * This function invokes all functions attached to action hook `$hook_name`.
  * It is possible to create new action hooks by simply calling this function,
  * specifying the name of the new hook using the `$hook_name` parameter.
  *
  * You can pass extra arguments to the hooks, much like you can with `apply_filters()`.
  *
  * Example usage:
  *
  *     // The action callback function.
  *     function example_callback( $arg1, $arg2 ) {
  *         // (maybe) do something with the args.
  *     }
  *     add_action( 'example_action', 'example_callback', 10, 2 );
  *
  *     /*
  *      * Trigger the actions by calling the 'example_callback()' function
  *      * that's hooked onto `example_action` above.
  *      *
  *      * - 'example_action' is the action hook.
  *      * - $arg1 and $arg2 are the additional arguments passed to the callback.
  *     $value = do_action( 'example_action', $arg1, $arg2 );
  *
  * @since 1.2.0
  * @since 5.3.0 Formalized the existing and already documented `...$arg` parameter
  *              by adding it to the function signature.
  *
  * @global WP_Hook[] $wp_filter         Stores all of the filters and actions.
  * @global int[]     $wp_actions        Stores the number of times each action was triggered.
  * @global string[]  $wp_current_filter Stores the list of current filters with the current one last.
  *
  * @param string $hook_name The name of the action to be executed.
  * @param mixed  ...$arg    Optional. Additional arguments which are passed on to the
  *                          functions hooked to the action. Default empty.
  */
  function do_action( $hook_name, ...$arg ) {
      global $wp_filter, $wp_actions, $wp_current_filter;
   
      if ( ! isset( $wp_actions[ $hook_name ] ) ) {
          $wp_actions[ $hook_name ] = 1;
      } else {
          ++$wp_actions[ $hook_name ];
      }
   
      // Do 'all' actions first.
      if ( isset( $wp_filter['all'] ) ) {
          $wp_current_filter[] = $hook_name;
          $all_args            = func_get_args(); // phpcs:ignore PHPCompatibility.FunctionUse.ArgumentFunctionsReportCurrentValue.NeedsInspection
          _wp_call_all_hook( $all_args );
      }
   
      if ( ! isset( $wp_filter[ $hook_name ] ) ) {
          if ( isset( $wp_filter['all'] ) ) {
              array_pop( $wp_current_filter );
          }
   
          return;
      }
   
      if ( ! isset( $wp_filter['all'] ) ) {
          $wp_current_filter[] = $hook_name;
      }
   
      if ( empty( $arg ) ) {
          $arg[] = '';
      } elseif ( is_array( $arg[0] ) && 1 === count( $arg[0] ) && isset( $arg[0][0] ) && is_object( $arg[0][0] ) ) {
          // Backward compatibility for PHP4-style passing of `array( &$this )` as action `$arg`.
          $arg[0] = $arg[0][0];
      }
   
      $wp_filter[ $hook_name ]->do_action( $arg );
   
      array_pop( $wp_current_filter );
  }
