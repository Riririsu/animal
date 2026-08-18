<?php
/**
 * どのテンプレートにも当てはまらないときに使われる、いちばん基本の型。
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();

oasis_page_hero(
	single_post_title( '', false ),
	'OASIS',
	single_post_title( '', false ) ? single_post_title( '', false ) : get_bloginfo( 'name' ),
	''
);
?>
	<section class="section" style="padding-top:44px">
		<div class="wrap">
			<?php if ( have_posts() ) : ?>
				<div class="grid grid--3" data-reveal-stagger="up">
					<?php while ( have_posts() ) : the_post(); ?>
						<a class="card" href="<?php the_permalink(); ?>">
							<?php oasis_image( get_post_thumbnail_id(), 'oasis-card', 'card__thumb card__thumb--tall', array( 'alt' => get_the_title() ) ); ?>
							<span class="card__body" style="padding:22px 26px 26px">
								<span class="card__meta"><span class="card__date"><?php echo esc_html( get_the_date( 'Y.m.d' ) ); ?></span></span>
								<span class="card__name" style="margin-top:10px"><?php the_title(); ?></span>
								<span class="card__text"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 50, '…' ) ); ?></span>
							</span>
						</a>
					<?php endwhile; ?>
				</div>
				<?php oasis_pagination(); ?>
			<?php else : ?>
				<p class="lead">まだ記事がありません。</p>
			<?php endif; ?>
		</div>
	</section>
<?php
get_footer();
