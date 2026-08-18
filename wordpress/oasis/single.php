<?php
/**
 * お知らせの個別ページ
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();

while ( have_posts() ) :
	the_post();
	$cs = get_the_category();
	oasis_page_hero(
		( get_permalink_by_slug( 'news' ) ? '<a href="' . esc_url( get_permalink_by_slug( 'news' ) ) . '">お知らせ</a> ／ ' : '' ) . get_the_title(),
		'NEWS',
		get_the_title(),
		''
	);
	?>
	<section class="section" style="padding-top:36px">
		<div class="wrap" style="max-width:840px">
			<p class="card__meta" style="margin-bottom:20px">
				<span class="card__date"><?php echo esc_html( get_the_date( 'Y.m.d' ) ); ?></span>
				<?php if ( $cs ) : ?><span class="tag"><?php echo esc_html( $cs[0]->name ); ?></span><?php endif; ?>
			</p>

			<?php if ( has_post_thumbnail() ) : ?>
				<?php the_post_thumbnail( 'large', array( 'class' => 'photo', 'style' => 'width:100%;height:auto;border-radius:var(--r-card);padding:0', 'decoding' => 'async' ) ); ?>
			<?php endif; ?>

			<div class="entry-body lead" style="margin-top:26px">
				<?php the_content(); ?>
			</div>

			<?php if ( get_permalink_by_slug( 'news' ) ) : ?>
				<div style="margin-top:40px;display:flex;justify-content:center">
					<a class="btn btn--outline-green" href="<?php echo esc_url( get_permalink_by_slug( 'news' ) ); ?>">
						お知らせ一覧にもどる <?php echo oasis_arrow( '#fff' ); // phpcs:ignore ?>
					</a>
				</div>
			<?php endif; ?>
		</div>
	</section>
	<?php
endwhile;
get_footer();
