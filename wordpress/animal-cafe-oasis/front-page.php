<?php
/**
 * トップページ
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();

$tel   = oasis_tel_link();
$ig    = oasis_instagram();
$open  = oasis_option( 'open', '11:00' );
$close = oasis_option( 'close', '19:00' );
$off   = oasis_closed_label();
$price = oasis_option( 'price', '1,000円（2ドリンク付）' );
$hero  = oasis_photo_id( 'hero' );
$terms = get_terms( array( 'taxonomy' => 'animal_cat', 'hide_empty' => true, 'orderby' => 'term_id' ) );
if ( is_wp_error( $terms ) ) {
	$terms = array();
}
?>

	<!-- ========== ヒーロー ========== -->
	<section class="hero">
		<?php if ( $hero ) : ?>
			<?php echo wp_get_attachment_image( $hero, 'oasis-hero', false, array(
				'class'         => 'hero__img',
				'alt'           => '',
				'fetchpriority' => 'high',
				'decoding'      => 'async',
				'sizes'         => '100vw',
			) ); ?>
		<?php endif; ?>
		<?php // 写真は「設定 → Oasis 写真の差し替え」で入れ替えられます ?>
		<div class="hero__veil"></div>
		<svg class="deco deco--sprig" style="top:-46px;left:-40px;width:250px;height:250px;--deco-rot:-18deg" aria-hidden="true"><use href="#v2-sprig"/></svg>

		<div class="hero__inner">
			<h1 class="hero__title">ここは、動物たちと<br>過ごす<span class="hero__accent">Oasis</span>。</h1>
			<p class="hero__lead">ふれあう、観察する、癒される。<br>家族みんなで楽しめる、あにまるカフェ。</p>
			<ul class="hero__facts">
				<li class="hero__fact hero__fact--now" data-open-status><?php echo esc_html( oasis_hours_text() ); ?></li>
				<li class="hero__fact">ご予約不要</li>
				<li class="hero__fact">入場料 <?php echo esc_html( $price ); ?></li>
			</ul>
		</div>

		<div class="hero__badge">
			<svg width="26" height="26" style="color:var(--c-green)" aria-hidden="true"><use href="#ic-car"/></svg>
			<strong>駐車場あり</strong>
			<small>第二駐車場も<br>ご利用いただけます</small>
		</div>

		<svg class="wave wave--overlap wave--hero" viewBox="0 0 1280 90" preserveAspectRatio="none" aria-hidden="true">
			<path d="M0 52C160 6 330 88 520 58 700 30 860 8 1010 34 1130 55 1210 74 1280 46V92H0Z" fill="#FBF7EE"/>
		</svg>
	</section>

	<!-- ========== 店舗紹介 ========== -->
	<section class="section">
		<svg class="deco deco--blob" style="top:20px;left:-80px;width:420px;height:420px" viewBox="0 0 400 400" aria-hidden="true">
			<ellipse cx="200" cy="200" rx="180" ry="150" fill="#DFEDE9" transform="rotate(-14 200 200)"/>
		</svg>
		<svg class="deco deco--paw" style="top:44px;right:220px;width:34px;height:34px;--deco-rot:18deg" aria-hidden="true"><use href="#v2-paw"/></svg>
		<svg class="deco deco--paw" style="top:110px;right:150px;width:26px;height:26px;--deco-rot:-8deg" aria-hidden="true"><use href="#v2-paw"/></svg>

		<div class="wrap grid grid--split">
			<div data-reveal="left">
				<p class="eyebrow">ABOUT OASIS</p>
				<h2 class="section-title">動物と人を<br>笑顔でつなぐ場所</h2>
				<p class="lead">爬虫類・鳥類・小動物・うさぎ・サル・犬・猫。飲食を楽しみながら動物たちと過ごせる空間です。特徴や飼育方法をしっかりご説明したうえで、新しい家族としてお迎えいただきます。</p>
				<?php if ( get_permalink_by_slug( 'about' ) ) : ?>
					<a class="btn btn--primary" href="<?php echo esc_url( get_permalink_by_slug( 'about' ) ); ?>" style="margin-top:30px">
						店舗紹介を見る <?php echo oasis_arrow( '#fff' ); // phpcs:ignore ?>
					</a>
				<?php endif; ?>
			</div>

			<div class="photo-cluster" data-reveal="right">
				<?php echo oasis_photo( 'top-a', array( 'fallback' => '実物写真：店内の様子' ) ); // phpcs:ignore ?>
				<?php echo oasis_photo( 'top-b', array( 'fallback' => '実物写真：ふれあい' ) ); // phpcs:ignore ?>
				<?php echo oasis_photo( 'top-c', array( 'fallback' => '実物写真：どうぶつ' ) ); // phpcs:ignore ?>
			</div>
		</div>
	</section>

	<!-- ========== どうぶつ紹介 ========== -->
	<section class="section section--cream section--wave-top section--center">
		<svg class="wave" viewBox="0 0 1280 70" preserveAspectRatio="none" aria-hidden="true">
			<path d="M0 0H1280V22C1130 62 950 12 760 36 570 60 380 66 220 44 130 32 60 24 0 30Z" fill="#FBF7EE"/>
		</svg>
		<svg class="deco deco--sprig" style="top:60px;right:-46px;width:250px;height:250px;--deco-rot:12deg" aria-hidden="true"><use href="#v2-sprig"/></svg>
		<svg class="deco deco--paw" style="top:130px;left:70px;width:30px;height:30px;--deco-rot:-14deg" aria-hidden="true"><use href="#v2-paw"/></svg>

		<div class="wrap">
			<div class="section__head" data-reveal="up">
				<p class="eyebrow">ANIMALS</p>
				<h2 class="section-title">どうぶつ紹介</h2>
				<p class="lead">店内で暮らすどうぶつたちをご紹介します。体調や日によって、ふれあいをお休みしている場合があります。</p>
			</div>

			<?php if ( $terms ) : ?>
				<div class="grid grid--4" style="margin-top:38px" data-reveal-stagger="pop">
					<?php foreach ( array_slice( $terms, 0, 4 ) as $t ) :
						// そのカテゴリの代表として、いちばん上の子の写真を使う
						$rep = get_posts( array(
							'post_type'      => 'animal',
							'posts_per_page' => 1,
							'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
							'tax_query'      => array( array( 'taxonomy' => 'animal_cat', 'field' => 'term_id', 'terms' => $t->term_id ) ),
						) );
						$rep_id  = $rep ? $rep[0]->ID : 0;
						$rep_cat = $rep_id ? oasis_animal_meta( $rep_id, 'en_cat' ) : '';
						?>
						<a class="circle-link" href="<?php echo esc_url( get_term_link( $t ) ); ?>">
							<?php oasis_image( $rep_id ? oasis_main_image_id( $rep_id ) : 0, 'oasis-sq', 'circle-link__img', array( 'alt' => $t->name ) ); ?>
							<span>
								<?php if ( $rep_cat ) : ?><span class="circle-link__cat"><?php echo esc_html( $rep_cat ); ?></span><?php endif; ?>
								<span class="circle-link__name"><?php echo esc_html( $t->name ); ?></span>
								<span class="circle-link__note"><?php echo esc_html( $t->count ); ?>種</span>
							</span>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<div style="margin-top:40px;display:flex;justify-content:center" data-reveal="up">
				<a class="btn btn--outline-green btn--lg" href="<?php echo esc_url( get_post_type_archive_link( 'animal' ) ); ?>">
					すべてのどうぶつを見る <?php echo oasis_arrow( '#fff' ); // phpcs:ignore ?>
				</a>
			</div>
		</div>
	</section>

	<!-- ========== 来店の流れ ========== -->
	<section class="section">
		<svg class="deco deco--blob" style="bottom:-120px;right:-110px;width:460px;height:460px" viewBox="0 0 400 400" aria-hidden="true">
			<ellipse cx="200" cy="200" rx="190" ry="155" fill="#DFEDE9" transform="rotate(16 200 200)"/>
		</svg>

		<div class="wrap grid grid--split-rev">
			<div data-reveal="left">
				<p class="eyebrow">FIRST VISIT</p>
				<h2 class="section-title">来店の流れ</h2>
				<p class="lead">予約は承っておりません。営業時間内に直接お越しください。スタッフがふれあいのルールをご案内します。</p>
				<div class="hours-box">
					<p class="hours-box__now" data-open-status><?php echo esc_html( oasis_hours_text() ); ?></p>
					<p class="note" style="margin-top:8px">
						<?php echo $off ? '定休日 ' . esc_html( $off ) . '／' : ''; ?>入場料 <?php echo esc_html( $price ); ?>
					</p>
				</div>
			</div>

			<div class="grid grid--3 grid--steps" data-reveal-stagger="up">
				<div class="step-card"><span class="step-card__num">1</span><?php echo oasis_photo( 'step-1' ); // phpcs:ignore ?><p class="step-card__title">そのままご来店（ご予約不要）</p><p class="step-card__text">予約は承っておりません</p></div>
				<div class="step-card"><span class="step-card__num">2</span><?php echo oasis_photo( 'step-2' ); // phpcs:ignore ?><p class="step-card__title">受付・手指消毒・ルール確認</p><p class="step-card__text">スタッフがご案内します</p></div>
				<div class="step-card"><span class="step-card__num">3</span><?php echo oasis_photo( 'step-3' ); // phpcs:ignore ?><p class="step-card__title">ドリンクを選んでふれあい</p><p class="step-card__text">1時間・2ドリンク付</p></div>
			</div>
		</div>
	</section>

	<!-- ========== メニュー・料金 ========== -->
	<section class="section section--green section--wave-top">
		<svg class="wave" viewBox="0 0 1280 70" preserveAspectRatio="none" aria-hidden="true">
			<path d="M0 0H1280V26C1120 66 940 14 750 40 560 66 370 70 210 48 120 36 58 28 0 34Z" fill="#FBF7EE"/>
		</svg>
		<svg class="deco deco--sprig" style="bottom:-40px;left:-50px;width:280px;height:280px;--deco-rot:-24deg" aria-hidden="true"><use href="#v2-sprig"/></svg>

		<div class="wrap grid grid--2" style="align-items:center;gap:44px">
			<div data-reveal="left">
				<p class="eyebrow">MENU</p>
				<h2 class="section-title">メニュー・料金</h2>
				<p class="lead">入場料には2ドリンクが付いています。お食事のみのご利用も歓迎です。</p>

				<?php
				// 料金の行は「設定 → Oasis サイト設定 → 営業時間」で編集できます
				$rows = array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) oasis_option( 'price_rows', '' ) ) ) );
				if ( $rows ) : ?>
					<div class="rows rows--onGreen" style="margin-top:26px">
						<?php foreach ( $rows as $row ) :
							$cols = array_map( 'trim', explode( '|', $row, 2 ) ); ?>
							<div class="row">
								<span><?php echo esc_html( $cols[0] ); ?></span>
								<span class="row__value"><?php echo esc_html( isset( $cols[1] ) ? $cols[1] : '' ); ?></span>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<?php if ( oasis_option( 'price_note', '' ) ) : ?>
					<p class="note" style="margin-top:14px"><?php echo esc_html( oasis_option( 'price_note', '' ) ); ?></p>
				<?php endif; ?>

				<?php if ( get_permalink_by_slug( 'menu' ) ) : ?>
					<a class="btn btn--gold" href="<?php echo esc_url( get_permalink_by_slug( 'menu' ) ); ?>" style="margin-top:26px">
						メニューをすべて見る <?php echo oasis_arrow( '#2C2415' ); // phpcs:ignore ?>
					</a>
				<?php endif; ?>
			</div>

			<div data-reveal="right">
				<?php echo oasis_photo( 'drink-a', array( 'fallback' => 'ドリンク・ケーキ写真' ) ); // phpcs:ignore ?>
			</div>
		</div>
	</section>

	<!-- ========== ルール・お知らせ ========== -->
	<section class="section">
		<svg class="deco deco--paw" style="top:56px;left:34px;width:30px;height:30px;--deco-rot:-12deg" aria-hidden="true"><use href="#v2-paw"/></svg>

		<div class="wrap grid grid--2" style="align-items:start">
			<div class="panel panel--gold" data-reveal="up">
				<p class="eyebrow eyebrow--gold">RULES</p>
				<h2 class="panel__title" style="margin-top:12px">ご利用ルール</h2>
				<ul class="bullets">
					<li>動物の体調を優先し、無理なふれあいは行いません</li>
					<li>ふれあい前後の手指消毒をお願いします</li>
					<li>フラッシュ撮影はご遠慮ください</li>
					<li>小さなお子さまは保護者の方とご一緒にお願いします</li>
				</ul>
				<?php if ( get_permalink_by_slug( 'rules' ) ) : ?>
					<a class="link-arrow" href="<?php echo esc_url( get_permalink_by_slug( 'rules' ) ); ?>">ルールをすべて見る →</a>
				<?php endif; ?>
			</div>

			<div class="panel panel--cream" data-reveal="up" style="--reveal-delay:.12s">
				<p class="eyebrow">NEWS</p>
				<h2 class="panel__title" style="margin-top:12px">お知らせ</h2>
				<p class="lead" style="margin-top:0">新しい仲間の入荷、臨時休業、店内の様子などをお届けします。</p>

				<div style="margin-top:18px;display:flex;flex-direction:column;gap:12px">
					<?php
					$news = get_posts( array( 'posts_per_page' => 3 ) );
					if ( $news ) :
						foreach ( $news as $n ) : ?>
							<a class="news-mini" href="<?php echo esc_url( get_permalink( $n->ID ) ); ?>">
								<?php
								$thumb = get_post_thumbnail_id( $n->ID );
								if ( $thumb ) {
									echo wp_get_attachment_image( $thumb, 'oasis-sq', false, array(
										'class' => 'news-mini__img', 'alt' => get_the_title( $n->ID ),
										'loading' => 'lazy', 'decoding' => 'async',
									) );
								} else {
									echo oasis_photo( 'news-closed', array( 'alt' => get_the_title( $n->ID ) ) ); // phpcs:ignore
								}
								?>
								<span>
									<span class="news-mini__date"><?php echo esc_html( get_the_date( 'Y.m.d', $n ) ); ?></span>
									<span class="news-mini__title"><?php echo esc_html( get_the_title( $n->ID ) ); ?></span>
								</span>
							</a>
						<?php endforeach;
					else : ?>
						<p class="note" style="color:var(--c-text)">お知らせはまだありません。</p>
					<?php endif; ?>
				</div>

				<?php if ( get_permalink_by_slug( 'news' ) ) : ?>
					<a class="link-arrow" href="<?php echo esc_url( get_permalink_by_slug( 'news' ) ); ?>">お知らせをすべて見る →</a>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<!-- ========== アクセス ========== -->
	<section class="section section--cream section--wave-top">
		<svg class="wave" viewBox="0 0 1280 66" preserveAspectRatio="none" aria-hidden="true">
			<path d="M0 0H1280V20C1140 60 960 12 770 36 580 60 380 64 220 42 130 30 58 22 0 28Z" fill="#FBF7EE"/>
		</svg>
		<svg class="deco deco--sprig" style="top:70px;right:-40px;width:230px;height:230px;--deco-rot:14deg" aria-hidden="true"><use href="#v2-sprig"/></svg>

		<div class="wrap">
			<div class="section__head" data-reveal="up">
				<p class="eyebrow">ACCESS</p>
				<h2 class="section-title" style="margin-bottom:26px">アクセス・駐車場</h2>
			</div>

			<div class="grid grid--media">
				<?php $oasis_map = oasis_map_embed( ' data-reveal="left"' ); ?>
				<?php if ( '' !== $oasis_map ) : ?>
					<?php echo $oasis_map; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- 保存時に wp_kses 済み ?>
				<?php else : ?>
					<div class="photo photo--water photo--h-xl" data-reveal="left">Googleマップ</div>
				<?php endif; ?>

				<div data-reveal="right">
					<div class="panel">
						<p class="lead" style="margin-top:0">
							<?php echo esc_html( oasis_option( 'address', '' ) ); ?><br>
							TEL <?php echo esc_html( oasis_option( 'tel_display', '' ) ); ?><br>
							<?php echo esc_html( $open . ' – ' . $close ); ?><?php echo $off ? '／' . esc_html( $off ) . '定休' : ''; ?>
						</p>
					</div>
					<div class="panel" style="margin-top:20px">
						<p class="panel__title" style="margin-bottom:0;font-size:var(--fs-step-t);color:var(--c-green)">第二駐車場のご案内</p>
						<p class="note" style="margin-top:10px;color:var(--c-text)">満車の際は「薩摩味処 喰宴」駐車場をご利用ください。踏切を渡ってすぐ左折、右側です。</p>
						<div style="margin-top:14px">
							<a class="photo-map-link" href="<?php echo esc_url( get_permalink_by_slug( 'access' ) ? get_permalink_by_slug( 'access' ) : home_url( '/' ) ); ?>">
								<?php echo oasis_photo( 'parking-map', array( 'class' => 'photo-map photo-map--sm', 'alt' => '第二駐車場の案内図' ) ); // phpcs:ignore ?>
								<span class="photo-map-link__note">案内図を大きく見る →</span>
							</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>

<?php
// 最後のセクションがクリーム（--c-cream-2）の帯なので、フッターの波も同じ色にする
oasis_set_footer_wave( '#F3F0E2' );
get_footer();
