# wp-read-time-plugin
Reading Time Plugin for wordpress,  plugin has shortcode with parameters and can worck in two ways :<br>
'reading_time_text'        = can be any text SSSS will be thanget to time number<br>
'reading_time_speed'       = '500' any number default 200<br>
'reading_time_bar_color'   = 'red' or any color can get in hex code or decimal (rgb)<br>
'reading_time_bar_display' = 'yes' or 'no'<br>
'reading_time_minutes'     = 'no' or 'yes'<br>
'reading_time_round'       = 'up' or 'down'<br>
<br>
1.With parameters:
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
Function get_reading_time can get's parameters like shortcode: <br>

get_reading_time(array(<br>
'reading_time_text'        => 'The estimated reading time for this post is SSSS seconds',<br>
'reading_time_speed'       => '500',<br>
'reading_time_bar_color'   => 'red',<br>
'reading_time_bar_display' => 'yes',<br>
'reading_time_minutes'     => 'no',<br>
'reading_time_round'       => 'up',<br>
		));
