<?php
/**
 * どうぶつ個別ページ
 *
 * 写真はすべて管理画面から差し替えられます。
 *   メイン写真 … アイキャッチ画像
 *   サムネイル … メイン写真 ＋「サブ写真」（押すとメインが入れ替わります）
 *   POPカード … 「店内POPカード」
 *
 * 「お迎えのご相談」の枠は、どうぶつごとのチェックボックスで出し分けます。
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();

while ( have_posts() ) :
	the_post();

	$id      = get_the_ID();
	$gallery = oasis_gallery_ids( $id );
	$main    = $gallery ? $gallery[0] : 0;
	$pop     = (int) get_post_meta( $id, '_oasis_pop', true );
	$tel     = oasis_tel_link();
	$ig      = oasis_instagram();

	// 同じカテゴリの他の子を最大4件
	$cats   = wp_get_object_terms( $id, 'animal_cat', array( 'fields' => 'ids' ) );
	$others = get_posts( array(
		'post_type'      => 'animal',
		'posts_per_page' => 4,
		'post__not_in'   => array( $id ),
		'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
		'tax_query'      => ( ! is_wp_error( $cats ) && $cats )
			? array( array( 'taxonomy' => 'animal_cat', 'field' => 'term_id', 'terms' => $cats ) )
			: array(),
	) );
	if ( count( $others ) < 4 ) {
		$fill = get_posts( array(
			'post_type'      => 'animal',
			'posts_per_page' => 4 - count( $others ),
			'post__not_in'   => array_merge( array( $id ), wp_list_pluck( $others, 'ID' ) ),
			'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
		) );
		$others = array_merge( $others, $fill );
	}
	?>

	<section class="section" style="padding:26px 0 0">
		<div class="wrap">
			<p class="breadcrumb">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>">ホーム</a> ／
				<a href="<?php echo esc_url( get_post_type_archive_link( 'animal' ) ); ?>">どうぶつ紹介</a> ／
				<?php the_title(); ?>
			</p>
		</div>
	</section>

	<section class="section" style="padding-top:24px">
		<svg class="deco deco--blob" style="top:-90px;right:-120px;width:420px;height:420px" viewBox="0 0 400 400" aria-hidden="true">
			<ellipse cx="200" cy="200" rx="180" ry="150" fill="#DFEDE9" transform="rotate(12 200 200)"/>
		</svg>

		<div class="wrap grid grid--wide-l">
			<div data-reveal="left">
				<?php
				if ( $main ) {
					echo wp_get_attachment_image( $main, 'oasis-main', false, array(
						'class'         => 'photo photo--organic-3 photo--animal',
						'id'            => 'animal-photo',
						'alt'           => get_the_title(),
						'fetchpriority' => 'high',
						'decoding'      => 'async',
						'sizes'         => '(max-width: 1024px) 94vw, 46vw',
					) );
				} else {
					echo '<div class="photo photo--organic-3 photo--animal">実物写真：' . esc_html( get_the_title() ) . '</div>';
				}
				?>

				<?php if ( count( $gallery ) > 1 ) : ?>
					<div class="thumb-strip" data-gallery data-gallery-target="#animal-photo">
						<?php foreach ( $gallery as $i => $att ) :
							$big = wp_get_attachment_image_url( $att, 'oasis-main' );
							$alt = sprintf( '%sの写真%d枚目', get_the_title(), $i + 1 );
							if ( ! $big ) { continue; } ?>
							<button type="button" data-photo="<?php echo esc_url( $big ); ?>"
								data-alt="<?php echo esc_attr( $alt ); ?>"
								aria-current="<?php echo 0 === $i ? 'true' : 'false'; ?>">
								<?php echo wp_get_attachment_image( $att, 'oasis-thumb', false, array( 'alt' => $alt, 'decoding' => 'async' ) ); ?>
							</button>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>

			<div data-reveal="right">
				<?php if ( oasis_animal_meta( $id, 'en_cat' ) ) : ?>
					<p class="eyebrow" style="letter-spacing:.2em"><?php echo esc_html( oasis_animal_meta( $id, 'en_cat' ) ); ?></p>
				<?php endif; ?>

				<h1 class="detail-title"><?php the_title(); ?></h1>

				<?php if ( oasis_animal_meta( $id, 'en' ) ) : ?>
					<p class="detail-sub"><?php echo esc_html( oasis_animal_meta( $id, 'en' ) ); ?></p>
				<?php endif; ?>

				<?php if ( oasis_animal_meta( $id, 'lead' ) ) : ?>
					<p class="lead" style="margin-top:22px"><?php echo esc_html( oasis_animal_meta( $id, 'lead' ) ); ?></p>
				<?php endif; ?>

				<?php
				$rows = array( 'origin' => '原産地', 'size' => '大きさ', 'life' => '寿命', 'food' => '食べ物' );
				$has  = false;
				foreach ( $rows as $k => $l ) {
					if ( oasis_animal_meta( $id, $k ) ) { $has = true; break; }
				}
				?>
				<?php if ( $has ) : ?>
					<div class="rows" style="margin-top:24px">
						<?php foreach ( $rows as $k => $l ) :
							$v = oasis_animal_meta( $id, $k );
							if ( ! $v ) { continue; } ?>
							<div class="row">
								<span class="row__label"><?php echo esc_html( $l ); ?></span>
								<span class="row__value"><?php echo esc_html( $v ); ?></span>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<?php if ( oasis_show_adopt( $id ) ) : ?>
					<div class="panel panel--cream adopt-panel" style="margin-top:22px">
						<h2 class="panel__title" style="margin-bottom:10px">お迎えのご相談</h2>
						<p class="note" style="color:var(--c-text)"><?php echo esc_html( oasis_option( 'adopt_text', '' ) ); ?></p>
						<div style="display:flex;flex-wrap:wrap;gap:12px;margin-top:20px">
							<a class="btn btn--primary" href="<?php echo esc_attr( $tel ); ?>">電話で相談</a>
							<?php if ( $ig ) : ?>
								<a class="btn btn--outline" href="<?php echo esc_url( $ig ); ?>" target="_blank" rel="noopener">Instagram DM</a>
							<?php endif; ?>
						</div>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<?php if ( $pop || oasis_animal_meta( $id, 'feature' ) || oasis_animal_meta( $id, 'trivia' ) ) : ?>
		<section class="section section--cream section--wave-top">
			<svg class="wave" viewBox="0 0 1280 66" preserveAspectRatio="none" aria-hidden="true">
				<path d="M0 0H1280V20C1140 60 960 12 770 36 580 60 380 64 220 42 130 30 58 22 0 28Z" fill="#FBF7EE"/>
			</svg>

			<div class="wrap">
				<h2 class="section-title section-title--sm" style="margin:0 0 20px" data-reveal="up">店内POPカード</h2>
				<div class="grid grid--2" style="align-items:start">
					<?php if ( $pop ) : ?>
						<?php echo wp_get_attachment_image( $pop, 'oasis-pop', false, array(
							'class'       => 'photo-pop',
							'alt'         => sprintf( '%sの店内POPカード', get_the_title() ),
							'loading'     => 'lazy',
							'decoding'    => 'async',
							'data-reveal' => 'left',
						) ); ?>
					<?php else : ?>
						<div class="photo photo--sand photo--h-lg" data-reveal="left">POPカード画像</div>
					<?php endif; ?>

					<div data-reveal="right">
						<?php if ( oasis_animal_meta( $id, 'blurb' ) ) : ?>
							<p class="lead" style="margin-top:0"><?php echo esc_html( oasis_animal_meta( $id, 'blurb' ) ); ?></p>
						<?php endif; ?>

						<?php if ( oasis_animal_meta( $id, 'feature' ) || oasis_animal_meta( $id, 'trivia' ) ) : ?>
							<div class="panel" style="margin-top:20px">
								<?php if ( oasis_animal_meta( $id, 'feature' ) ) : ?>
									<h3 class="panel__title" style="margin-bottom:8px">特徴</h3>
									<p class="note" style="color:var(--c-text)"><?php echo esc_html( oasis_animal_meta( $id, 'feature' ) ); ?></p>
								<?php endif; ?>
								<?php if ( oasis_animal_meta( $id, 'trivia' ) ) : ?>
									<h3 class="panel__title" style="margin:22px 0 8px">豆知識</h3>
									<p class="note" style="color:var(--c-text)"><?php echo esc_html( oasis_animal_meta( $id, 'trivia' ) ); ?></p>
								<?php endif; ?>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $others ) : ?>
		<section class="section">
			<div class="wrap">
				<h2 class="section-title section-title--sm" style="margin:0 0 22px" data-reveal="up">ほかのどうぶつ</h2>
				<div class="grid grid--4" data-reveal-stagger="pop">
					<?php foreach ( $others as $o ) : ?>
						<a class="circle-link" href="<?php echo esc_url( get_permalink( $o->ID ) ); ?>">
							<?php oasis_image( oasis_main_image_id( $o->ID ), 'oasis-sq', 'circle-link__img', array( 'alt' => get_the_title( $o->ID ) ) ); ?>
							<span class="circle-link__name"><?php echo esc_html( get_the_title( $o->ID ) ); ?></span>
						</a>
					<?php endforeach; ?>
				</div>
				<div style="margin-top:34px;display:flex;justify-content:center" data-reveal="up">
					<a class="btn btn--outline-green" href="<?php echo esc_url( get_post_type_archive_link( 'animal' ) ); ?>">
						どうぶつ紹介にもどる <?php echo oasis_arrow( '#fff' ); // phpcs:ignore ?>
					</a>
				</div>
			</div>
		</section>
	<?php endif; ?>

<?php
endwhile;
get_footer();
