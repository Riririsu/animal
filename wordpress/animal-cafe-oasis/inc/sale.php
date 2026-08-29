<?php
/**
 * 「お迎えできる子」（販売中の個体）
 *
 * どうぶつ紹介が「種類」の説明なのに対して、こちらは1匹ずつの登録です。
 * 管理画面から追加・並べ替え・削除ができ、性別と金額もここで入力します。
 *
 * 個体ごとのページは作りません（生体販売ページのカードとして並びます）。
 * 種類を選ぶと、カードがその子の「どうぶつ紹介」ページへのリンクになります。
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** 性別の選択肢。 */
function oasis_sale_sexes() {
	return array(
		''     => '選ばない',
		'male' => 'オス',
		'female' => 'メス',
		'unknown' => '不明',
	);
}

/* ------------------------------------------------------------------ *
 *  投稿タイプ
 * ------------------------------------------------------------------ */
function oasis_register_sale_post_type() {
	register_post_type( 'oasis_sale', array(
		'labels' => array(
			'name'                  => 'お迎えできる子',
			'singular_name'         => 'お迎えできる子',
			'add_new'               => '新規追加',
			'add_new_item'          => 'お迎えできる子を追加',
			'edit_item'             => 'お迎えできる子を編集',
			'new_item'              => '新しい子',
			'view_item'             => '生体販売ページで見る',
			'search_items'          => 'お迎えできる子を検索',
			'not_found'             => 'まだ登録がありません',
			'not_found_in_trash'    => 'ゴミ箱にはありません',
			'featured_image'        => '写真',
			'set_featured_image'    => '写真を選ぶ',
			'remove_featured_image' => '写真を外す',
			'use_featured_image'    => 'この子の写真に使う',
		),
		// 個体ごとのページは作らないので公開URLは持たせない
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'publicly_queryable'  => false,
		'exclude_from_search' => true,
		'has_archive'         => false,
		'rewrite'             => false,
		'menu_icon'           => 'dashicons-heart',
		'menu_position'       => 6,
		'supports'            => array( 'title', 'thumbnail', 'page-attributes' ),
		'show_in_rest'        => false,
	) );
}
add_action( 'init', 'oasis_register_sale_post_type' );

/* ------------------------------------------------------------------ *
 *  入力欄
 * ------------------------------------------------------------------ */
function oasis_sale_meta_boxes() {
	add_meta_box( 'oasis-sale-data', 'この子のデータ', 'oasis_box_sale', 'oasis_sale', 'normal', 'high' );
	add_meta_box( 'oasis-sale-news', 'お知らせにも載せる', 'oasis_box_sale_news', 'oasis_sale', 'side', 'high' );
}
add_action( 'add_meta_boxes', 'oasis_sale_meta_boxes' );

function oasis_box_sale( $post ) {
	wp_nonce_field( 'oasis_save_sale', 'oasis_sale_nonce' );

	$animal = (int) get_post_meta( $post->ID, '_oasis_sale_animal', true );
	$sex    = (string) get_post_meta( $post->ID, '_oasis_sale_sex', true );
	$price  = (string) get_post_meta( $post->ID, '_oasis_sale_price', true );
	$note   = (string) get_post_meta( $post->ID, '_oasis_sale_note', true );

	$animals = get_posts( array(
		'post_type'      => 'animal',
		'posts_per_page' => -1,
		'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
	) );
	?>
	<p class="description" style="margin:0 0 14px">
		上の「タイトル」に出したい名前を入れてください（例：ネザーランドドワーフ、うさこ）。<br>
		写真は右側の「写真」から選びます。並び順は右側の「順序」の数字が小さい子から並びます。
	</p>

	<table class="form-table oasis-table"><tbody>
		<tr>
			<th scope="row"><label for="oasis_sale_animal">種類</label></th>
			<td>
				<select id="oasis_sale_animal" name="oasis_sale_animal">
					<option value="0">選ばない</option>
					<?php foreach ( $animals as $a ) : ?>
						<option value="<?php echo (int) $a->ID; ?>" <?php selected( $animal, $a->ID ); ?>>
							<?php echo esc_html( $a->post_title ); ?>
						</option>
					<?php endforeach; ?>
				</select>
				<p class="description">
					選ぶと、カードを押したときにその子の「どうぶつ紹介」ページが開きます。<br>
					「選ばない」のときは、カードはリンクにならずそのまま表示されます。
				</p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="oasis_sale_sex">性別</label></th>
			<td>
				<select id="oasis_sale_sex" name="oasis_sale_sex">
					<?php foreach ( oasis_sale_sexes() as $val => $label ) : ?>
						<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $sex, $val ); ?>>
							<?php echo esc_html( $label ); ?>
						</option>
					<?php endforeach; ?>
				</select>
				<p class="description">「選ばない」にすると、カードに性別の行が出ません。</p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="oasis_sale_price">金額</label></th>
			<td>
				<input type="text" id="oasis_sale_price" name="oasis_sale_price" class="regular-text"
				       placeholder="88,000円" value="<?php echo esc_attr( $price ); ?>">
				<p class="description">
					そのままカードに出ます。「88,000円」「応相談」「お問い合わせください」など自由に書けます。<br>
					空にすると、カードに金額の行が出ません。
				</p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="oasis_sale_note">ひとこと</label></th>
			<td>
				<textarea id="oasis_sale_note" name="oasis_sale_note" rows="2" class="large-text"
				          placeholder="人なつっこく、抱っこもできます。"><?php echo esc_textarea( $note ); ?></textarea>
				<p class="description">カードの下に小さく出ます。長いときは2行までで切れます。空でも構いません。</p>
			</td>
		</tr>
	</tbody></table>
	<?php
}

/* ------------------------------------------------------------------ *
 *  お知らせにも載せる
 * ------------------------------------------------------------------ */
function oasis_box_sale_news( $post ) {
	$news_id = (int) get_post_meta( $post->ID, '_oasis_sale_news_id', true );

	// すでに投稿済みなら、その記事の状態を出す（もう作られません）
	if ( $news_id ) {
		if ( get_post( $news_id ) && 'trash' !== get_post_status( $news_id ) ) {
			printf(
				'<p>お知らせに投稿済みです。</p><p><a href="%s">「%s」を編集</a></p>',
				esc_url( (string) get_edit_post_link( $news_id, '' ) ),
				esc_html( get_the_title( $news_id ) )
			);
		} else {
			echo '<p>お知らせに投稿済みです（記事は削除されています）。</p>';
		}
		echo '<p class="description">お知らせに載せるのは1回だけです。'
			. 'この子を何度更新しても、お知らせが増えることはありません。<br>'
			. 'もう一度載せたいときは、お知らせから新しい記事を書いてください。</p>';
		return;
	}

	// 新規追加のときは既定でオン
	$raw = get_post_meta( $post->ID, '_oasis_sale_news', true );
	$on  = ( '' === $raw ) ? '1' : $raw;
	?>
	<p>
		<label>
			<input type="checkbox" name="oasis_sale_news" value="1" <?php checked( $on, '1' ); ?>>
			<strong>お知らせにも投稿する</strong>
		</label>
	</p>
	<p class="description">
		「公開」を押したときに、お知らせ（カテゴリ：入荷）に記事を1本つくります。<br>
		写真・名前・性別・金額から文章を組み立てます。<strong>あとから自由に書き換えられます。</strong><br>
		投稿されるのは<strong>1回だけ</strong>です。この子を更新しても増えません。
	</p>
	<?php
}

/* ------------------------------------------------------------------ *
 *  保存
 * ------------------------------------------------------------------ */
function oasis_save_sale( $post_id ) {
	if ( ! isset( $_POST['oasis_sale_nonce'] )
		|| ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['oasis_sale_nonce'] ) ), 'oasis_save_sale' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$animal = isset( $_POST['oasis_sale_animal'] ) ? absint( wp_unslash( $_POST['oasis_sale_animal'] ) ) : 0;
	if ( $animal && 'animal' === get_post_type( $animal ) ) {
		update_post_meta( $post_id, '_oasis_sale_animal', $animal );
	} else {
		delete_post_meta( $post_id, '_oasis_sale_animal' );
	}

	$sexes = oasis_sale_sexes();
	$sex   = isset( $_POST['oasis_sale_sex'] ) ? sanitize_key( wp_unslash( $_POST['oasis_sale_sex'] ) ) : '';
	update_post_meta( $post_id, '_oasis_sale_sex', isset( $sexes[ $sex ] ) ? $sex : '' );

	$price = isset( $_POST['oasis_sale_price'] ) ? sanitize_text_field( wp_unslash( $_POST['oasis_sale_price'] ) ) : '';
	update_post_meta( $post_id, '_oasis_sale_price', $price );

	$note = isset( $_POST['oasis_sale_note'] ) ? sanitize_textarea_field( wp_unslash( $_POST['oasis_sale_note'] ) ) : '';
	update_post_meta( $post_id, '_oasis_sale_note', $note );

	// 「お知らせにも投稿する」（チェックが無いときは POST に入ってこない）
	update_post_meta( $post_id, '_oasis_sale_news', isset( $_POST['oasis_sale_news'] ) ? '1' : '0' );
	oasis_sale_maybe_post_news( $post_id );
}
add_action( 'save_post_oasis_sale', 'oasis_save_sale' );

/**
 * 条件がそろっていれば、お知らせに記事を1本つくる。
 *
 * つくるのは1回だけです（作った記事のIDを覚えておきます）。
 * 下書きのあいだは何もせず、「公開」したときに投稿します。
 */
function oasis_sale_maybe_post_news( $post_id ) {
	if ( '1' !== get_post_meta( $post_id, '_oasis_sale_news', true ) ) {
		return;                                   // チェックが外れている
	}
	if ( 'publish' !== get_post_status( $post_id ) ) {
		return;                                   // まだ下書き
	}
	if ( get_post_meta( $post_id, '_oasis_sale_news_id', true ) ) {
		// 一度投稿したら、それ以上は作らない。
		// お知らせ側で記事を消した場合も、勝手に作り直さない。
		return;
	}

	$sexes = oasis_sale_sexes();
	$name  = get_the_title( $post_id );
	$animal_id = (int) get_post_meta( $post_id, '_oasis_sale_animal', true );
	$kind  = $animal_id ? get_the_title( $animal_id ) : '';
	$sex   = (string) get_post_meta( $post_id, '_oasis_sale_sex', true );
	$price = (string) get_post_meta( $post_id, '_oasis_sale_price', true );
	$note  = (string) get_post_meta( $post_id, '_oasis_sale_note', true );

	// 1行目：「◯◯の△△が仲間入りしました。」
	$lines = array( ( $kind && $kind !== $name ? $kind . 'の' : '' ) . $name . 'が仲間入りしました。' );

	// 2行目：性別と価格（入っているものだけ）
	$facts = array();
	if ( '' !== $sex && isset( $sexes[ $sex ] ) ) {
		$facts[] = '性別：' . $sexes[ $sex ];
	}
	if ( '' !== $price ) {
		$facts[] = '価格：' . $price;
	}
	if ( $facts ) {
		$lines[] = implode( '／', $facts );
	}

	if ( '' !== $note ) {
		$lines[] = $note;
	}

	$sales_url = get_permalink_by_slug( 'sales' );
	$lines[] = $sales_url
		? 'くわしくは<a href="' . esc_url( $sales_url ) . '">生体販売・お迎えのご相談</a>のページをご覧ください。'
		: 'くわしくはお気軽にお問い合わせください。';

	$news_id = wp_insert_post( array(
		'post_type'    => 'post',
		'post_status'  => 'publish',
		'post_title'   => $name . 'が仲間入りしました',
		'post_content' => implode( "\n\n", $lines ),
	), true );

	if ( is_wp_error( $news_id ) ) {
		return;
	}

	// カテゴリ「入荷」
	$term = get_term_by( 'slug', 'arrival', 'category' );
	if ( $term && ! is_wp_error( $term ) ) {
		wp_set_post_terms( $news_id, array( (int) $term->term_id ), 'category' );
	}

	// 写真はこの子のものを使う。無ければ種類の写真。
	$thumb = get_post_thumbnail_id( $post_id );
	if ( ! $thumb && $animal_id ) {
		$thumb = oasis_main_image_id( $animal_id );
	}
	if ( $thumb ) {
		set_post_thumbnail( $news_id, (int) $thumb );
	}

	update_post_meta( $post_id, '_oasis_sale_news_id', (int) $news_id );
	set_transient( 'oasis_sale_news_' . get_current_user_id(), (int) $news_id, 60 );
}

/** お知らせを投稿したことを、1回だけ管理画面で知らせる。 */
function oasis_sale_news_notice() {
	$key     = 'oasis_sale_news_' . get_current_user_id();
	$news_id = (int) get_transient( $key );
	if ( ! $news_id || ! get_post( $news_id ) ) {
		return;
	}
	delete_transient( $key );
	printf(
		'<div class="notice notice-success is-dismissible"><p>'
		. 'お知らせに「<strong>%s</strong>」を投稿しました。'
		. '<a href="%s">文章を編集する</a>'
		. '</p></div>',
		esc_html( get_the_title( $news_id ) ),
		esc_url( (string) get_edit_post_link( $news_id, '' ) )
	);
}
add_action( 'admin_notices', 'oasis_sale_news_notice' );

/* ------------------------------------------------------------------ *
 *  一覧画面の列
 * ------------------------------------------------------------------ */
function oasis_sale_columns( $cols ) {
	$new = array();
	foreach ( $cols as $key => $label ) {
		if ( 'title' === $key ) {
			$new['oasis_sale_photo'] = '写真';
		}
		$new[ $key ] = $label;
		if ( 'title' === $key ) {
			$new['oasis_sale_kind']  = '種類';
			$new['oasis_sale_sex']   = '性別';
			$new['oasis_sale_price'] = '金額';
		}
	}
	return $new;
}
add_filter( 'manage_oasis_sale_posts_columns', 'oasis_sale_columns' );

function oasis_sale_column( $col, $post_id ) {
	if ( 'oasis_sale_photo' === $col ) {
		$id = get_post_thumbnail_id( $post_id );
		echo $id ? wp_get_attachment_image( $id, array( 60, 60 ), false, array( 'style' => 'border-radius:8px' ) ) : '—';
		return;
	}
	if ( 'oasis_sale_kind' === $col ) {
		$a = (int) get_post_meta( $post_id, '_oasis_sale_animal', true );
		echo $a ? esc_html( get_the_title( $a ) ) : '—';
		return;
	}
	if ( 'oasis_sale_sex' === $col ) {
		$sexes = oasis_sale_sexes();
		$sex   = (string) get_post_meta( $post_id, '_oasis_sale_sex', true );
		echo ( '' !== $sex && isset( $sexes[ $sex ] ) ) ? esc_html( $sexes[ $sex ] ) : '—';
		return;
	}
	if ( 'oasis_sale_price' === $col ) {
		$price = (string) get_post_meta( $post_id, '_oasis_sale_price', true );
		echo '' !== $price ? esc_html( $price ) : '—';
	}
}
add_action( 'manage_oasis_sale_posts_custom_column', 'oasis_sale_column', 10, 2 );

/* ------------------------------------------------------------------ *
 *  表示（ショートコード [oasis_sale_list]）
 * ------------------------------------------------------------------ */
function oasis_sale_list_shortcode() {
	$posts = get_posts( array(
		'post_type'      => 'oasis_sale',
		'posts_per_page' => -1,
		'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
	) );

	if ( ! $posts ) {
		return '<p class="sale-empty">いまお迎えいただける子のご案内は準備中です。'
			. 'お気軽にお問い合わせください。</p>';
	}

	$sexes = oasis_sale_sexes();
	$out   = '<div class="grid grid--4" data-reveal-stagger="up">';

	foreach ( $posts as $post ) {
		$animal_id = (int) get_post_meta( $post->ID, '_oasis_sale_animal', true );
		$link      = $animal_id ? get_permalink( $animal_id ) : '';
		$sex       = (string) get_post_meta( $post->ID, '_oasis_sale_sex', true );
		$price     = (string) get_post_meta( $post->ID, '_oasis_sale_price', true );
		$note      = (string) get_post_meta( $post->ID, '_oasis_sale_note', true );
		$cat       = $animal_id ? (string) oasis_animal_meta( $animal_id, 'en_cat' ) : '';

		$thumb_id = get_post_thumbnail_id( $post->ID );
		if ( ! $thumb_id && $animal_id ) {
			$thumb_id = oasis_main_image_id( $animal_id );   // 写真未設定なら種類の写真を借りる
		}
		$thumb = $thumb_id
			? wp_get_attachment_image( $thumb_id, 'oasis-card', false, array(
				'class' => 'card__thumb', 'alt' => $post->post_title,
				'loading' => 'lazy', 'decoding' => 'async',
			) )
			: '<span class="card__thumb card__thumb--sand"></span>';

		$meta = '';
		if ( '' !== $sex && isset( $sexes[ $sex ] ) ) {
			$meta .= '<span class="sale-meta__label">性別</span>'
				. '<span class="sale-meta__value">' . esc_html( $sexes[ $sex ] ) . '</span>';
		}
		if ( '' !== $price ) {
			$meta .= '<span class="sale-meta__label">価格</span>'
				. '<span class="sale-meta__value sale-meta__value--price">' . esc_html( $price ) . '</span>';
		}

		$body = '<span class="card__body">'
			. ( '' !== $cat ? '<span class="card__cat">' . esc_html( $cat ) . '</span>' : '' )
			. '<span class="card__name">' . esc_html( $post->post_title ) . '</span>'
			. ( '' !== $meta ? '<span class="sale-meta">' . $meta . '</span>' : '' )
			. ( '' !== $note ? '<span class="sale-note">' . esc_html( $note ) . '</span>' : '' )
			. '</span>';

		$out .= $link
			? '<a class="card" href="' . esc_url( $link ) . '">' . $thumb . $body . '</a>'
			: '<div class="card">' . $thumb . $body . '</div>';
	}

	return $out . '</div>';
}
add_shortcode( 'oasis_sale_list', 'oasis_sale_list_shortcode' );

/* ------------------------------------------------------------------ *
 *  すでにある生体販売ページを新しい形に直す（1回だけ）
 *
 *  初期データの取り込みは「ページがまだ無いとき」しか作らないので、
 *  以前のテーマで作られたページは、固定の4枚のカードが入ったままになります。
 *  そのままだと「お迎えできる子」を追加してもサイトに出ないため、
 *  古いカードの部分だけを [oasis_sale_list] に置き換えます。
 *
 *  ・書き換えるのは、古いカードの並びがそのまま残っているときだけ
 *  ・すでに [oasis_sale_list] があるページには触りません
 *  ・WordPress のリビジョンが残るので、元に戻すこともできます
 * ------------------------------------------------------------------ */
function oasis_sale_upgrade_page() {
	if ( get_option( 'oasis_sale_page_done' ) ) {
		return;
	}

	$page = get_page_by_path( 'sales' );
	if ( ! $page ) {
		update_option( 'oasis_sale_page_done', 1 );
		return;
	}

	$content = (string) $page->post_content;

	// すでに新しい形なら何もしない
	if ( false !== strpos( $content, '[oasis_sale_list]' ) ) {
		update_option( 'oasis_sale_page_done', 1 );
		return;
	}

	$new_block = '<h2 class="section-title section-title--sm" style="margin:50px 0 22px" data-reveal="up">いまお迎えできる子</h2>'
		. "\n      [oasis_sale_list]";

	// 古い見出し＋カードの並びを、まるごと置き換える
	$updated = preg_replace(
		'/<h2[^>]*>お迎えのご相談が多い子<\/h2>\s*<div class="grid grid--4"[^>]*>.*?<\/div>/us',
		$new_block,
		$content,
		1
	);

	if ( null === $updated || $updated === $content ) {
		// 形が違って自動では直せない。管理画面で知らせる。
		update_option( 'oasis_sale_page_manual', 1 );
		update_option( 'oasis_sale_page_done', 1 );
		return;
	}

	wp_update_post( array( 'ID' => $page->ID, 'post_content' => $updated ) );
	update_option( 'oasis_sale_page_done', 1 );
}
add_action( 'admin_init', 'oasis_sale_upgrade_page' );

/** 自動で直せなかったときのお知らせ。 */
function oasis_sale_page_notice() {
	if ( ! get_option( 'oasis_sale_page_manual' ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$page = get_page_by_path( 'sales' );
	if ( ! $page ) {
		delete_option( 'oasis_sale_page_manual' );
		return;
	}
	printf(
		'<div class="notice notice-warning"><p>'
		. '<strong>あにまるカフェ Oasis：</strong>'
		. '「お迎えできる子」をサイトに出すための記述が、生体販売ページに見つかりませんでした。<br>'
		. '<a href="%s">生体販売ページを編集</a>して、'
		. '「いまお迎えできる子」を出したい場所に <code>[oasis_sale_list]</code> と1行だけ書いて更新してください。'
		. '</p></div>',
		esc_url( (string) get_edit_post_link( $page->ID, '' ) )
	);
}
add_action( 'admin_notices', 'oasis_sale_page_notice' );
