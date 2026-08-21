<?php
/**
 * どうぶつのカテゴリ別一覧
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();

$term  = get_queried_object();
$terms = get_terms( array( 'taxonomy' => 'animal_cat', 'hide_empty' => true, 'orderby' => 'term_id' ) );
if ( is_wp_error( $terms ) ) {
	$terms = array();
}

oasis_page_hero(
	'<a href="' . esc_url( get_post_type_archive_link( 'animal' ) ) . '">どうぶつ紹介</a> ／ ' . esc_html( $term->name ),
	'ANIMALS',
	$term->name,
	$term->description ? $term->description : sprintf( '%s のどうぶつ %d種をご紹介します。', $term->name, (int) $term->count )
);
?>
	<section class="section" style="padding-top:44px">
		<div class="wrap">
			<div class="chips" role="group" aria-label="どうぶつの絞り込み" data-reveal="up">
				<a class="chip" href="<?php echo esc_url( get_post_type_archive_link( 'animal' ) ); ?>">すべて</a>
				<?php foreach ( $terms as $t ) : ?>
					<a class="chip" <?php echo ( $t->term_id === $term->term_id ) ? 'aria-current="page"' : ''; ?>
						href="<?php echo esc_url( get_term_link( $t ) ); ?>"><?php echo esc_html( $t->name ); ?></a>
				<?php endforeach; ?>
			</div>

			<?php if ( have_posts() ) : ?>
				<div class="grid grid--4" style="margin-top:34px" data-reveal-stagger="up">
					<?php
					$i = 0;
					while ( have_posts() ) :
						the_post();
						set_query_var( 'oasis_card_index', $i );
						get_template_part( 'template-parts/card', 'animal' );
						$i++;
					endwhile;
					?>
				</div>
			<?php else : ?>
				<p class="lead" style="margin-top:34px">このカテゴリのどうぶつはまだ登録されていません。</p>
			<?php endif; ?>
		</div>
	</section>
<?php
get_footer();
