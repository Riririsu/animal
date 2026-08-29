<?php
/**
 * テンプレートから呼ぶ共通の関数。
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** カンマ区切りで保存したIDの一覧を配列で返す。 */
function oasis_get_id_list( $post_id, $key ) {
	$raw = get_post_meta( $post_id, $key, true );
	if ( ! $raw ) {
		return array();
	}
	return array_values( array_filter( array_map( 'absint', explode( ',', $raw ) ) ) );
}

/** どうぶつの入力値を取り出す。 */
function oasis_animal_meta( $post_id, $key ) {
	return get_post_meta( $post_id, '_oasis_' . $key, true );
}

/** このどうぶつのページに「お迎えのご相談」を出すか。 */
function oasis_show_adopt( $post_id ) {
	if ( '1' !== oasis_option( 'adopt_enabled', '1' ) ) {
		return false;                       // サイト全体でオフ
	}
	$raw = get_post_meta( $post_id, '_oasis_adopt', true );
	return ( '' === $raw ) ? true : ( '1' === $raw );
}

/** 電話のリンク（tel:）。 */
function oasis_tel_link() {
	$n = preg_replace( '/[^0-9+]/', '', (string) oasis_option( 'tel_link', '' ) );
	return 'tel:' . $n;
}

/** Instagram のURL。未設定なら空。 */
function oasis_instagram() {
	return trim( (string) oasis_option( 'instagram', '' ) );
}

/**
 * どうぶつのメイン写真。アイキャッチが無ければサブ写真の1枚目を使う。
 * 返り値は添付ファイルID（無ければ 0）。
 */
function oasis_main_image_id( $post_id ) {
	$id = get_post_thumbnail_id( $post_id );
	if ( $id ) {
		return (int) $id;
	}
	$subs = oasis_get_id_list( $post_id, '_oasis_photos' );
	return $subs ? (int) $subs[0] : 0;
}

/**
 * 個別ページのサムネイルに並べる写真。
 * メイン写真を先頭に、そのあとサブ写真。POPカードは含めません。
 */
function oasis_gallery_ids( $post_id ) {
	$ids  = array();
	$main = oasis_main_image_id( $post_id );
	if ( $main ) {
		$ids[] = $main;
	}
	foreach ( oasis_get_id_list( $post_id, '_oasis_photos' ) as $id ) {
		if ( ! in_array( $id, $ids, true ) ) {
			$ids[] = $id;
		}
	}
	return $ids;
}

/**
 * 写真を出す。写真が無いときは、静的版と同じ色つきの枠を出します。
 *
 * @param int    $id    添付ファイルID
 * @param string $size  画像サイズ名
 * @param string $class クラス名
 * @param array  $args  alt / fallback（写真が無いときに出す文字）/ attr（追加の属性）
 */
function oasis_image( $id, $size, $class, $args = array() ) {
	$args = wp_parse_args( $args, array( 'alt' => '', 'fallback' => '', 'attr' => array(), 'lazy' => true ) );

	if ( $id ) {
		$attr = array_merge( array(
			'class'    => $class,
			'alt'      => $args['alt'],
			'decoding' => 'async',
			'loading'  => $args['lazy'] ? 'lazy' : 'eager',
		), $args['attr'] );
		echo wp_get_attachment_image( (int) $id, $size, false, $attr );
		return;
	}

	printf( '<div class="%s">%s</div>', esc_attr( $class ), esc_html( $args['fallback'] ) );
}

/** 矢印つきボタンの中身。 */
function oasis_arrow( $color = '#fff' ) {
	return '<span class="btn__arrow"><svg width="14" height="14" style="color:' . esc_attr( $color ) . '" aria-hidden="true"><use href="#ic-arrow"/></svg></span>';
}

/** 下層ページの見出し。 */
function oasis_page_hero( $crumb, $eyebrow, $title, $lead ) {
	?>
	<section class="page-hero">
		<svg class="deco deco--blob" style="top:-120px;right:-60px;width:420px;height:420px" viewBox="0 0 400 400" aria-hidden="true">
			<ellipse cx="200" cy="200" rx="180" ry="150" fill="#DFEDE9" transform="rotate(14 200 200)"/>
		</svg>
		<svg class="deco deco--sprig" style="bottom:-70px;left:-50px;width:250px;height:250px;--deco-rot:-18deg" aria-hidden="true"><use href="#v2-sprig"/></svg>

		<div class="wrap" style="position:relative;z-index:2">
			<p class="breadcrumb"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">ホーム</a> ／ <?php echo wp_kses_post( $crumb ); ?></p>
			<p class="eyebrow" style="margin-top:22px"><?php echo esc_html( $eyebrow ); ?></p>
			<h1 class="page-hero__title"><?php echo esc_html( $title ); ?></h1>
			<?php if ( $lead ) : ?>
				<p class="lead lead--wide"><?php echo esc_html( $lead ); ?></p>
			<?php endif; ?>
		</div>

		<svg class="wave wave--overlap" viewBox="0 0 1280 66" preserveAspectRatio="none" aria-hidden="true">
			<path d="M0 40C140 4 330 66 520 44 700 24 880 8 1030 30 1150 48 1215 60 1280 36V68H0Z" fill="#FBF7EE"/>
		</svg>
	</section>
	<?php
}

/** 営業時間の1行（JavaScript が曜日と時刻から書き換えます）。 */
function oasis_hours_text() {
	return sprintf( '本日 営業中 %s – %s', oasis_option( 'open', '11:00' ), oasis_option( 'close', '19:00' ) );
}

/**
 * フッター上部の波の色。
 *
 * 波はフッターに置いてあるので、そのすぐ上にあるセクションの背景色と
 * 同じ色で塗らないと、境目に色の違う帯が出てしまいます。
 * どのセクションで終わるかを知っているのは各テンプレートなので、
 * 必要なページだけ get_footer() の前に oasis_set_footer_wave() で色を伝えます。
 * 何も指定しなければ、基本の背景色（クリーム）になります。
 */
function oasis_set_footer_wave( $color ) {
	$GLOBALS['oasis_footer_wave'] = $color;
}

/**
 * 本文（固定ページ）の中身から、フッターの波の色を決める。
 * 初期データの本文は <section> で組まれているので、最後のセクションの
 * 背景色に合わせます。
 */
function oasis_wave_color_for_content( $html ) {
	if ( preg_match_all( '/<section[^>]*class="([^"]*)"/i', $html, $m ) ) {
		$last = end( $m[1] );
		if ( false !== strpos( $last, 'section--green' ) ) {
			return '#2E5E3A';
		}
		if ( false !== strpos( $last, 'section--cream' ) ) {
			return '#F3F0E2';
		}
	}
	return '#FBF7EE';
}

/** フッター上部の波の色を返す。 */
function oasis_footer_wave() {
	return isset( $GLOBALS['oasis_footer_wave'] ) ? $GLOBALS['oasis_footer_wave'] : '#FBF7EE';
}

/** 定休日の表記（例：火曜）。 */
function oasis_closed_label() {
	$names = array( '日', '月', '火', '水', '木', '金', '土' );
	$days  = (array) oasis_option( 'closed_days', array( '2' ) );
	$out   = array();
	foreach ( $days as $d ) {
		if ( isset( $names[ (int) $d ] ) ) {
			$out[] = $names[ (int) $d ] . '曜';
		}
	}
	return $out ? implode( '・', $out ) : '';
}

/* ------------------------------------------------------------------ *
 *  メニュー
 * ------------------------------------------------------------------ */

/** ヘッダーのメニュー（未設定のときに出る既定の並び）。 */
function oasis_fallback_nav() {
	$items = array(
		array( home_url( '/' ),                  'HOME',    'ホーム' ),
		array( get_permalink_by_slug( 'about' ), 'OASIS',   '店舗紹介' ),
		array( get_post_type_archive_link( 'animal' ), 'ANIMALS', 'どうぶつ紹介' ),
		array( get_permalink_by_slug( 'menu' ),  'PRICE',   'メニュー・料金' ),
		array( get_permalink_by_slug( 'access' ),'ACCESS',  'アクセス' ),
		array( get_permalink_by_slug( 'news' ),  'NEWS',    'お知らせ' ),
	);
	foreach ( $items as $it ) {
		if ( ! $it[0] ) {
			continue;
		}
		printf(
			'<a class="gnav__link" href="%s"%s>%s<span class="gnav__ja">%s</span></a>',
			esc_url( $it[0] ),
			oasis_nav_is_current( $it[0] ) ? ' aria-current="page"' : '',
			esc_html( $it[1] ), esc_html( $it[2] )
		);
	}
}

/** スマホメニュー（未設定のときに出る既定の並び）。 */
function oasis_fallback_drawer() {
	$items = array(
		array( home_url( '/' ),                        'ホーム',             'HOME' ),
		array( get_permalink_by_slug( 'about' ),       '店舗紹介・コンセプト', 'OASIS' ),
		array( get_post_type_archive_link( 'animal' ), 'どうぶつ紹介',        'ANIMALS' ),
		array( get_permalink_by_slug( 'menu' ),        'メニュー・料金',      'PRICE' ),
		array( get_permalink_by_slug( 'rules' ),       'ご利用ルール',        'RULES' ),
		array( get_permalink_by_slug( 'access' ),      'アクセス・駐車場',    'ACCESS' ),
		array( get_permalink_by_slug( 'news' ),        'お知らせ・ブログ',    'NEWS' ),
	);
	foreach ( $items as $it ) {
		if ( ! $it[0] ) {
			continue;
		}
		printf(
			'<a class="drawer__link" href="%s"%s>%s<span class="drawer__en">%s</span></a>',
			esc_url( $it[0] ),
			oasis_nav_is_current( $it[0] ) ? ' aria-current="page"' : '',
			esc_html( $it[1] ), esc_html( $it[2] )
		);
	}
}

/* ------------------------------------------------------------------ *
 *  メニューの「現在地」判定
 * ------------------------------------------------------------------ */

/**
 * URL を比較用の文字列にそろえる（パスだけを見る）。
 */
function oasis_nav_url_key( $url ) {
	$path = wp_parse_url( (string) $url, PHP_URL_PATH );
	if ( ! $path ) {
		$path = '/';
	}
	$key = untrailingslashit( $path );
	// トップページは '/' → '' になってしまうので、'/' のまま返す
	return '' === $key ? '/' : $key;
}

/**
 * いま見ているページが、メニューのどの項目にあたるかを返す。
 *
 * WordPress が付ける current_page_parent は、固定ページ以外を見ているときに
 * 「投稿ページ」（このサイトではお知らせ）へ必ず付いてしまいます。
 * そのため、どうぶつ紹介を見ているのにお知らせにも下線が出ていました。
 * ここでは見ているページの種類から現在地を自分で決めています。
 *
 * @return string 比較用の文字列。判定できないときは空文字。
 */
function oasis_nav_current_key() {
	// どうぶつ紹介：一覧・個別・カテゴリのどれでも「どうぶつ紹介」を現在地にする
	if ( is_post_type_archive( 'animal' ) || is_singular( 'animal' ) || is_tax( 'animal_cat' ) ) {
		return oasis_nav_url_key( get_post_type_archive_link( 'animal' ) );
	}

	// お知らせ：一覧・個別・カテゴリ・日付などで「お知らせ」を現在地にする
	if ( is_home() || is_singular( 'post' ) || is_category() || is_tag() || is_date() || is_author() ) {
		$posts_page = (int) get_option( 'page_for_posts' );
		return oasis_nav_url_key( $posts_page ? get_permalink( $posts_page ) : home_url( '/' ) );
	}

	if ( is_front_page() ) {
		return oasis_nav_url_key( home_url( '/' ) );
	}

	if ( is_page() ) {
		return oasis_nav_url_key( get_permalink() );
	}

	return '';
}

/** このリンク先が現在地かどうか。 */
function oasis_nav_is_current( $url ) {
	$current = oasis_nav_current_key();
	return '' !== $current && oasis_nav_url_key( $url ) === $current;
}

/** スラッグから固定ページのURLを取る。無ければ空文字。 */
function get_permalink_by_slug( $slug ) {
	$page = get_page_by_path( $slug );
	return $page ? get_permalink( $page->ID ) : '';
}

/** ヘッダーのメニュー用ウォーカー（英字＋日本語の2段表示）。 */
class Oasis_Nav_Walker extends Walker_Nav_Menu {
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		// メニュー名を英字（HOME など）、「リンク先のタイトル属性」を日本語として使います
		$en      = $item->title;
		$ja      = $item->attr_title;
		$current = oasis_nav_is_current( $item->url );

		$output .= sprintf(
			'<a class="gnav__link" href="%s"%s>%s%s</a>',
			esc_url( $item->url ),
			$current ? ' aria-current="page"' : '',
			esc_html( $en ),
			$ja ? '<span class="gnav__ja">' . esc_html( $ja ) . '</span>' : ''
		);
	}
	public function end_el( &$output, $item, $depth = 0, $args = null ) {}
}

/** スマホメニュー用ウォーカー。 */
class Oasis_Drawer_Walker extends Walker_Nav_Menu {
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$en = $item->attr_title;
		$output .= sprintf(
			'<a class="drawer__link" href="%s"%s>%s%s</a>',
			esc_url( $item->url ),
			oasis_nav_is_current( $item->url ) ? ' aria-current="page"' : '',
			esc_html( $item->title ),
			$en ? '<span class="drawer__en">' . esc_html( $en ) . '</span>' : ''
		);
	}
	public function end_el( &$output, $item, $depth = 0, $args = null ) {}
}

/** フッターのリンク。'__animals' はどうぶつ一覧を指します。 */
function oasis_footer_links( $items ) {
	foreach ( $items as $slug => $label ) {
		$url = ( '__animals' === $slug ) ? get_post_type_archive_link( 'animal' ) : get_permalink_by_slug( $slug );
		if ( ! $url ) {
			continue;
		}
		printf( '<a href="%s">%s</a>', esc_url( $url ), esc_html( $label ) );
	}
}

/** フッターメニュー用ウォーカー。 */
class Oasis_Footer_Walker extends Walker_Nav_Menu {
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$output .= sprintf( '<a href="%s">%s</a>', esc_url( $item->url ), esc_html( $item->title ) );
	}
	public function end_el( &$output, $item, $depth = 0, $args = null ) {}
}

/** ページ送り。 */
function oasis_pagination() {
	$links = paginate_links( array(
		'type'      => 'array',
		'prev_text' => '← 前へ',
		'next_text' => '次へ →',
	) );
	if ( ! $links ) {
		return;
	}
	echo '<nav class="pager" style="margin-top:40px" aria-label="ページ送り" data-reveal="up">';
	foreach ( $links as $link ) {
		$link = str_replace( 'page-numbers current', 'pager__item" aria-current="page', $link );
		$link = str_replace( 'page-numbers', 'pager__item', $link );
		echo wp_kses_post( $link );
	}
	echo '</nav>';
}

/* ------------------------------------------------------------------ *
 *  静的サイトから取り込んだ本文のリンク・画像を、WordPress のものに直す
 * ------------------------------------------------------------------ */

/**
 * Googleマップの埋め込みコードを、レスポンシブな枠に入れて返す。
 *
 * 管理画面に貼りつける <iframe> は width="600" height="450" のような
 * 固定サイズを持っている。そのまま出すとスマホで画面からはみ出して
 * レイアウトが崩れるので、幅・高さの指定を外して .map-embed で包む。
 *
 * @param string $attrs 枠に足す属性（例: ' data-reveal="left"'）。
 * @return string 未設定なら空文字。
 */
function oasis_map_embed( $attrs = '' ) {
	$embed = trim( (string) oasis_option( 'map_embed', '' ) );
	if ( '' === $embed ) {
		return '';
	}

	// iframe の固定 width / height と、幅を決めてしまう style を取り除く
	$embed = preg_replace_callback(
		'/<iframe\b[^>]*>/i',
		function ( $m ) {
			$tag = preg_replace( '/\s(?:width|height)\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $m[0] );
			$tag = preg_replace_callback(
				'/\sstyle\s*=\s*(["\'])(.*?)\1/is',
				function ( $sm ) {
					$style = preg_replace( '/(?:^|;)\s*(?:width|height|min-width|max-width)\s*:[^;]*/i', '', $sm[2] );
					$style = trim( $style, " \t;" );
					return '' === $style ? '' : ' style="' . esc_attr( $style ) . '"';
				},
				$tag
			);
			return $tag;
		},
		$embed
	);

	return '<div class="map-embed"' . $attrs . '>' . $embed . '</div>';
}

/**
 * 取り込んだ本文には index.html / animals.html / images/... といった
 * 静的サイトのままの書き方が残っています。表示するときに置き換えます。
 * 本文を書き換えるわけではないので、あとから元に戻すこともできます。
 */
function oasis_fix_legacy_links( $html ) {
	// 差し替える対象がひとつも入っていない本文は、そのまま返して処理を省く。
	// ここに載せ忘れると「設定を変えても本文が変わらない」不具合になるので、
	// 下で str_replace / preg_replace している目印は必ず全部並べておくこと。
	$needles = array(
		'.html',
		'images/',
		'#instagram',
		'tel:0000000000',
		OASIS_PH_TEL,
		OASIS_PH_ADDRESS,
		OASIS_PH_HOURS,
		OASIS_PH_CLOSED,
		OASIS_PH_LICENSE,
		'Googleマップ',
	);
	$found = false;
	foreach ( $needles as $needle ) {
		if ( false !== strpos( $html, $needle ) ) {
			$found = true;
			break;
		}
	}
	if ( ! $found ) {
		return $html;
	}

	$map = array(
		'index.html'   => home_url( '/' ),
		'animals.html' => get_post_type_archive_link( 'animal' ),
	);
	foreach ( array( 'about', 'menu', 'rules', 'access', 'news' ) as $slug ) {
		$url = get_permalink_by_slug( $slug );
		if ( $url ) {
			$map[ $slug . '.html' ] = $url;
		}
	}

	// href="xxx.html" / href="xxx.html#anchor"
	$html = preg_replace_callback(
		'/(href=")([a-z0-9\-]+\.html)(#[^"]*)?(")/i',
		function ( $m ) use ( $map ) {
			$file = strtolower( $m[2] );

			if ( isset( $map[ $file ] ) ) {
				return $m[1] . esc_url( $map[ $file ] ) . ( isset( $m[3] ) ? $m[3] : '' ) . $m[4];
			}
			// animal-<スラッグ>.html は、そのどうぶつのページへ
			if ( preg_match( '/^animal-([a-z0-9\-]+)\.html$/', $file, $mm ) ) {
				$posts = get_posts( array(
					'post_type'      => 'animal',
					'name'           => $mm[1],
					'posts_per_page' => 1,
					'fields'         => 'ids',
				) );
				if ( $posts ) {
					return $m[1] . esc_url( get_permalink( $posts[0] ) ) . ( isset( $m[3] ) ? $m[3] : '' ) . $m[4];
				}
			}
			return $m[0];
		},
		$html
	);

	// 画像・地図の SVG はテーマの中を指す
	$html = str_replace( array( 'src="images/', "src='images/" ), array( 'src="' . OASIS_URI . '/assets/images/', "src='" . OASIS_URI . '/assets/images/' ), $html );

	// 電話・Instagram は設定の値に
	$html = str_replace( 'tel:0000000000', oasis_tel_link(), $html );
	$disp = (string) oasis_option( 'tel_display', '' );
	if ( '' !== $disp ) {
		$html = str_replace( OASIS_PH_TEL, esc_html( $disp ), $html );
	}
	$addr = (string) oasis_option( 'address', '' );
	if ( '' !== $addr ) {
		$html = str_replace( OASIS_PH_ADDRESS, esc_html( $addr ), $html );
	}

	// 営業時間「11:00 – 19:00」／定休日「火曜」／第一種動物取扱業の表記
	$open  = (string) oasis_option( 'open', '11:00' );
	$close = (string) oasis_option( 'close', '19:00' );
	if ( '' !== $open && '' !== $close ) {
		$html = str_replace( OASIS_PH_HOURS, esc_html( $open . ' – ' . $close ), $html );
	}
	$off = oasis_closed_label();
	if ( '' !== $off ) {
		// 「11:00 – 19:00／火曜定休」の形はすでに時刻が置換済みなので、末尾だけ直す
		$html = str_replace( OASIS_PH_CLOSED . '定休', esc_html( $off ) . '定休', $html );
		// アクセスページの「定休日：火曜」のように単独で置いてある場合
		$html = str_replace(
			'<span class="row__value">' . OASIS_PH_CLOSED . '</span>',
			'<span class="row__value">' . esc_html( $off ) . '</span>',
			$html
		);
	}
	$license = (string) oasis_option( 'license', '' );
	if ( '' !== $license ) {
		$html = str_replace( OASIS_PH_LICENSE, wp_kses( $license, array( 'br' => array() ) ), $html );
	}

	$ig = oasis_instagram();
	if ( $ig ) {
		$html = str_replace( 'href="#instagram"', 'href="' . esc_url( $ig ) . '" target="_blank" rel="noopener"', $html );
	}

	// Googleマップの埋め込みが設定されていれば置き換える
	$embed = oasis_map_embed( ' data-reveal="left"' );
	if ( '' !== $embed ) {
		$html = preg_replace(
			'/<div class="photo photo--water photo--h-xl"[^>]*>Googleマップ<\/div>/u',
			// 置換文字列の $ や \ をそのまま出すためエスケープする
			str_replace( array( '\\', '$' ), array( '\\\\', '\\$' ), $embed ),
			$html
		);
	}

	return $html;
}
add_filter( 'the_content', 'oasis_fix_legacy_links', 20 );
