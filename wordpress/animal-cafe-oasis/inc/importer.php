<?php
/**
 * 初期データの取り込み。
 *
 * テーマに同梱している 21種の写真と文章を、
 * メディアライブラリと「どうぶつ」に一括で登録します。
 * 同じどうぶつが既にある場合は飛ばすので、何度押しても増えません。
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** 設定画面の下に出す取り込みパネル。 */
function oasis_importer_panel() {
	$done  = (int) get_option( 'oasis_seed_done', 0 );
	$count = wp_count_posts( 'animal' );
	$have  = isset( $count->publish ) ? (int) $count->publish : 0;
	?>
	<div class="oasis-import">
		<h2>初期データの取り込み</h2>
		<p>
			テーマに同梱している <strong>どうぶつ21種（写真47枚＋店内POPカード21枚）</strong> を、
			メディアライブラリと「どうぶつ」にまとめて登録します。
		</p>
		<p class="description">
			同じどうぶつが既にあるときは飛ばします。何度押してもデータが重複することはありません。<br>
			写真の枚数が多いため、1〜3分ほどかかることがあります。画面が切り替わるまでお待ちください。
		</p>

		<?php if ( $have ) : ?>
			<p><strong>現在の登録数：どうぶつ <?php echo esc_html( $have ); ?>件</strong>
			<?php if ( $done ) : ?>（取り込み済み）<?php endif; ?></p>
		<?php endif; ?>

		<form method="post">
			<?php wp_nonce_field( 'oasis_import', 'oasis_import_nonce' ); ?>
			<p>
				<button type="submit" name="oasis_do_import" value="1" class="button button-primary">
					初期データを取り込む
				</button>
			</p>
		</form>
	</div>
	<?php
}

/** 取り込みの実行。 */
function oasis_maybe_import() {
	if ( empty( $_POST['oasis_do_import'] ) ) {
		return;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( ! isset( $_POST['oasis_import_nonce'] )
		|| ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['oasis_import_nonce'] ) ), 'oasis_import' ) ) {
		return;
	}

	$result = oasis_run_import();
	set_transient( 'oasis_import_result', $result, 60 );

	wp_safe_redirect( admin_url( 'options-general.php?page=oasis-settings&oasis_imported=1' ) );
	exit;
}
add_action( 'load-settings_page_oasis-settings', 'oasis_maybe_import' );

function oasis_import_notice() {
	if ( empty( $_GET['oasis_imported'] ) ) {
		return;
	}
	$r = get_transient( 'oasis_import_result' );
	delete_transient( 'oasis_import_result' );
	if ( ! is_array( $r ) ) {
		return;
	}

	$class = empty( $r['errors'] ) ? 'notice-success' : 'notice-warning';
	echo '<div class="notice ' . esc_attr( $class ) . ' is-dismissible"><p>';
	printf(
		'取り込みが終わりました。追加 %d件／すでにあったもの %d件／画像 %d枚。',
		(int) $r['created'], (int) $r['skipped'], (int) $r['images']
	);
	echo '</p>';
	if ( ! empty( $r['errors'] ) ) {
		echo '<ul style="margin:0 0 10px 18px;list-style:disc">';
		foreach ( array_slice( $r['errors'], 0, 10 ) as $e ) {
			echo '<li>' . esc_html( $e ) . '</li>';
		}
		echo '</ul>';
	}
	echo '</div>';
}
add_action( 'admin_notices', 'oasis_import_notice' );

/**
 * 実際の取り込み処理。
 *
 * @return array created / skipped / images / errors
 */
function oasis_run_import() {
	@set_time_limit( 0 );

	$out  = array( 'created' => 0, 'skipped' => 0, 'images' => 0, 'errors' => array() );
	$file = OASIS_DIR . '/assets/seed/data.json';

	if ( ! file_exists( $file ) ) {
		$out['errors'][] = '初期データ（assets/seed/data.json）が見つかりませんでした。';
		return $out;
	}

	$json = json_decode( (string) file_get_contents( $file ), true );
	if ( ! is_array( $json ) || empty( $json['animals'] ) ) {
		$out['errors'][] = '初期データを読み取れませんでした。';
		return $out;
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	foreach ( $json['animals'] as $a ) {
		$slug = sanitize_title( $a['slug'] );

		$exists = get_posts( array(
			'post_type'      => 'animal',
			'name'           => $slug,
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'fields'         => 'ids',
		) );
		if ( $exists ) {
			$out['skipped']++;
			continue;
		}

		$post_id = wp_insert_post( array(
			'post_type'   => 'animal',
			'post_title'  => $a['name'],
			'post_name'   => $slug,
			'post_status' => 'publish',
			'menu_order'  => $out['created'] + $out['skipped'],
		), true );

		if ( is_wp_error( $post_id ) ) {
			$out['errors'][] = $a['name'] . '：' . $post_id->get_error_message();
			continue;
		}

		// カテゴリ
		if ( ! empty( $a['cat'] ) ) {
			wp_set_object_terms( $post_id, $a['cat'], 'animal_cat', false );
		}

		// 文章
		foreach ( array( 'en', 'en_cat', 'lead', 'blurb', 'origin', 'size', 'life', 'food', 'feature', 'trivia' ) as $k ) {
			if ( isset( $a[ $k ] ) ) {
				update_post_meta( $post_id, '_oasis_' . $k, $a[ $k ] );
			}
		}
		update_post_meta( $post_id, '_oasis_adopt', ! empty( $a['adopt'] ) ? '1' : '0' );

		// 写真
		$subs = array();
		foreach ( (array) $a['photos'] as $i => $fname ) {
			$att = oasis_sideload( $fname, $post_id, $a['name'] );
			if ( is_wp_error( $att ) ) {
				$out['errors'][] = $fname . '：' . $att->get_error_message();
				continue;
			}
			$out['images']++;
			if ( 0 === $i ) {
				set_post_thumbnail( $post_id, $att );   // 1枚目をメイン写真に
			} else {
				$subs[] = $att;
			}
		}
		if ( $subs ) {
			update_post_meta( $post_id, '_oasis_photos', implode( ',', $subs ) );
		}

		// POPカード
		if ( ! empty( $a['pop'] ) ) {
			$pop = oasis_sideload( $a['pop'], $post_id, $a['name'] . ' 店内POPカード' );
			if ( is_wp_error( $pop ) ) {
				$out['errors'][] = $a['pop'] . '：' . $pop->get_error_message();
			} else {
				$out['images']++;
				update_post_meta( $post_id, '_oasis_pop', $pop );
			}
		}

		$out['created']++;
	}

	// 固定ページ（店舗紹介・メニュー・ルール・アクセス・生体販売・お知らせ）
	$out = oasis_import_pages( $out );

	// トップページ用の写真（「設定 → Oasis 写真の差し替え」で入れ替えられます）
	$tops = array(
		'hero'  => 'トップの大きな写真',
		'top-a' => 'トップ 店舗紹介の写真',
		'top-b' => 'トップ 店舗紹介の写真2',
		'top-c' => 'トップ 店舗紹介の写真3',
	);
	foreach ( $tops as $key => $label ) {
		$cur = oasis_photo_id( $key );
		if ( $cur && get_post( $cur ) ) {
			continue;   // すでに設定済み
		}
		$att = oasis_sideload( $key . '.webp', 0, $label );
		if ( ! is_wp_error( $att ) ) {
			oasis_set_photo_id( $key, $att );
			$out['images']++;
		}
	}
	update_option( 'oasis_seed_done', 1 );

	return $out;
}

/**
 * テーマ内の画像をメディアライブラリに登録する。
 *
 * @return int|WP_Error 添付ファイルID
 */
function oasis_sideload( $filename, $post_id, $title ) {
	$src = OASIS_DIR . '/assets/seed/img/' . basename( $filename );
	if ( ! file_exists( $src ) ) {
		return new WP_Error( 'oasis_missing', 'ファイルが見つかりません' );
	}

	$tmp = wp_tempnam( $filename );
	if ( ! $tmp ) {
		return new WP_Error( 'oasis_tmp', '一時ファイルを作れませんでした' );
	}
	if ( ! copy( $src, $tmp ) ) {
		@unlink( $tmp );
		return new WP_Error( 'oasis_copy', 'ファイルをコピーできませんでした' );
	}

	$file = array(
		'name'     => basename( $filename ),
		'tmp_name' => $tmp,
	);

	$att = media_handle_sideload( $file, (int) $post_id, $title );
	if ( is_wp_error( $att ) ) {
		@unlink( $tmp );
		return $att;
	}
	return (int) $att;
}



/**
 * 固定ページをつくる。
 * 本文は静的サイトのデザインをそのまま入れています。
 * すでに同じスラッグのページがあるときは作りません。
 */
function oasis_import_pages( $out ) {
	$dir  = OASIS_DIR . '/assets/seed/pages/';
	$meta = array();
	if ( file_exists( $dir . 'pages.json' ) ) {
		$meta = json_decode( (string) file_get_contents( $dir . 'pages.json' ), true );
	}
	if ( ! is_array( $meta ) ) {
		$meta = array();
	}

	foreach ( $meta as $slug => $m ) {
		if ( get_page_by_path( $slug ) ) {
			continue;
		}
		$file = $dir . $slug . '.html';
		if ( ! file_exists( $file ) ) {
			continue;
		}

		$body = (string) file_get_contents( $file );
		// ブロックエディタで壊れないよう、HTML ブロックとして入れる
		$content = "<!-- wp:html -->\n" . $body . "\n<!-- /wp:html -->";

		$page_id = wp_insert_post( array(
			'post_type'    => 'page',
			'post_title'   => isset( $m['title'] ) ? $m['title'] : $slug,
			'post_name'    => $slug,
			'post_excerpt' => isset( $m['lead'] ) ? $m['lead'] : '',
			'post_content' => $content,
			'post_status'  => 'publish',
		), true );

		if ( is_wp_error( $page_id ) ) {
			$out['errors'][] = $slug . '：' . $page_id->get_error_message();
			continue;
		}

		update_post_meta( $page_id, '_wp_page_template', 'page-templates/template-full.php' );
		if ( ! empty( $m['eyebrow'] ) ) {
			update_post_meta( $page_id, '_oasis_eyebrow', $m['eyebrow'] );
		}
		$out['created']++;
	}

	// お知らせ一覧のページ（中身は home.php が作るので空でよい）
	if ( ! get_page_by_path( 'news' ) ) {
		$news_id = wp_insert_post( array(
			'post_type'   => 'page',
			'post_title'  => 'お知らせ・ブログ',
			'post_name'   => 'news',
			'post_status' => 'publish',
		), true );
		if ( ! is_wp_error( $news_id ) ) {
			update_option( 'page_for_posts', $news_id );
			$out['created']++;
		}
	} else {
		$p = get_page_by_path( 'news' );
		if ( $p && ! get_option( 'page_for_posts' ) ) {
			update_option( 'page_for_posts', $p->ID );
		}
	}

	/*
	 * トップページの設定。
	 * 「ホーム」という固定ページをトップに割り当てますが、
	 * 中身は front-page.php が作るので、このページは空のままで構いません。
	 */
	if ( ! get_option( 'page_on_front' ) ) {
		$home = get_page_by_path( 'home' );
		if ( ! $home ) {
			$home_id = wp_insert_post( array(
				'post_type'   => 'page',
				'post_title'  => 'ホーム',
				'post_name'   => 'home',
				'post_status' => 'publish',
			), true );
			$home = is_wp_error( $home_id ) ? null : get_post( $home_id );
		}
		if ( $home ) {
			update_option( 'page_on_front', $home->ID );
		}
	}
	if ( get_option( 'page_on_front' ) ) {
		update_option( 'show_on_front', 'page' );
	}

	// お知らせのサンプル記事（1件も無いときだけ）
	oasis_import_sample_news();

	// メニューがまだ割り当てられていなければ作る
	oasis_build_menus();

	return $out;
}

/**
 * ヘッダー・スマホ・フッターのメニューを自動でつくる。
 * すでに同じ名前のメニューがあるときは何もしません。
 */
function oasis_build_menus() {
	$sets = array(
		// array( ラベル（日本語）, 英字, リンク先 )
		'primary' => array(
			'name'  => 'ヘッダー',
			'items' => array(
				array( 'ホーム', 'HOME', '__home' ),
				array( '店舗紹介', 'OASIS', 'about' ),
				array( 'どうぶつ紹介', 'ANIMALS', '__animals' ),
				array( 'メニュー・料金', 'PRICE', 'menu' ),
				array( '生体販売', 'SALES', 'sales' ),
				array( 'アクセス', 'ACCESS', 'access' ),
				array( 'お知らせ', 'NEWS', 'news' ),
			),
		),
		'drawer' => array(
			'name'  => 'スマホ',
			'items' => array(
				array( 'ホーム', 'HOME', '__home' ),
				array( '店舗紹介・コンセプト', 'OASIS', 'about' ),
				array( 'どうぶつ紹介', 'ANIMALS', '__animals' ),
				array( 'メニュー・料金', 'PRICE', 'menu' ),
				array( 'ご利用ルール', 'RULES', 'rules' ),
				array( '生体販売・お迎え', 'SALES', 'sales' ),
				array( 'アクセス・駐車場', 'ACCESS', 'access' ),
				array( 'お知らせ・ブログ', 'NEWS', 'news' ),
			),
		),
		'footer' => array(
			'name'  => 'フッター',
			'items' => array(
				array( '店舗紹介・コンセプト', '', 'about' ),
				array( 'どうぶつ紹介', '', '__animals' ),
				array( 'メニュー・料金', '', 'menu' ),
				array( 'ご利用ルール', '', 'rules' ),
				array( '生体販売・お迎えのご相談', '', 'sales' ),
				array( 'アクセス・駐車場', '', 'access' ),
				array( 'お知らせ・ブログ', '', 'news' ),
			),
		),
	);

	$locations = (array) get_theme_mod( 'nav_menu_locations', array() );

	foreach ( $sets as $loc => $set ) {
		if ( ! empty( $locations[ $loc ] ) && wp_get_nav_menu_object( $locations[ $loc ] ) ) {
			continue;
		}
		$menu = wp_get_nav_menu_object( $set['name'] );
		$menu_id = $menu ? (int) $menu->term_id : (int) wp_create_nav_menu( $set['name'] );
		if ( ! $menu_id || is_wp_error( $menu_id ) ) {
			continue;
		}

		if ( ! $menu ) {
			foreach ( $set['items'] as $it ) {
				list( $title, $sub, $target ) = $it;

				if ( '__home' === $target ) {
					$args = array(
						'menu-item-title'  => $title,
						'menu-item-url'    => home_url( '/' ),
						'menu-item-type'   => 'custom',
						'menu-item-status' => 'publish',
					);
				} elseif ( '__animals' === $target ) {
					$args = array(
						'menu-item-title'  => $title,
						'menu-item-url'    => get_post_type_archive_link( 'animal' ),
						'menu-item-type'   => 'custom',
						'menu-item-status' => 'publish',
					);
				} else {
					$page = get_page_by_path( $target );
					if ( ! $page ) {
						continue;
					}
					$args = array(
						'menu-item-title'     => $title,
						'menu-item-object'    => 'page',
						'menu-item-object-id' => $page->ID,
						'menu-item-type'      => 'post_type',
						'menu-item-status'    => 'publish',
					);
				}
				// 英字は専用の欄（外観 → メニュー の「英字」）に入れる
				$item_id = wp_update_nav_menu_item( $menu_id, 0, $args );
				if ( $item_id && ! is_wp_error( $item_id ) && '' !== $sub ) {
					update_post_meta( (int) $item_id, '_oasis_menu_en', $sub );
				}
			}
		}

		$locations[ $loc ] = $menu_id;
	}

	set_theme_mod( 'nav_menu_locations', $locations );
}

/** お知らせのサンプル記事。1件も無いときだけ作ります。 */
function oasis_import_sample_news() {
	if ( (int) wp_count_posts( 'post' )->publish > 0 ) {
		return;
	}
	$samples = array(
		array( '新しい仲間が来ました', 'arrival', '店内で暮らす動物たちに新しい仲間が加わりました。ぜひ会いにいらしてください。' ),
		array( '臨時休業のお知らせ', 'closed', '設備点検のため臨時休業する場合があります。ご来店の際はご注意ください。' ),
		array( 'ゾウガメの餌やり体験について', 'shop', '1回100円でご体験いただけます。動物の体調によりお休みする場合があります。' ),
	);
	foreach ( $samples as $s ) {
		$id = wp_insert_post( array(
			'post_type'    => 'post',
			'post_title'   => $s[0],
			'post_content' => $s[2],
			'post_status'  => 'publish',
		), true );
		if ( ! is_wp_error( $id ) ) {
			$term = get_term_by( 'slug', $s[1], 'category' );
			if ( $term ) {
				wp_set_post_categories( $id, array( (int) $term->term_id ) );
			}
		}
	}
}
