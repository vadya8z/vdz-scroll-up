<?php
/*
Plugin Name: VDZ Scroll Up
Plugin URI:  http://online-services.org.ua
Description: VDZ Scroll Up plugin
Version:     1.2.3
Author:      VadimZ
Author URI:  http://online-services.org.ua#vdz-scroll-up
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'VDZ_SU_API', 'vdz_info_scroll_up' );

class VDZ_SU_SETTINGS{
	const ARROW_COLOR = '#2271b1';
}
require_once 'api.php';
require_once 'updated_plugin_admin_notices.php';

// Код активации плагина
register_activation_hook( __FILE__, 'vdz_su_activate_plugin' );
function vdz_su_activate_plugin() {
	global $wp_version;
	if ( version_compare( $wp_version, '3.8', '<' ) ) {
		// Деактивируем плагин
		deactivate_plugins( plugin_basename( __FILE__ ) );
		wp_die( 'This plugin required WordPress version 3.8 or higher' );
	}
	add_option( 'vdz_scroll_up_front_show', 1 );

	do_action( VDZ_SU_API, 'on', plugin_basename( __FILE__ ) );
}

// Код деактивации плагина
register_deactivation_hook( __FILE__, function () {
	$plugin_name = preg_replace( '|\/(.*)|', '', plugin_basename( __FILE__ ));
	$response = wp_remote_get( "http://api.online-services.org.ua/off/{$plugin_name}" );
	if ( ! is_wp_error( $response ) && isset( $response['body'] ) && ( json_decode( $response['body'] ) !== null ) ) {
		//TODO Вывод сообщения для пользователя
	}
} );
//Сообщение при отключении плагина
add_action( 'admin_init', function (){
	if(is_admin()){
		$plugin_data = get_plugin_data(__FILE__);
		$plugin_name = isset($plugin_data['Name']) ? $plugin_data['Name'] : ' us';
		$plugin_dir_name = preg_replace( '|\/(.*)|', '', plugin_basename( __FILE__ ));
		$handle = 'admin_'.$plugin_dir_name;
		wp_register_script( $handle, '', null, false, true );
		wp_enqueue_script( $handle );
		$msg = '';
		if ( function_exists( 'get_locale' ) && in_array( get_locale(), array( 'uk', 'ru_RU' ), true ) ) {
			$msg .= "Спасибо, что были с нами! ({$plugin_name}) Хорошего дня!";
		}else{
			$msg .= "Thanks for your time with us! ({$plugin_name}) Have a nice day!";
		}
		wp_add_inline_script( $handle, "document.getElementById('deactivate-".esc_attr($plugin_dir_name)."').onclick=function (e){alert('".esc_attr( $msg )."');}" );
	}
} );



/*Добавляем новые поля для в настройках шаблона шаблона для верификации сайта*/
function vdz_su_theme_customizer( $wp_customize ) {

	if ( ! class_exists( 'WP_Customize_Control' ) ) {
		exit;
	}


	// Добавляем секцию для идетнтификатора YS
	$wp_customize->add_section(
		'vdz_scroll_up_section',
		array(
			'title'    => __( 'VDZ Scroll Up' ),
			'priority' => 10,
		// 'description' => __( 'Scroll Up code on your site' ),
		)
	);
	// Добавляем настройки
	$wp_customize->add_setting(
		'vdz_scroll_up_front_show',
		array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	if( class_exists( 'WP_Customize_Color_Control' ) ){
		$wp_customize->add_setting( 'vdz_scroll_up_widget_color', array(
			'type' => 'option',
			'sanitize_callback'    => 'sanitize_hex_color',
			'default' => VDZ_SU_SETTINGS::ARROW_COLOR,
		));
		// Add Controls
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'vdz_scroll_up_widget_color', array(
			'label' => 'Widget Color',
			'section' => 'vdz_scroll_up_section',
			'settings' => 'vdz_scroll_up_widget_color',
		)));
    }

	// Show/Hide
	$wp_customize->add_control(
		new WP_Customize_Control(
			$wp_customize,
			'vdz_scroll_up_front_show',
			array(
				'label'       => __( 'VDZ Scroll Up' ),
				'section'     => 'vdz_scroll_up_section',
				'settings'    => 'vdz_scroll_up_front_show',
				'type'        => 'select',
				'description' => __( 'ON/OFF' ),
				'choices'     => array(
					1 => __( 'Show' ),
					0 => __( 'Hide' ),
				),
			)
		)
	);

	// Добавляем ссылку на сайт
	$wp_customize->add_setting(
		'vdz_scroll_up_link',
		array(
			'type' => 'option',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Control(
			$wp_customize,
			'vdz_scroll_up_link',
			array(
				// 'label'    => __( 'Link' ),
									'section' => 'vdz_scroll_up_section',
				'settings'                    => 'vdz_scroll_up_link',
				'type'                        => 'hidden',
				'description'                 => '<br/><a href="//online-services.org.ua#vdz-scroll-up" target="_blank">VadimZ</a>',
			)
		)
	);
}
add_action( 'customize_register', 'vdz_su_theme_customizer', 1 );


// Виджет
add_action( 'wp_footer', 'vdz_su_scroll_up_show', 1100 );
function vdz_su_scroll_up_show() {
	$vdz_scroll_up_front_show = (int) get_option( 'vdz_scroll_up_front_show' );
	if ( empty( $vdz_scroll_up_front_show ) ) {
		return;
	}
	$widget_color = sanitize_hex_color(get_option('vdz_scroll_up_widget_color',VDZ_SU_SETTINGS::ARROW_COLOR))
	?>
	<div id="vdz_scroll_up" onclick="window.scroll({top: 0,behavior: 'smooth'});">
		<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"	 viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve"><g>	<g>		<path d="M256,0C114.833,0,0,114.833,0,256s114.833,256,256,256s256-114.853,256-256S397.167,0,256,0z M256,472.341			c-119.275,0-216.341-97.046-216.341-216.341S136.725,39.659,256,39.659c119.295,0,216.341,97.046,216.341,216.341			S375.275,472.341,256,472.341z"/>	</g></g><g>	<g>		<path d="M369.227,283.365l-99.148-99.148c-7.734-7.694-20.226-7.694-27.96,0l-99.148,99.148c-6.365,7.416-6.365,18.382,0,25.798			c7.119,8.309,19.651,9.28,27.96,2.161L256,226.256l85.267,85.069c7.734,7.694,20.226,7.694,27.96,0			C376.921,303.591,376.921,291.098,369.227,283.365z"/></g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g></svg>
	</div>
	<style>
		#vdz_scroll_up svg{
			width: 40px;
			height: 40px;
			fill: <?php echo esc_attr($widget_color) ? esc_attr($widget_color) : '#0F9E5E';?>;
		}
		#vdz_scroll_up{
			display: inline-block;
            cursor: pointer;
			padding: 0;
			position: fixed;
			right: 20px;
			bottom: 20px;
			transition: all .5s ease-out 0s;
			border-radius: 50%;
			opacity: .7;
            z-index: 5000;
		}
	</style>
	<?php
}
//TODO: window.on.scroll hidden btn on start page

//add_action( 'wp_enqueue_scripts', 'vdz_su_scroll_up_scripts' );
//function vdz_su_scroll_up_scripts(){
//	wp_enqueue_script( 'vdz_google_events', plugin_dir_url( __FILE__ ) . 'assets/js/vdz_scroll_up.js', null, VDZ_GA_VERSION, false, true );
//}


// Добавляем допалнительную ссылку настроек на страницу всех плагинов
add_filter(
	'plugin_action_links_' . plugin_basename( __FILE__ ),
	function( $links ) {
		$settings_link = '<a href="' . esc_url( admin_url( 'customize.php?autofocus[section]=vdz_scroll_up_section' ) ) . '">' . esc_html__( 'Settings' ) . '</a>';
		array_unshift( $links, $settings_link );
		array_walk( $links, 'wp_kses_post' );
		return $links;
	}
);
