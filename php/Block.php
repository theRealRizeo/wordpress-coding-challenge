<?php
/**
 * Block class.
 *
 * @package SiteCounts
 */

namespace XWP\SiteCounts;

use WP_Block;
use WP_Query;

/**
 * The Site Counts dynamic block.
 *
 * Registers and renders the dynamic block.
 */
class Block {

	/**
	 * The Plugin instance.
	 *
	 * @var Plugin
	 */
	protected $plugin;

	/**
	 * Instantiates the class.
	 *
	 * @param Plugin $plugin The plugin object.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Adds the action to register the block.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'init', [ $this, 'register_block' ] );
	}

	/**
	 * Registers the block.
	 */
	public function register_block() {
		register_block_type_from_metadata(
			$this->plugin->dir(),
			[
				'render_callback' => [ $this, 'render_callback' ],
			]
		);
	}

	/**
	 * Renders the block.
	 *
	 * @param array    $attributes The attributes for the block.
	 * @param string   $content    The block content, if any.
	 * @param WP_Block $block      The instance of this block.
	 * @return string The markup of the block.
	 */
	public function render_callback( $attributes, $content, $block ) {
		$post_types      = get_post_types( array( 'public' => true ) );
		$class_name      = sanitize_html_class( $attributes['className'] );
		$current_post_id = get_the_ID();

		ob_start();

		?>
		<div class="<?php echo esc_attr( $class_name ); ?>">
			<h2><?php esc_html_e( 'Post Counts', 'site-counts' ); ?></h2>
			<ul>
				<?php
				foreach ( $post_types as $post_type_slug ) :
					$post_type_object = get_post_type_object( $post_type_slug );
					$posts_count      = wp_count_posts( $post_type_slug );
					?>
					<li>
						<?php
						/* Translators: %1$d The total count, %2$s The post type label. */
						printf( esc_html__( 'There are %1$d %2$s.', 'site-counts' ), esc_attr( $posts_count->publish ), esc_html( $post_type_object->labels->name ) );
						?>
					</li>
				<?php endforeach; ?>
			</ul>
			<?php if ( $current_post_id ) : ?>
				<p>
					<?php
					/* Translators: %d The current post id. */
					printf( esc_html__( 'The current post ID is %d.', 'site-counts' ), esc_attr( $current_post_id ) );
					?>
				</p>
			<?php endif; ?>
			<?php
			$query = new WP_Query(
				array(
					'post_type'        => array( 'post', 'page' ),
					'post_status'      => 'any',
					'date_query'       => array(
						array(
							'hour'     => 9,
							'compare'  => '>=',
						),
						array(
							'hour'     => 17,
							'compare'  => '<=',
						),
					),
					'tag'              => 'foo',
					'category_name'    => 'baz',
					'posts_per_page '  => 5,
					'suppress_filters' => false,
				)
			);

			if ( $query->have_posts() ) :
				?>
				<h2>
					<?php
					/* Translators: %1$s The tag name, %2$s The category name. */
					printf( esc_html__( '5 posts with the tag of %1$s and the category of %2$s', 'site-counts' ), 'foo', 'baz' );
					?>
				</h2>
				<ul>
					<?php foreach ( $query->posts as $post ) : ?>
						<li><?php echo esc_html( $post->post_title ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
		<?php

		return ob_get_clean();
	}
}
