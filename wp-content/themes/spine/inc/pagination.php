<?php
/**
 * Spine 2 WordPress theme
 *
 * Custom pagination with Foundation 4 markup
 *
 * @package    spine2
 * @subpackage pagination
 * @version    0.1
 * @author     paul <spine@paulwp.com>
 * @copyright  Copyright (c) 2013, Paul de Wouters
 * @link       http://paulwp.com
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

/**
 * Echoes post navigation in page numbers format (similar to WP-PageNavi).
 *
 * The links, if needed, are ordered as:
 *   previous page arrow,
 *   first page,
 *   up to two pages before current page,
 *   current page,
 *   up to two pages after the current page,
 *   last page,
 *   next page arrow.
 *
 * @since 0.2.3
 *
 * @uses g_ent() Pass entities through filter
 *
 * @global WP_Query $wp_query Query object
 * @return null Returns early if on a single post or page, or only 1 page present
 */

function spine2_numeric_posts_nav() {

	if( is_singular() )
		return;

	global $wp_query;

	/** Stop execution if there's only 1 page */
	if( $wp_query->max_num_pages <= 1 )
		return;

	$paged = get_query_var( 'paged' ) ? absint( get_query_var( 'paged' ) ) : 1;
	$max   = intval( $wp_query->max_num_pages );

	/**	Add current page to the array */
	if ( $paged >= 1 )
		$links[] = $paged;

	/**	Add the pages around the current page to the array */
	if ( $paged >= 3 ) {
		$links[] = $paged - 1;
		$links[] = $paged - 2;
	}

	if ( ( $paged + 2 ) <= $max ) {
		$links[] = $paged + 2;
		$links[] = $paged + 1;
	}

	echo '<ul class="pagination">' . "\n";

	/**	Previous Post Link */
	if ( get_previous_posts_link() )
		printf( '<li>%s</li>' . "\n", get_previous_posts_link(  '&laquo; '  . __( 'Previous Page', 'spine' ) ) );

	/**	Link to first page, plus ellipses if necessary */
	if ( ! in_array( 1, $links ) ) {
		$class = 1 == $paged ? ' class="current"' : '';

		printf( '<li%s><a href="%s">%s</a></li>' . "\n", $class, esc_url( get_pagenum_link( 1 ) ), '1' );

		if ( ! in_array( 2, $links ) )
			echo '<li>&hellip;</li>';
	}

	/**	Link to current page, plus 2 pages in either direction if necessary */
	sort( $links );
	foreach ( (array) $links as $link ) {
		$class = $paged == $link ? ' class="current"' : '';
		printf( '<li%s><a href="%s">%s</a></li>' . "\n", $class, esc_url( get_pagenum_link( $link ) ), $link );
	}

	/**	Link to last page, plus ellipses if necessary */
	if ( ! in_array( $max, $links ) ) {
		if ( ! in_array( $max - 1, $links ) )
			echo  '<li>&hellip;</li>' . "\n";

		$class = $paged == $max ? ' class="current"' : '';
		printf( '<li%s><a href="%s">%s</a></li>' . "\n", $class, esc_url( get_pagenum_link( $max ) ), $max );
	}

	/**	Next Post Link */
	if ( get_next_posts_link() )
		printf( '<li>%s</li>' . "\n", get_next_posts_link(  __( 'Next Page', 'spine' ) . ' &raquo;'  ) );

	echo '</ul>' . "\n";

}