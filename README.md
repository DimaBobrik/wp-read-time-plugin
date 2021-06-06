# wp-reading-time-plugin
<p>
Reading Time Plugin for wordpress, work for any post, page, custom_post_type, has selection in what post_type show the plugin.
Plugin has Localization, need create only .po files in languages folder like example.
</p>
Plugin has shortcode with parameters and can worck in two ways :<br>
'reading_time_text'        = can be any text SSSS will be thanget to time number<br>
'reading_time_speed'       = '500' any number default 200<br>
'reading_time_bar_color'   = 'red' or any color can get in hex code or decimal (rgb)<br>
'reading_time_bar_display' = 'yes' or 'no'<br>
'reading_time_minutes'     = 'no' or 'yes'<br>
'reading_time_round'       = 'up' or 'down'<br>
<br>
1.With parameters:
<br>
<br>
[reading_time  <br>
'reading_time_text' = 'The estimated reading time for this post is SSSS seconds' <br>
'reading_time_speed' = '500'<br>
'reading_time_bar_color'   = 'red'<br>
'reading_time_bar_display' = 'yes'<br>
'reading_time_minutes'     = 'no'<br>
'reading_time_round'       = 'up' ]<br>
<br>
2.With text inside shortcode:<br>
<br>
[reading_time <br>
'reading_time_text' = 'The estimated reading time for this post is SSSS seconds' <br>
'reading_time_speed' = '500'<br>
'reading_time_bar_color'   = 'red'<br>
'reading_time_bar_display' = 'yes'<br>
'reading_time_minutes'     = 'no'<br>
'reading_time_round'       = 'up' ]<br>
Lorem ipsum text only for example. Lorem ipsum text only for example.<br>
[/reading_time]<br>

Plugin has the_reading_time and get_reading_time functions. <br>
Functions can get's parameters like shortcode: <br>
<br>
the_reading_time(array(<br>
'reading_time_text'        => 'The estimated reading time for this post is SSSS seconds',<br>
'reading_time_speed'       => '500',<br>
'reading_time_bar_color'   => 'red',<br>
'reading_time_bar_display' => 'yes',<br>
'reading_time_minutes'     => 'no',<br>
'reading_time_round'       => 'up',<br>
		));
<br><br>
get_reading_time(array(<br>
'reading_time_text'        => 'The estimated reading time for this post is SSSS seconds',<br>
'reading_time_speed'       => '500',<br>
'reading_time_bar_color'   => 'red',<br>
'reading_time_bar_display' => 'yes',<br>
'reading_time_minutes'     => 'no',<br>
'reading_time_round'       => 'up',<br>
		));
