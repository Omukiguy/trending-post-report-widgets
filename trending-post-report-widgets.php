<?php
/**
 * Plugin name: 	Trending Post Reports Widgets
 * Description: 	Post Traffic report dashboard Widgets showing the most viewed posts and most commented on post.
 * Plugin URI: 		https://omukiguy.com
 * Version:			0.1.0
 * Plugin Author: 	Laurence Bahiirwa
 * Author URI: 		https://omukiguy.com
 * License:			GPL2 or Later
 * text-domain: 	trending-post-widget
 */

namespace Trending_Post_Widget;

defined( 'ABSPATH' ) or die( 'Unauthorized Access' );

require_once dirname( __FILE__ ) . '/includes/UpdateClient.class.php';

remove_action( 'wp_head', __NAMESPACE__ . '\adjacent_posts_rel_link_wp_head' );

function tpw_save_post_views( $query ) {
	if( $query->is_main_query() ) {
		
		$postID = $query->queried_object->ID;
		$metakey = 'tpw_post_views';
		
		$views = get_post_meta( $postID, $metakey, true );

		$post_count = ( empty( $views ) ? 0 : $views );
		$post_count++;

		update_post_meta( $postID, $metakey, $post_count );

	}
}

add_action( 'loop_start', __NAMESPACE__ . '\tpw_save_post_views', 10, 1 );

function wporg_add_dashboard_widgets() {
	// Add function here
	wp_add_dashboard_widget( 'trending-posts-widget', 'Trending Posts',  __NAMESPACE__ . '\trending_posts_query' );
	wp_add_dashboard_widget( 'trending-posts-comments-widget', 'Most Commented Posts',  __NAMESPACE__ . '\trending_posts_comment_query' );

	add_action( 'admin_print_styles', function() { wp_enqueue_style( 'tpw_scripts_styles',  plugins_url ('/css/style.css', __FILE__ ) ); } );
}

add_action( 'wp_dashboard_setup',  __NAMESPACE__ . '\wporg_add_dashboard_widgets' );


function trending_posts_query(){
	$post_args = array(
		'post_type' => 'post' ,
		'posts_per_page' => 10,
		'meta_key' => 'tpw_post_views',
		'orderby' => 'meta_value_num',
		'order' => 'DESC'
	);

	$query = new \WP_Query( $post_args );
	
	if( $query->have_posts() ){
		?>
		<table>
			<tr>
				<th><?php esc_html_e( 'Post Title', 'trending-post-widget' ); ?></th>
				<th><?php esc_html_e( 'Number of Views', 'trending-post-widget' ); ?></th>
			</tr>
			

		<?php
		while( $query->have_posts() ) {
				$query->the_post();
				echo '<tr><td>' . get_the_title() . '</td>';
				echo '<td>' . get_post_meta( get_the_ID(), 'tpw_post_views', true ) . '</td></tr>';
		}
		?>	
		</table>
		<?php
	}
	wp_reset_postdata();
}

function trending_posts_comment_query() {
	global $wpdb;
	$limit = 10;

	$commented_posts = $wpdb->get_results(
		"SELECT id, post_title, comment_count 
		FROM {$wpdb->prefix}posts 
		WHERE post_type='post' AND post_status='publish' AND comment_count !='0' 
		ORDER BY comment_count DESC 
		LIMIT " . $limit
	);

	?>
		<table>
			<tr>
				<th><?php esc_html_e( 'Post Title', 'trending-post-widget' ); ?></th>
				<th><?php esc_html_e( 'Number of Comments', 'trending-post-widget' ); ?></th>
			</tr>

			<?php
				foreach( $commented_posts as $post ){
					echo '<tr><td>' . $post->post_title . '</td>';
					echo '<td>' . $post->comment_count . '</td></tr>';
				}
			?>	
		</table>
	<?php
}