<?php
/**
 * サイトの写真の差し替え（設定 → Oasis 写真の差し替え）
 *
 * どうぶつの写真は「どうぶつ」の編集画面で入れ替えます。
 * それ以外の写真（トップの大きな写真・店内・スタッフ・ドリンクなど）は、
 * このファイルが用意する1つの画面でまとめて入れ替えられます。
 *
 * ページの本文側には [oasis_photo slot="..."] という短い記述が入っていて、
 * ここで選んだ写真がそこに表示されます。写真を選んでいないときは、
 * これまでどおり色のついた枠が出ます。
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 差し替えできる写真の一覧。
 *
 *   label … 管理画面に出る名前
 *   note  … 補足
 *   size  … 使う画像サイズ
 *   class … 枠の形（この class は変えないでください）
 *   ratio … 管理画面のプレビューの形（cover の比率）
 */
function oasis_photo_slots() {
	return array(

		'トップページ' => array(
			'hero' => array(
				'label' => 'いちばん上の大きな写真',
				'note'  => '横長。左半分に文字が重なるので、被写体が右寄りの写真がおすすめです。',
				'size'  => 'oasis-hero',
				'class' => 'hero__img',
			),
			'top-a' => array(
				'label' => '店舗紹介：大きい写真',
				'note'  => '「動物と人を笑顔でつなぐ場所」の横に出る、いちばん大きい写真。',
				'size'  => 'oasis-wide',
				'class' => 'photo photo--organic photo-cluster__a',
			),
			'top-b' => array(
				'label' => '店舗紹介：中くらいの写真',
				'note'  => '大きい写真に少し重なる、二番目の写真。',
				'size'  => 'oasis-sq',
				'class' => 'photo photo--sand photo--organic-2 photo--framed photo-cluster__b',
			),
			'top-c' => array(
				'label' => '店舗紹介：丸い写真',
				'note'  => '小さい丸。スマホでは表示されません。',
				'size'  => 'oasis-sq',
				'class' => 'photo photo--mist photo--circle photo--framed photo-cluster__c',
			),
			'step-1' => array(
				'label' => '来店の流れ①　そのままご来店',
				'note'  => '横長。お店の入口など。',
				'size'  => 'oasis-card',
				'class' => 'step-card__photo',
			),
			'step-2' => array(
				'label' => '来店の流れ②　受付・ルール確認',
				'note'  => '横長。受付やご案内の様子など。',
				'size'  => 'oasis-card',
				'class' => 'step-card__photo step-card__photo--sand',
			),
			'step-3' => array(
				'label' => '来店の流れ③　ドリンクを選んでふれあい',
				'note'  => '横長。ふれあいの様子など。',
				'size'  => 'oasis-card',
				'class' => 'step-card__photo step-card__photo--mist',
			),
			'parking-map' => array(
				'label' => '第二駐車場の案内図',
				'note'  => '縦長。写真を選ばない場合は、いまの案内図（イラスト）が出ます。',
				'size'  => 'oasis-pop',
				'class' => 'photo-map',
			),
		),

		'店舗紹介ページ' => array(
			'about-interior' => array(
				'label' => '店内の様子',
				'size'  => 'oasis-wide',
				'class' => 'photo photo--organic photo-cluster__a',
			),
			'about-exterior' => array(
				'label' => 'お店の外観',
				'size'  => 'oasis-sq',
				'class' => 'photo photo--sand photo--circle photo--framed photo-cluster__b',
			),
			'staff-1' => array(
				'label' => 'スタッフ①',
				'note'  => '正方形の写真。丸く切り抜かれます。',
				'size'  => 'oasis-sq',
				'class' => 'circle-link__img circle-link__img--plain',
			),
			'staff-2' => array(
				'label' => 'スタッフ②',
				'size'  => 'oasis-sq',
				'class' => 'circle-link__img circle-link__img--plain',
			),
			'staff-3' => array(
				'label' => 'スタッフ③',
				'size'  => 'oasis-sq',
				'class' => 'circle-link__img circle-link__img--plain',
			),
		),

		'メニュー・生体販売' => array(
			'drink-a' => array(
				'label' => 'トップ：ドリンク・ケーキ（大）',
				'size'  => 'oasis-wide',
				'class' => 'photo photo--sand photo--organic-3 photo-cluster__a',
			),
			'drink-b' => array(
				'label' => 'トップ：ドリンク（丸）',
				'size'  => 'oasis-sq',
				'class' => 'photo photo--sand photo--circle photo--framed-green photo-cluster__b',
			),
			'menu-photo' => array(
				'label' => 'メニューページの写真',
				'note'  => '横長。ドリンクやケーキなど。',
				'size'  => 'oasis-wide',
				'class' => 'photo photo--sand photo--organic-3 photo--h-lg',
			),
			'sales-photo' => array(
				'label' => '生体販売ページ：ご説明の様子',
				'size'  => 'oasis-wide',
				'class' => 'photo photo--organic-2 photo--h-sm',
			),
		),

		'お知らせ' => array(
			'news-closed' => array(
				'label' => 'トップに出す「臨時休業」の丸写真',
				'note'  => '記事にアイキャッチ画像を設定していないときに使われます。',
				'size'  => 'oasis-sq',
				'class' => 'news-mini__img news-mini__img--sand',
			),
		),
	);
}

/** すべてのスロットを1階層の配列にして返す。 */
function oasis_photo_slots_flat() {
	$out = array();
	foreach ( oasis_photo_slots() as $group ) {
		foreach ( $group as $key => $slot ) {
			$out[ $key ] = $slot;
		}
	}
	return $out;
}

/** 指定したスロットに設定されている画像ID。無ければ 0。 */
function oasis_photo_id( $slot ) {
	$all = get_option( 'oasis_photos', array() );
	return isset( $all[ $slot ] ) ? (int) $all[ $slot ] : 0;
}

/** 画像IDを保存する（初期データの取り込みからも使います）。 */
function oasis_set_photo_id( $slot, $id ) {
	$all = get_option( 'oasis_photos', array() );
	$all[ $slot ] = (int) $id;
	update_option( 'oasis_photos', $all );
}

/**
 * スロットの写真を出す。写真が無ければ、これまでどおり色のついた枠を出す。
 *
 * @param string $slot  スロット名
 * @param array  $args  class / alt / fallback / size / lazy を上書きできます
 */
function oasis_photo( $slot, $args = array() ) {
	$slots = oasis_photo_slots_flat();
	$def   = isset( $slots[ $slot ] ) ? $slots[ $slot ] : array();

	$args = wp_parse_args( $args, array(
		'class'    => isset( $def['class'] ) ? $def['class'] : 'photo',
		'size'     => isset( $def['size'] ) ? $def['size'] : 'large',
		'alt'      => '',
		'fallback' => '',
		'lazy'     => true,
		'attr'     => array(),
	) );

	$id = oasis_photo_id( $slot );

	if ( $id ) {
		$attr = array_merge( array(
			'class'    => $args['class'],
			'alt'      => $args['alt'],
			'decoding' => 'async',
			'loading'  => $args['lazy'] ? 'lazy' : 'eager',
		), $args['attr'] );
		return wp_get_attachment_image( $id, $args['size'], false, $attr );
	}

	// 写真が無いとき
	if ( 'parking-map' === $slot ) {
		return sprintf(
			'<img class="%s" src="%s" width="800" height="1340" loading="lazy" decoding="async" alt="第二駐車場の案内図">',
			esc_attr( $args['class'] ),
			esc_url( OASIS_URI . '/assets/images/parking-map.svg' )
		);
	}

	$tag = ( false !== strpos( $args['class'], 'circle-link__img' )
		|| false !== strpos( $args['class'], 'step-card__photo' )
		|| false !== strpos( $args['class'], 'news-mini__img' ) ) ? 'span' : 'div';

	return sprintf(
		'<%1$s class="%2$s">%3$s</%1$s>',
		$tag,
		esc_attr( $args['class'] ),
		esc_html( $args['fallback'] )
	);
}

/**
 * 本文の中で使う書き方：
 *   [oasis_photo slot="about-interior" alt="店内の様子" fallback="実物写真：店内の様子"]
 */
function oasis_photo_shortcode( $atts ) {
	$atts = shortcode_atts( array(
		'slot'     => '',
		'class'    => '',
		'size'     => '',
		'alt'      => '',
		'fallback' => '',
		'reveal'   => '',
	), $atts, 'oasis_photo' );

	if ( ! $atts['slot'] ) {
		return '';
	}

	$args = array( 'alt' => $atts['alt'], 'fallback' => $atts['fallback'] );
	if ( $atts['class'] ) { $args['class'] = $atts['class']; }
	if ( $atts['size'] )  { $args['size']  = $atts['size']; }
	if ( $atts['reveal'] ) { $args['attr'] = array( 'data-reveal' => $atts['reveal'] ); }

	$html = oasis_photo( $atts['slot'], $args );

	// 枠（写真が無いとき）にも data-reveal を付ける
	if ( $atts['reveal'] && false === strpos( $html, 'data-reveal' ) ) {
		$html = preg_replace( '/^<(div|span) /', '<$1 data-reveal="' . esc_attr( $atts['reveal'] ) . '" ', $html );
	}
	return $html;
}
add_shortcode( 'oasis_photo', 'oasis_photo_shortcode' );

/* ------------------------------------------------------------------ *
 *  管理画面
 * ------------------------------------------------------------------ */

function oasis_photos_menu() {
	add_options_page( 'Oasis 写真の差し替え', 'Oasis 写真の差し替え', 'manage_options', 'oasis-photos', 'oasis_photos_page' );
}
add_action( 'admin_menu', 'oasis_photos_menu' );

function oasis_photos_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( isset( $_POST['oasis_photos_nonce'] )
		&& wp_verify_nonce( sanitize_key( wp_unslash( $_POST['oasis_photos_nonce'] ) ), 'oasis_save_photos' ) ) {

		$raw  = isset( $_POST['oasis_photo'] ) ? (array) wp_unslash( $_POST['oasis_photo'] ) : array();
		$save = array();
		foreach ( array_keys( oasis_photo_slots_flat() ) as $key ) {
			$save[ $key ] = isset( $raw[ $key ] ) ? absint( $raw[ $key ] ) : 0;
		}
		update_option( 'oasis_photos', $save );
		echo '<div class="notice notice-success is-dismissible"><p>写真を保存しました。</p></div>';
	}
	?>
	<div class="wrap oasis-settings">
		<h1>Oasis 写真の差し替え</h1>
		<p>
			サイトに出てくる写真を、ここでまとめて差し替えられます。<br>
			<strong>写真は1枚アップロードすれば、必要な大きさに自動で切り抜かれます。</strong>
			切り抜きは中央が基準なので、被写体が中央にくる写真を選んでください。
		</p>
		<p class="description">
			どうぶつの写真は、この画面ではなく「<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=animal' ) ); ?>">どうぶつ</a>」の編集画面で入れ替えます。<br>
			お知らせの写真は、記事の「アイキャッチ画像」です。
		</p>

		<form method="post">
			<?php wp_nonce_field( 'oasis_save_photos', 'oasis_photos_nonce' ); ?>

			<?php foreach ( oasis_photo_slots() as $group_name => $slots ) : ?>
				<h2><?php echo esc_html( $group_name ); ?></h2>
				<div class="oasis-photo-grid">
					<?php foreach ( $slots as $key => $slot ) :
						$id  = oasis_photo_id( $key );
						$src = $id ? wp_get_attachment_image_url( $id, 'medium' ) : '';
						?>
						<div class="oasis-photo-card">
							<p class="oasis-label"><?php echo esc_html( $slot['label'] ); ?></p>
							<?php if ( ! empty( $slot['note'] ) ) : ?>
								<p class="description"><?php echo esc_html( $slot['note'] ); ?></p>
							<?php endif; ?>

							<div class="oasis-media" data-multiple="0">
								<input type="hidden" name="oasis_photo[<?php echo esc_attr( $key ); ?>]"
									class="oasis-media__value" value="<?php echo esc_attr( $id ? $id : '' ); ?>">
								<div class="oasis-media__list">
									<?php if ( $src ) : ?>
										<span class="oasis-media__item" data-id="<?php echo esc_attr( $id ); ?>">
											<img src="<?php echo esc_url( $src ); ?>" alt="">
											<button type="button" class="oasis-media__remove" aria-label="この写真を外す">&times;</button>
										</span>
									<?php endif; ?>
								</div>
								<p>
									<button type="button" class="button oasis-media__pick">写真を選ぶ</button>
									<button type="button" class="button oasis-media__clear">外す</button>
								</p>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endforeach; ?>

			<?php submit_button( '写真を保存' ); ?>
		</form>
	</div>
	<?php
}
