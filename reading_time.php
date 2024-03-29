<?php
/*
Plugin Name: Custom Reading Time
Description: The “Reading Time” value will be auto calculated according to the length of the content of the post.
Author: Dima Bobrovski
Version: 9.0
Plugin URI: https://github.com/DimaBobrik/wp-read-time-plugin
*/

add_filter( 'the_content', 'reading_time' );

add_action( 'admin_menu', 'reading_time_menu' );

function short_reading_time( $atts = array(), $content = false ) {
	/** @var array default shortcode attributes. */
	$shortcode_args = shortcode_atts(
		array(
			'reading_time_text'        => 'The estimated reading time for this post is SSSS seconds',
			'reading_time_speed'       => '200',
			'reading_time_bar_color'   => 'blue',
			'reading_time_bar_display' => 'yes',
			'reading_time_minutes'     => 'no',
			'reading_time_round'       => 'up',
		),
		$atts
	);
	$time           = ! $content ? reading_time( get_the_content(), $shortcode_args ) : reading_time( $content );

	return $time;
}

add_shortcode( 'reading_time', 'short_reading_time' );

function reading_time( $content, $reading_time_options = false ) {

	/** @var boolean flag show reading time with content or without. */
	$shortcode_flag = ! $reading_time_options;

	/** @var array reading time attributes. */
	$reading_time_options = ! $reading_time_options ? get_option( 'reading_time' ) : $reading_time_options;

	/**
	 * if current post_type exist in selected  post types.
	 * Does not apply shortcode, the_reading_time, get_reading_time.
	 */
	$current_object = get_queried_object();
	if ( ! empty( $current_object ) && isset( $current_object->post_type ) && isset( $reading_time_options['reading_time_post_types'] ) ) {
		if ( ! in_array( $current_object->post_type, $reading_time_options['reading_time_post_types'] ) ) {
			return $content;
		}
	}

	/** @var integer first try to get estimated time from custom values. */
	$tempo = get_post_custom_values( 'readingtime' );

	if ( empty( $tempo ) || ! is_numeric( $tempo[0] ) ) {
		/** calculate estimated time */
		if ( ! is_numeric( $reading_time_options['reading_time_speed'] ) or $reading_time_options['reading_time_speed'] <= 0 ) {
			/** Default value. */
			$reading_time_options['reading_time_speed'] = 200;
		}
		/** Calculating Reading time and round up or down. */
		$tempo[0] = round(
			str_word_count( strip_tags( $content ) ) * 60 / $reading_time_options['reading_time_speed'],
			0,
			$reading_time_options["reading_time_round"] === 'up' ? PHP_ROUND_HALF_UP : PHP_ROUND_HALF_DOWN
		);

		if ( ! is_numeric( $tempo[0] ) ) {
			return $content;
		}
	}
	$shown_reading_time = $tempo[0];
	/** compose text message */
	$text = $reading_time_options['reading_time_text'];
	if ( '' === $text ) {
		$text = 'I think you will spend SSSS seconds reading this post';
	}
	/** Calculating Reading time in minutes. */
	if ( 'yes' === $reading_time_options['reading_time_minutes'] ) {
		$shown_reading_time = (int) ( $tempo[0] / 60 );
		$text               = str_replace( 'seconds', 'minutes', $text );
	}
	/** Replace SSSS to calculated reading time. */
	$text = str_replace( 'SSSS', $shown_reading_time, $text );
	$out  = '<p class="readingtime_text">' . stripslashes( $text ) . '</p>';

	/** check against 'yes' for backward compatibility */
	if ( 'yes' === $reading_time_options['reading_time_bar_display'] ) {
		/** display progress bar */
		/** progress bar background color*/
		$barcolor = $reading_time_options['reading_time_bar_color'];
		if ( '' === $barcolor ) {
			$barcolor = 'red';
		}
		/** uniq string */
		$uniq_name = uniqid( null, false );
		$out       .= '
				<style>
					.readingtime_border { border:1px solid black;width:250px;height:10px; }
					.readingtime_bar_' . $uniq_name . ' { background-color:' . $barcolor . ';width:0px;height:10px; }
				</style>
				
				<p>
					<div class="readingtime_border">
						<div id="readingtime_bar_in_' . $uniq_name . '" class="readingtime_bar_' . $uniq_name . '"></div>
					</div>
				</p>
			
				<script type="text/javascript">
			jQuery(document).ready(function(){
				   jQuery("#readingtime_bar_in_' . $uniq_name . ' ").animate({
				    width: "100%"
				}, ' . $tempo[0] . ' * 1000);
				});				
				</script>
				';
	}

	return ! $shortcode_flag ? $out : $out . $content;
}

function reading_time_menu() {
	/** create option page in settings */
	add_options_page(
		'Reading Time Options',
		__( 'Reading Time', 'reading-time' ),
		'manage_options',
		'reading_time_options',
		'reading_time_options',
	);
}

function reading_time_options() {

	/**  must check that the user has the required capability. */
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.' ) );
	}

	/** See if the user has posted us some information */
	if ( ! empty( $_POST ) ) {
		/** Update Reading Time Options for admin panel */
		update_option(
			'reading_time',
			array(
				'reading_time_text'        => $_POST['reading_time_text'] ?? esc_html__( 'The estimated reading time for this post is SSSS seconds.' ),
				'reading_time_speed'       => $_POST['reading_time_speed'] ?? esc_html__( '200' ),
				'reading_time_bar_color'   => $_POST['reading_time_bar_color'] ?? esc_html__( 'blue' ),
				'reading_time_bar_display' => $_POST['reading_time_bar_display'] ?? esc_html__( 'yes' ),
				'reading_time_minutes'     => $_POST['reading_time_minutes'] ?? esc_html__( 'no' ),
				'reading_time_round'       => $_POST['reading_time_round'] ?? esc_html__( 'up' ),
				'reading_time_post_types'  => $_POST['reading_time_post_types'] ?? esc_html__( 'post' ),
			)
		);
		/** Put an settings updated message on the screen */
		?>
		<div class="updated">
			<p><strong><?php esc_html_e( 'Settings saved.', 'reading-time' ); ?></strong></p>
		</div>
		<?php
	}
	/** get stored values */
	$reading_time_options = get_option( 'reading_time' );
	if ( false === $reading_time_options ) {
		/** insert default values */
		add_option(
			'reading_time',
			array(
				'reading_time_text'        => 'The estimated reading time for this post is SSSS seconds',
				'reading_time_speed'       => '200',
				'reading_time_bar_color'   => 'blue',
				'reading_time_bar_display' => 'yes',
				'reading_time_minutes'     => 'no',
				'reading_time_round'       => 'up',
				'reading_time_post_types'  => 'post',
			)
		);
		$reading_time_options = get_option( 'reading_time' );
	}

	/** chek select inputs values what was selected */
	$sel_bar_yes = 'yes' === $reading_time_options['reading_time_bar_display'] ? 'selected="selected"' : '';
	$sel_bar_no  = 'no' === $reading_time_options['reading_time_bar_display'] ? 'selected="selected"' : '';

	$sel_bar_up   = 'up' === $reading_time_options['reading_time_round'] ? 'selected="selected"' : '';
	$sel_bar_down = 'down' === $reading_time_options['reading_time_round'] ? 'selected="selected"' : '';

	$sel_minutes_yes = 'yes' === $reading_time_options['reading_time_minutes'] ? 'selected="selected"' : '';
	$sel_minutes_no  = 'no' === $reading_time_options['reading_time_minutes'] ? 'selected="selected"' : '';

	$plugin_url = plugin_dir_url( __FILE__ );
	/** enqueue style for admin panel  */
	wp_enqueue_style( 'reading-time-admin', $plugin_url . 'css/reading-time-admin.css', array(), '1.0', false );

	$args       = array(
		'public' => true,
	);
	$output     = 'names'; // 'names' or 'objects' (default: 'names')
	$operator   = 'and'; // 'and' or 'or' (default: 'and')
	$post_types = get_post_types( $args, $output, $operator );
	if ( in_array( 'attachment', $post_types ) ) {
		unset( $post_types['attachment'] );
	}


	/** settings form */
	?>
	<div class="wrap">
		<h2>
			<?php esc_html_e( 'Reading Time', 'reading-time' ); ?>
		</h2>
		<form name="form1" method="post" action="">
			<div class="input-holder">
				<label for="reading_time_text">
					<?php esc_html_e( 'Free Text', 'reading-time' ); ?>:
				</label>
				<textarea id="reading_time_text" name="reading_time_text" rows="2" cols="70">
					<?php esc_html_e( stripslashes( $reading_time_options['reading_time_text'] ), 'reading-time' ); ?>
				</textarea>
				<div class="description">
					<?php esc_html_e( "Use 'SSSS' as a placeholder for seconds, e.g. 'The estimated reading time for this post is SSSS seconds'", 'reading-time' ); ?>
				</div>
			</div>
			<div class="input-holder">
				<label for="reading_time_speed"><?php esc_html_e( 'Speed', 'reading-time' ); ?>:</label>
				<input type="text" name="reading_time_speed" id="reading_time_speed" size="20"
				       value="<?php echo absint( $reading_time_options['reading_time_speed'] ); ?>"/>
				<div class="description">
					<?php _e( "E.g. 250 for fast readers, 150 for slow readers; the default value is 200", 'reading-time' ); ?>
				</div>
			</div>
			<div class="input-holder">
				<label for="reading_time_bar_color"><?php esc_html_e( 'Progress bar color', 'reading-time' ); ?>
					:</label>
				<input type="text" name="reading_time_bar_color" id="reading_time_bar_color" size="20"
				       value="<?php echo $reading_time_options['reading_time_bar_color']; ?>"/>
				<div class="description"><?php echo esc_html_e( "E.g. 'blue', '#006699'", 'reading-time' ); ?></div>
			</div>
			<div class="input-holder">
				<label for="reading_time_bar_display"><?php esc_html_e( "Progress bar display", 'reading-time' ); ?>
					:</label>
				<select name="reading_time_bar_display" id="reading_time_bar_display">
					<option value="yes" <?php esc_html_e( $sel_bar_yes ); ?>> <?php esc_html_e( 'yes', 'reading-time' ); ?></option>
					<option value="no" <?php esc_html_e( $sel_bar_no ); ?>> <?php esc_html_e( 'no', 'reading-time' ); ?></option>
				</select>
			</div>
			<div class="input-holder">
				<label for="reading_time_round_up_down"><?php esc_html_e( "Round Up/Down", 'reading-time' ); ?>
					:</label>
				<select name="reading_time_round_up_down" id="reading_time_round_up_down">
					<option value="up" <?php esc_html_e( $sel_bar_up ); ?>> <?php esc_html_e( 'round up', 'reading-time' ); ?></option>
					<option value="down" <?php esc_html_e( $sel_bar_down ); ?>> <?php echo esc_html_e( 'round down', 'reading-time' ); ?></option>
				</select>
			</div>
			<div class="input-holder">
				<label for="reading_time_post_types"><?php esc_html_e( "Accepted post types for showing plugin", 'reading-time' ); ?>
					:</label>
				<select name="reading_time_post_types[]" id="reading_time_post_types" multiple>
					<?php foreach ( $post_types as $post_type ) {
						$post_type_selected = '';
						if ( is_array( $reading_time_options['reading_time_post_types'] ) && in_array( $post_type, $reading_time_options['reading_time_post_types'] ) ) {
							$post_type_selected = 'selected="selected"';
						}
						?>
						<option value="<?php echo $post_type; ?>" <?php echo $post_type_selected; ?>> <?php esc_html_e( $post_type ); ?></option>
					<?php } ?>
				</select>
			</div>

			<div class="input-holder">
				<label for="reading_time_minutes"><?php esc_html_e( "Show minutes instead of seconds", 'reading-time' ); ?>
					:</label>
				<select name="reading_time_minutes" id="reading_time_minutes">
					<option value="yes" <?php esc_html_e( $sel_minutes_yes, 'reading-time' ); ?>> <?php esc_html_e( 'yes', 'reading-time' ); ?></option>
					<option value="no" <?php esc_html_e( $sel_minutes_no, 'reading-time' ); ?>> <?php esc_html_e( 'no', 'reading-time' ); ?></option>
				</select>
				<div class="description"><?php esc_html_e( 'If active, remember to change the text accordingly (e.g.... SSSS minutes instead of ... SSSS seconds)', 'reading-time' ); ?>
				</div>
			</div>
			<div class="submit">
				<input type="submit" name="Submit" class="button-primary"
				       value="<?php esc_html_e( 'Save Changes', 'reading-time' ); ?>"/>
			</div>
		</form>
	</div>
	<?php
}

/**
 * Function the_reading_time array of parameters:
 * 'reading_time_text'        => some text SSSS will changed to time
 * 'reading_time_speed'       => 'time in integer default 200
 * 'reading_time_bar_color'   => color can be hex code or decimal or like (green,rea,blue...)
 * 'reading_time_bar_display' => yes/no
 * 'reading_time_minutes'     => yes/no
 * 'reading_time_round'       => up/down
 */
function the_reading_time( $params = false ) {
	$default_args = ! $params ? get_option( 'reading_time' ) : $params;
	$reading_time = reading_time( get_the_content(), $default_args );
	echo $reading_time;
}

/**
 * Function get_reading_time array of parameters:
 * 'reading_time_text'        => some text SSSS will changed to time
 * 'reading_time_speed'       => 'time in integer default 200
 * 'reading_time_bar_color'   => color can be hex code or decimal or like (green,rea,blue...)
 * 'reading_time_bar_display' => yes/no
 * 'reading_time_minutes'     => yes/no
 * 'reading_time_round'       => up/down
 */
function get_reading_time( $params = false ) {
	$default_args = ! $params ? get_option( 'reading_time' ) : $params;
	$reading_time = reading_time( get_the_content(), $default_args );

	return $reading_time;
}

/** Text Domain Localization */
function custom_load_plugin_textdomain() {
	load_plugin_textdomain( 'reading-time', false, basename( dirname( __FILE__ ) ) . '/languages/' );
}

add_action( 'plugins_loaded', 'custom_load_plugin_textdomain' );