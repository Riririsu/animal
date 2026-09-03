<?php
/**
 * メニュー・料金の中身。
 *
 * これまではページの本文にHTMLで書いていたため、値段ひとつ変えるにも
 * HTMLを触る必要がありました。ここでは「設定 → Oasis メニュー」の
 * フォームから、品名・値段・区分を追加／並べ替え／削除できるようにします。
 *
 * ページ側には [oasis_menu] と書いておくと、この内容が並びます。
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** 何も設定されていないときの中身（店内メニュー表のとおり）。 */
function oasis_menu_defaults() {
	return array(
		'admission' => array(
			'label' => '入場料（1時間・2ドリンク付）',
			'price' => '1,000円',
			'note'  => 'お食事のみ・生体のお買い上げのみの方は入場料は頂きません。',
		),
		'experience' => array(
			'title' => '体験メニュー',
			'name'  => 'ゾウガメの餌やり体験',
			'price' => '1回 200円',
			'note'  => '動物の体調により、お休みする場合があります。当日スタッフにお尋ねください。',
		),
		'groups' => array(
			array(
				'title' => 'ドリンク',
				'items' => array(
					array( 'name' => 'オレンジ',                 'price' => '400円', 'note' => '' ),
					array( 'name' => 'アップル',                 'price' => '400円', 'note' => '' ),
					array( 'name' => 'コカコーラ',               'price' => '400円', 'note' => '' ),
					array( 'name' => 'ファンタグレープ',         'price' => '400円', 'note' => '' ),
					array( 'name' => '烏龍茶',                   'price' => '400円', 'note' => '' ),
					array( 'name' => 'カルピス',                 'price' => '400円', 'note' => '' ),
					array( 'name' => 'アイスコーヒー',           'price' => '400円', 'note' => '' ),
					array( 'name' => 'ホットコーヒー',           'price' => '400円', 'note' => '' ),
					array( 'name' => '瓶ビール（スーパードライ）', 'price' => '700円', 'note' => '' ),
				),
			),
			array(
				'title' => 'ご飯物',
				'items' => array(
					array( 'name' => 'オムライス', 'price' => '900円', 'note' => '' ),
					array( 'name' => 'エビピラフ', 'price' => '700円', 'note' => '' ),
					array( 'name' => 'カレー中辛', 'price' => '850円', 'note' => '' ),
				),
			),
			array(
				'title' => 'パスタ',
				'items' => array(
					array( 'name' => 'もっちり麺の焼きナポリタン', 'price' => '700円', 'note' => '' ),
					array( 'name' => 'カルボナーラ',               'price' => '700円', 'note' => '' ),
					array( 'name' => '和風タラコスパゲッティ',     'price' => '700円', 'note' => '' ),
				),
			),
			array(
				'title' => 'サイドメニュー',
				'items' => array(
					array( 'name' => '唐揚げ',     'price' => '500円', 'note' => '' ),
					array( 'name' => 'ホットドッグ', 'price' => '500円', 'note' => '' ),
					array( 'name' => 'ポテト',     'price' => '400円', 'note' => 'クリックルカット' ),
				),
			),
			array(
				'title' => 'ケーキ',
				'items' => array(
					array( 'name' => 'レモン香るレアチーズケーキ',   'price' => '500円', 'note' => '' ),
					array( 'name' => 'さつま芋と和三盆のモンブラン', 'price' => '500円', 'note' => '' ),
					array( 'name' => '抹茶ときな粉のケーキ',         'price' => '450円', 'note' => '' ),
				),
			),
		),
		'foot_note' => '価格は税込です。店内メニュー表と同じ内容を掲載しています。',
	);
}

/** 保存されているメニュー。無ければ初期値。 */
function oasis_menu_data() {
	$defaults = oasis_menu_defaults();
	$saved    = get_option( 'oasis_menu' );
	if ( ! is_array( $saved ) || empty( $saved['groups'] ) ) {
		return $defaults;
	}

	$d = wp_parse_args( $saved, $defaults );

	// 古い保存内容に足りない項目があっても落ちないようにそろえる
	$d['admission']  = wp_parse_args( (array) $d['admission'], $defaults['admission'] );
	$d['experience'] = wp_parse_args( (array) $d['experience'], $defaults['experience'] );

	$groups = array();
	foreach ( (array) $d['groups'] as $g ) {
		$items = array();
		foreach ( (array) ( $g['items'] ?? array() ) as $it ) {
			$items[] = wp_parse_args( (array) $it, array( 'name' => '', 'price' => '', 'note' => '' ) );
		}
		$groups[] = array( 'title' => (string) ( $g['title'] ?? '' ), 'items' => $items );
	}
	$d['groups']    = $groups;
	$d['foot_note'] = (string) ( $d['foot_note'] ?? '' );

	return $d;
}

/* ------------------------------------------------------------------ *
 *  表示（ショートコード [oasis_menu]）
 * ------------------------------------------------------------------ */
function oasis_menu_shortcode() {
	$d = oasis_menu_data();

	$out = '<div class="grid grid--2" data-reveal-stagger="up">';

	// 入場料
	$out .= '<div class="panel panel--green">'
		. '<p class="eyebrow" style="letter-spacing:.26em">ADMISSION</p>'
		. '<p style="margin-top:14px;font-family:var(--f-serif);font-size:var(--fs-panel-t);font-weight:600;line-height:1.5">'
		. esc_html( $d['admission']['label'] ) . '</p>'
		. '<p class="price-hero">' . oasis_menu_price( $d['admission']['price'] ) . '</p>'
		. ( '' !== $d['admission']['note']
			? '<p class="note" style="margin-top:14px;color:var(--c-on-green)">' . esc_html( $d['admission']['note'] ) . '</p>'
			: '' )
		. '</div>';

	// 体験メニュー
	$out .= '<div class="panel panel--gold">'
		. '<p class="eyebrow eyebrow--gold" style="letter-spacing:.26em">EXPERIENCE</p>'
		. '<h2 class="panel__title" style="margin:14px 0 8px">' . esc_html( $d['experience']['title'] ) . '</h2>'
		. '<div class="row" style="border-bottom:0;padding:12px 0"><span>' . esc_html( $d['experience']['name'] )
		. '</span><span class="row__value">' . esc_html( $d['experience']['price'] ) . '</span></div>'
		. ( '' !== $d['experience']['note']
			? '<p class="note" style="margin-top:8px;color:var(--c-text)">' . esc_html( $d['experience']['note'] ) . '</p>'
			: '' )
		. '</div>';

	$out .= '</div>';

	// 区分ごとの一覧（品目数がばらばらなので、段組みで詰めて並べる）
	$out .= '<div class="menu-lists" data-reveal-stagger="up">';
	foreach ( $d['groups'] as $group ) {
		if ( empty( $group['items'] ) ) {
			continue;
		}
		$out .= '<div>'
			. '<h2 class="section-title section-title--sm" style="margin:0 0 16px">' . esc_html( $group['title'] ) . '</h2>'
			. '<div class="rows">';
		foreach ( $group['items'] as $item ) {
			if ( '' === trim( $item['name'] ) ) {
				continue;
			}
			$name = esc_html( $item['name'] );
			if ( '' !== trim( (string) $item['note'] ) ) {
				$name .= '<span class="row__note">（' . esc_html( $item['note'] ) . '）</span>';
			}
			$out .= '<div class="row"><span>' . $name . '</span>'
				. '<span class="row__value">' . esc_html( $item['price'] ) . '</span></div>';
		}
		$out .= '</div></div>';
	}
	$out .= oasis_photo( 'menu-photo', array( 'alt' => 'ドリンク・ケーキ', 'fallback' => 'ドリンク・ケーキ写真' ) );
	$out .= '</div>';

	if ( '' !== trim( (string) $d['foot_note'] ) ) {
		$out .= '<p class="note" style="margin-top:44px">' . esc_html( $d['foot_note'] ) . '</p>';
	}

	return $out;
}
add_shortcode( 'oasis_menu', 'oasis_menu_shortcode' );

/** 「1,000円」を、数字を大きく・単位を小さく表示する形にする。 */
function oasis_menu_price( $price ) {
	$price = trim( (string) $price );
	if ( preg_match( '/^([0-9,\.]+)\s*(.*)$/u', $price, $m ) ) {
		return esc_html( $m[1] ) . ( '' !== $m[2] ? '<small>' . esc_html( $m[2] ) . '</small>' : '' );
	}
	return esc_html( $price );
}

/* ------------------------------------------------------------------ *
 *  管理画面
 * ------------------------------------------------------------------ */

function oasis_menu_admin_menu() {
	add_options_page( 'Oasis メニュー', 'Oasis メニュー', 'manage_options', 'oasis-menu', 'oasis_menu_page' );
}
add_action( 'admin_menu', 'oasis_menu_admin_menu' );

/** 送られてきた内容を整えて保存する形にする。 */
function oasis_menu_sanitize( $raw ) {
	$out = array(
		'admission' => array(
			'label' => sanitize_text_field( $raw['admission']['label'] ?? '' ),
			'price' => sanitize_text_field( $raw['admission']['price'] ?? '' ),
			'note'  => sanitize_textarea_field( $raw['admission']['note'] ?? '' ),
		),
		'experience' => array(
			'title' => sanitize_text_field( $raw['experience']['title'] ?? '' ),
			'name'  => sanitize_text_field( $raw['experience']['name'] ?? '' ),
			'price' => sanitize_text_field( $raw['experience']['price'] ?? '' ),
			'note'  => sanitize_textarea_field( $raw['experience']['note'] ?? '' ),
		),
		'groups'    => array(),
		'foot_note' => sanitize_textarea_field( $raw['foot_note'] ?? '' ),
	);

	foreach ( (array) ( $raw['groups'] ?? array() ) as $g ) {
		$title = sanitize_text_field( $g['title'] ?? '' );
		$items = array();
		foreach ( (array) ( $g['items'] ?? array() ) as $it ) {
			$name = sanitize_text_field( $it['name'] ?? '' );
			if ( '' === trim( $name ) ) {
				continue;                       // 品名が空の行は捨てる
			}
			$items[] = array(
				'name'  => $name,
				'price' => sanitize_text_field( $it['price'] ?? '' ),
				'note'  => sanitize_text_field( $it['note'] ?? '' ),
			);
		}
		if ( '' === trim( $title ) && ! $items ) {
			continue;                           // 空の区分は捨てる
		}
		$out['groups'][] = array( 'title' => $title, 'items' => $items );
	}

	return $out;
}

function oasis_menu_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( isset( $_POST['oasis_menu_nonce'] )
		&& wp_verify_nonce( sanitize_key( wp_unslash( $_POST['oasis_menu_nonce'] ) ), 'oasis_save_menu' ) ) {

		if ( isset( $_POST['oasis_menu_reset'] ) ) {
			delete_option( 'oasis_menu' );
			echo '<div class="notice notice-success is-dismissible"><p>最初の状態に戻しました。</p></div>';
		} else {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- oasis_menu_sanitize で整えています
			$raw = isset( $_POST['oasis_menu'] ) ? (array) wp_unslash( $_POST['oasis_menu'] ) : array();
			update_option( 'oasis_menu', oasis_menu_sanitize( $raw ) );
			echo '<div class="notice notice-success is-dismissible"><p>メニューを保存しました。</p></div>';
		}
	}

	$d = oasis_menu_data();
	?>
	<div class="wrap oasis-settings oasis-menu-edit">
		<h1>Oasis メニュー</h1>
		<p>
			<strong>メニュー・料金ページの中身</strong>です。品名と値段をここで直すと、サイトに反映されます。<br>
			値段は文字なので、<code>400円</code> のほか <code>時価</code> <code>¥400</code> のようにも書けます。
		</p>

		<form method="post" id="oasis-menu-form">
			<?php wp_nonce_field( 'oasis_save_menu', 'oasis_menu_nonce' ); ?>

			<h2>入場料</h2>
			<table class="form-table oasis-table"><tbody>
				<tr><th scope="row">見出し</th><td>
					<input type="text" class="large-text" name="oasis_menu[admission][label]"
						value="<?php echo esc_attr( $d['admission']['label'] ); ?>"></td></tr>
				<tr><th scope="row">金額</th><td>
					<input type="text" class="regular-text" name="oasis_menu[admission][price]"
						value="<?php echo esc_attr( $d['admission']['price'] ); ?>">
					<p class="description">数字のあとの「円」は小さく表示されます。</p></td></tr>
				<tr><th scope="row">注意書き</th><td>
					<textarea class="large-text" rows="2" name="oasis_menu[admission][note]"><?php echo esc_textarea( $d['admission']['note'] ); ?></textarea></td></tr>
			</tbody></table>

			<h2>体験メニュー</h2>
			<table class="form-table oasis-table"><tbody>
				<tr><th scope="row">見出し</th><td>
					<input type="text" class="large-text" name="oasis_menu[experience][title]"
						value="<?php echo esc_attr( $d['experience']['title'] ); ?>"></td></tr>
				<tr><th scope="row">内容</th><td>
					<input type="text" class="regular-text" name="oasis_menu[experience][name]"
						value="<?php echo esc_attr( $d['experience']['name'] ); ?>"></td></tr>
				<tr><th scope="row">金額</th><td>
					<input type="text" class="regular-text" name="oasis_menu[experience][price]"
						value="<?php echo esc_attr( $d['experience']['price'] ); ?>"></td></tr>
				<tr><th scope="row">注意書き</th><td>
					<textarea class="large-text" rows="2" name="oasis_menu[experience][note]"><?php echo esc_textarea( $d['experience']['note'] ); ?></textarea></td></tr>
			</tbody></table>

			<h2>ドリンク・お食事</h2>
			<p class="description">
				区分（ドリンク・ご飯物など）ごとに品目を並べます。<br>
				<strong>行の追加・削除・並べ替え</strong>は、それぞれのボタンでできます。
			</p>

			<div id="oasis-menu-groups">
				<?php foreach ( $d['groups'] as $gi => $group ) : ?>
					<?php oasis_menu_group_html( $gi, $group ); ?>
				<?php endforeach; ?>
			</div>

			<p><button type="button" class="button" id="oasis-menu-add-group">＋ 区分を追加</button></p>

			<h2>ページの最後に出す文</h2>
			<textarea class="large-text" rows="2" name="oasis_menu[foot_note]"><?php echo esc_textarea( $d['foot_note'] ); ?></textarea>

			<p class="submit">
				<button type="submit" class="button button-primary button-large">変更を保存</button>
				<button type="submit" name="oasis_menu_reset" value="1" class="button"
					onclick="return confirm('入力した内容を捨てて、最初の状態に戻します。よろしいですか？');">最初の状態に戻す</button>
			</p>
		</form>
	</div>

	<script type="text/template" id="oasis-menu-group-tpl">
		<?php oasis_menu_group_html( '__GI__', array( 'title' => '', 'items' => array() ) ); ?>
	</script>
	<script type="text/template" id="oasis-menu-row-tpl">
		<?php oasis_menu_row_html( '__GI__', '__RI__', array( 'name' => '', 'price' => '', 'note' => '' ) ); ?>
	</script>
	<?php
}

/** 区分ひとかたまりの入力欄。 */
function oasis_menu_group_html( $gi, $group ) {
	?>
	<div class="oasis-menu-group" data-gi="<?php echo esc_attr( $gi ); ?>">
		<div class="oasis-menu-group__head">
			<label>
				<span class="oasis-label">区分の名前</span>
				<input type="text" class="regular-text" name="oasis_menu[groups][<?php echo esc_attr( $gi ); ?>][title]"
					value="<?php echo esc_attr( $group['title'] ); ?>" placeholder="ドリンク">
			</label>
			<span class="oasis-menu-group__tools">
				<button type="button" class="button oasis-menu-move" data-dir="up" title="上へ">▲</button>
				<button type="button" class="button oasis-menu-move" data-dir="down" title="下へ">▼</button>
				<button type="button" class="button oasis-menu-del-group">この区分を削除</button>
			</span>
		</div>

		<table class="widefat striped oasis-menu-rows">
			<thead><tr>
				<th>品名</th><th style="width:140px">金額</th><th style="width:180px">補足（任意）</th><th style="width:150px"></th>
			</tr></thead>
			<tbody>
				<?php foreach ( $group['items'] as $ri => $item ) : ?>
					<?php oasis_menu_row_html( $gi, $ri, $item ); ?>
				<?php endforeach; ?>
			</tbody>
		</table>
		<p><button type="button" class="button oasis-menu-add-row">＋ 品目を追加</button></p>
	</div>
	<?php
}

/** 品目1行の入力欄。 */
function oasis_menu_row_html( $gi, $ri, $item ) {
	$base = 'oasis_menu[groups][' . $gi . '][items][' . $ri . ']';
	?>
	<tr class="oasis-menu-row">
		<td><input type="text" class="large-text" name="<?php echo esc_attr( $base ); ?>[name]"
			value="<?php echo esc_attr( $item['name'] ); ?>" placeholder="オレンジ"></td>
		<td><input type="text" class="regular-text" name="<?php echo esc_attr( $base ); ?>[price]"
			value="<?php echo esc_attr( $item['price'] ); ?>" placeholder="400円"></td>
		<td><input type="text" class="regular-text" name="<?php echo esc_attr( $base ); ?>[note]"
			value="<?php echo esc_attr( $item['note'] ); ?>" placeholder="クリックルカット"></td>
		<td class="oasis-menu-row__tools">
			<button type="button" class="button oasis-menu-move-row" data-dir="up" title="上へ">▲</button>
			<button type="button" class="button oasis-menu-move-row" data-dir="down" title="下へ">▼</button>
			<button type="button" class="button oasis-menu-del-row" title="この行を削除">削除</button>
		</td>
	</tr>
	<?php
}

/* ------------------------------------------------------------------ *
 *  すでにあるメニューページを、フォーム編集できる形に直す（1回だけ）
 *
 *  初期データの取り込みは「ページがまだ無いとき」しか作らないので、
 *  以前のテーマで作られたページは、品目がHTMLで直接書かれたままです。
 *  そのままだと管理画面から直せないため、[oasis_menu] に置き換えます。
 *
 *  ・すでに [oasis_menu] があるページには触りません
 *  ・置き換える前の品目と値段は、そのまま管理画面の初期値として取り込みます
 *  ・WordPress のリビジョンが残るので、元に戻すこともできます
 * ------------------------------------------------------------------ */
function oasis_menu_upgrade_page() {
	if ( get_option( 'oasis_menu_page_done' ) ) {
		return;
	}

	$page = get_page_by_path( 'menu' );
	if ( ! $page ) {
		update_option( 'oasis_menu_page_done', 1 );
		return;
	}

	$content = (string) $page->post_content;

	if ( false !== strpos( $content, '[oasis_menu]' ) ) {
		update_option( 'oasis_menu_page_done', 1 );
		return;
	}

	// いま載っている品目を読み取って、管理画面の初期値にする
	$picked = oasis_menu_read_from_html( $content );
	if ( $picked && ! get_option( 'oasis_menu' ) ) {
		update_option( 'oasis_menu', $picked );
	}

	$new = '<!-- wp:html -->' . "\n"
		. '<section class="section" style="padding-top:48px">' . "\n"
		. '    <div class="wrap">' . "\n"
		. '      [oasis_menu]' . "\n"
		. '    </div>' . "\n"
		. '  </section>' . "\n"
		. '<!-- /wp:html -->';

	wp_update_post( array( 'ID' => $page->ID, 'post_content' => $new ) );
	update_option( 'oasis_menu_page_done', 1 );
}
add_action( 'admin_init', 'oasis_menu_upgrade_page' );

/**
 * 古い形のメニューページのHTMLから、区分と品目を読み取る。
 * 読み取れないときは空配列を返す（そのときは初期値が使われます）。
 */
function oasis_menu_read_from_html( $html ) {
	$data = oasis_menu_defaults();

	// 入場料
	if ( preg_match( '/<p class="price-hero">(.*?)<\/p>/us', $html, $m ) ) {
		$data['admission']['price'] = trim( wp_strip_all_tags( $m[1] ) );
	}

	/*
	 * 区分ごとの見出しと行。
	 * 見出し（<h2 class="section-title--sm">）で区切って、
	 * そのかたまりの中にある <div class="row"> を拾います。
	 * 入れ子の </div> を数える必要がないので、書き方が多少違っても読み取れます。
	 */
	$groups = array();
	$parts  = preg_split(
		'/<h2 class="section-title section-title--sm"[^>]*>(.*?)<\/h2>/us',
		$html, -1, PREG_SPLIT_DELIM_CAPTURE
	);
	// $parts = ( 見出しの前, 見出し1, 中身1, 見出し2, 中身2, … )
	for ( $i = 1; $i + 1 < count( $parts ) + 1 && isset( $parts[ $i + 1 ] ); $i += 2 ) {
		$title = trim( wp_strip_all_tags( $parts[ $i ] ) );
		$body  = $parts[ $i + 1 ];
		$items = array();
		if ( preg_match_all(
			'/<div class="row"[^>]*><span>(.*?)<\/span><span class="row__value">(.*?)<\/span><\/div>/us',
			$body, $rows, PREG_SET_ORDER
		) ) {
			foreach ( $rows as $r ) {
				$items[] = array(
					'name'  => trim( wp_strip_all_tags( $r[1] ) ),
					'price' => trim( wp_strip_all_tags( $r[2] ) ),
					'note'  => '',
				);
			}
		}
		if ( $items ) {
			$groups[] = array( 'title' => $title, 'items' => $items );
		}
	}

	if ( ! $groups ) {
		return array();
	}
	$data['groups'] = $groups;
	return $data;
}
