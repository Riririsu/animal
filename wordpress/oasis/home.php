<?php
/**
 * お知らせ一覧
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();

$cats = get_terms( array( 'taxonomy' => 'category', 'hide_empty' => true ) );
if ( is_wp_error( $cats ) ) {
	$cats = array();
}

oasis_page_hero( 'お知らせ・ブログ', 'NEWS', 'お知らせ・ブログ', '新しい仲間の入荷、臨時休業、店内の様子などをお届けします。' );
?>
	<section class="section" style="padding-top:44px">
		<div class="wrap">

			<?php if ( $cats ) : ?>
				<div class="chips" role="group" aria-label="お知らせの絞り込み"
					data-filter-group data-filter-target="#news-list" data-reveal="up">
					<button class="chip" type="button" data-filter="all" aria-pressed="true">すべて</button>
					<?php foreach ( $cats as $c ) : ?>
						<button class="chip" type="button" data-filter="<?php echo esc_attr( $c->slug ); ?>" aria-pressed="false"><?php echo esc_html( $c->name ); ?></button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php if ( have_posts() ) : ?>
				<div class="grid grid--3" id="news-list" aria-live="polite" style="margin-top:34px" data-reveal-stagger="up">
					<?php while ( have_posts() ) : the_post();
						$slugs = wp_get_post_categories( get_the_ID(), array( 'fields' => 'slugs' ) ); ?>
						<a class="card" href="<?php the_permalink(); ?>" data-cat="<?php echo esc_attr( implode( ' ', (array) $slugs ) ); ?>">
							<?php oasis_image( get_post_thumbnail_id(), 'oasis-card', 'card__thumb card__thumb--tall', array( 'alt' => get_the_title() ) ); ?>
							<span class="card__body" style="padding:22px 26px 26px">
								<span class="card__meta">
									<span class="card__date"><?php echo esc_html( get_the_date( 'Y.m.d' ) ); ?></span>
									<?php
									$cs = get_the_category();
									if ( $cs ) {
										echo '<span class="tag">' . esc_html( $cs[0]->name ) . '</span>';
									}
									?>
								</span>
								<span class="card__name" style="margin-top:10px"><?php the_title(); ?></span>
								<span class="card__text"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 50, '…' ) ); ?></span>
							</span>
						</a>
					<?php endwhile; ?>
				</div>
				<?php oasis_pagination(); ?>
			<?php else : ?>
				<p class="lead" style="margin-top:34px">お知らせはまだありません。</p>
			<?php endif; ?>

		</div>
	</section>
<?php
get_footer();
