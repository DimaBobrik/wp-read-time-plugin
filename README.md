# wp-read-time-plugin
Reading Time Plugin for wordpress,  plugin has shortcode with parameters and can worck in two ways :
'reading_time_text'        = can be any text SSSS will be thanget to time number
'reading_time_speed'       = '500' any number default 200
'reading_time_bar_color'   = 'red' or any color can get in hex code or decimal (rgb)
'reading_time_bar_display' = 'yes' or 'no'
'reading_time_minutes'     = 'no' or 'yes'
'reading_time_round'       = 'up' or 'down'

1.With parameters:

[reading_time 
'reading_time_text' = 'The estimated reading time for this post is SSSS seconds' 
'reading_time_speed' = '500'
'reading_time_bar_color'   = 'red'
'reading_time_bar_display' = 'yes'
'reading_time_minutes'     = 'no'
'reading_time_round'       = 'up' ]

2.With text inside shortcode:

[reading_time 
'reading_time_text' = 'The estimated reading time for this post is SSSS seconds' 
'reading_time_speed' = '500'
'reading_time_bar_color'   = 'red'
'reading_time_bar_display' = 'yes'
'reading_time_minutes'     = 'no'
'reading_time_round'       = 'up' ]
Lorem ipsum text only for example. Lorem ipsum text only for example.
[/reading_time]

Plugin has the_reading_time and get_reading_time functions.
Function get_reading_time can get's parameters like shortcode:

get_reading_time(array(
			'reading_time_text'        => 'The estimated reading time for this post is SSSS seconds',
			'reading_time_speed'       => '500',
			'reading_time_bar_color'   => 'red',
			'reading_time_bar_display' => 'yes',
			'reading_time_minutes'     => 'no',
			'reading_time_round'       => 'up',
		));
