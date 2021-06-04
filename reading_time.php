<?php
/*
Plugin Name: Custom Reading Time
Description: The “Reading Time” value will be auto calculated according to the length of the content of the post.
Author: Dima Bobrovski
Version: 1.0
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
	// WORKS ONLY ON SINGLE POST

	if ( ! is_single() && ! is_page() ) {
		return $content;
	}

	/** @var boolean flag show reading time with content or without. */
	$shortcode_flag = ! $reading_time_options;

	/** @var array reading time attributes. */
	$reading_time_options = ! $reading_time_options ? get_option( 'reading_time' ) : $reading_time_options;

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
	$default_reading_time = $tempo[0];

	/** Calculating Reading time in minutes. */
	$shown_reading_time = ( $reading_time_options['reading_time_minutes'] == 'yes' ) ? (int) ( $default_reading_time / 60 ) : $default_reading_time;

	/** compose text message */
	$text = $reading_time_options['reading_time_text'];
	if ( $text == '' ) {
		$text = 'I think you will spend SSSS seconds reading this post';
	}
	/** Replace SSSS to calculated reading time. */
	$text = str_replace( 'SSSS', $shown_reading_time, $text );

	$out = '<p class="readingtime_text">' . stripslashes( $text ) . '</p>';

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
				}, ' . $shown_reading_time . ' * 1000);
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
		'Reading Time',
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
			)
		);
		/** Put an settings updated message on the screen */
		?>
		<div class="updated">
			<p><strong><?php esc_html_e( 'Settings saved.', 'menu-test' ); ?></strong></p>
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
	wp_enqueue_style( 'reading-time-admin', $plugin_url . '/css/reading-time-admin.css', array(), filemtime( $plugin_url . '/css/reading-time-admin.css' ), false );

	/** settings form */
	?>
	<div class="wrap">
		<h2>
			<?php echo esc_html__( 'Reading Time', 'menu-test' ); ?>
		</h2>
		<form name="form1" method="post" action="">
			<div class="input-holder">
				<label for="reading_time_text">
					<?php esc_html_e( 'Text', 'menu-test' ); ?>:
				</label>
				<textarea id="reading_time_text" name="reading_time_text" rows="2" cols="70">
					<?php echo esc_html__( stripslashes( $reading_time_options['reading_time_text'] ) ); ?>
				</textarea>
				<div class="description">
					<?php esc_html_e( "Use 'SSSS' as a placeholder for seconds, e.g. 'The estimated reading time for this post is SSSS seconds'", 'menu-test' ); ?>
				</div>
			</div>
			<div class="input-holder">
				<label for="reading_time_speed"><?php echo esc_html__( 'Speed', 'menu-test' ); ?>:</label>
				<input type="text" name="reading_time_speed" id="reading_time_speed" size="20"
				       value="<?php echo absint( $reading_time_options['reading_time_speed'] ); ?>"/>
				<div class="description">
					<?php _e( "E.g. 250 for fast readers, 150 for slow readers; the default value is 200", 'menu-test' ); ?>
				</div>
			</div>
			<div class="input-holder">
				<label for="reading_time_bar_color"><?php echo esc_html__( 'Progress bar color', 'menu-test' ); ?>
					:</label>
				<input type="text" name="reading_time_bar_color" id="reading_time_bar_color" size="20"
				       value="<?php echo $reading_time_options['reading_time_bar_color']; ?>"/>
				<div class="description"><?php echo esc_html_e( "E.g. 'blue', '#006699'", 'menu-test' ); ?></div>
			</div>
			<div class="input-holder">
				<label for="reading_time_bar_display"><?php echo esc_html__( "Progress bar display", 'menu-test' ); ?>
					:</label>
				<select name="reading_time_bar_display" id="reading_time_bar_display">
					<option value="yes" <?php echo esc_html__( $sel_bar_yes ); ?>> <?php echo esc_html__( 'yes', 'menu-test' ); ?></option>
					<option value="no" <?php echo esc_html__( $sel_bar_no ); ?>> <?php echo esc_html__( 'no', 'menu-test' ); ?></option>
				</select>
			</div>
			<div class="input-holder">
				<label for="reading_time_round_up_down"><?php echo esc_html__( "Round Up/Down", 'menu-test' ); ?>
					:</label>
				<select name="reading_time_round_up_down" id="reading_time_round_up_down">
					<option value="up" <?php echo esc_html__( $sel_bar_up ); ?>> <?php echo esc_html__( 'round up', 'menu-test' ); ?></option>
					<option value="down" <?php echo esc_html__( $sel_bar_down ); ?>> <?php echo esc_html_e( 'round down', 'menu-test' ); ?></option>
				</select>
			</div>

			<div class="input-holder">
				<label for="reading_time_minutes"><?php echo esc_html__( "Show minutes instead of seconds", 'menu-test' ); ?>
					:</label>
				<select name="reading_time_minutes" id="reading_time_minutes">
					<option value="yes" <?php echo esc_html__( $sel_minutes_yes ); ?>> <?php echo esc_html__( 'yes', 'menu-test' ); ?></option>
					<option value="no" <?php echo esc_html__( $sel_minutes_no ); ?>> <?php echo esc_html__( 'no', 'menu-test' ); ?></option>
				</select>
				<div class="description">If active, remember to change the text accordingly (e.g. "... SSSS minutes"
					instead of "... SSSS seconds")
				</div>
			</div>
			<div class="submit">
				<input type="submit" name="Submit" class="button-primary"
				       value="<?php echo esc_html__( 'Save Changes' ) ?>"/>
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
	$time         = reading_time( get_the_content(), $default_args );
	echo $time;
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
	$time         = reading_time( get_the_content(), $default_args );

	return $time;
}
