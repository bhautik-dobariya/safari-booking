<?php

/**
 * WordPress settings API demo class
 *
 * @author Tareq Hasan
 */
if ( !class_exists('WeDevs_Settings_API_Test' ) ):
class WeDevs_Settings_API_Test {

    private $settings_api;

    function __construct() {
        $this->settings_api = new WeDevs_Settings_API;

        add_action( 'admin_init', array($this, 'admin_init') );
        add_action( 'admin_menu', array($this, 'admin_menu') );
    }

    function admin_init() {

        //set the settings
        $this->settings_api->set_sections( $this->get_settings_sections() );
        $this->settings_api->set_fields( $this->get_settings_fields() );

        //initialize settings
        $this->settings_api->admin_init();
    }

    function admin_menu() {
        add_options_page( 'Safari Booking Settings', 'Safari Booking Settings', 'delete_posts', 'safari_booking_settings', array($this, 'plugin_page') );
    }

    function get_settings_sections() {
        $sections = array(
            array(
                'id'    => 'safari_booking_basic_settings',
                'title' => __( 'Basic Settings', 'wedevs' )
            )
        );
        return $sections;
    }

    /**
     * Returns all the settings fields
     *
     * @return array settings fields
     */
    function get_settings_fields() {
        $settings_fields = array(
            'safari_booking_basic_settings' => array(
                array(
                    'name'              => 'razor_pay_key_id',
                    'label'             => __( 'Razor pay key id', 'wedevs' ),
                    'desc'              => __( '', 'wedevs' ),
                    'placeholder'       => __( 'Razor pay key id', 'wedevs' ),
                    'type'              => 'text',
                    'default'           => '',
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                array(
                    'name'              => 'razor_pay_key_secret',
                    'label'             => __( 'Razor pay key secret', 'wedevs' ),
                    'desc'              => __( '', 'wedevs' ),
                    'placeholder'       => __( 'Razor pay key secret', 'wedevs' ),
                    'type'              => 'text',
                    'default'           => '',
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                array(
                    'name'              => 'adult_price_indian',
                    'label'             => __( 'Adult Price (Indian)', 'wedevs' ),
                    'desc'              => __( 'Fixed price for number any of adults', 'wedevs' ),
                    'placeholder'       => __( '0', 'wedevs' ),
                    'min'               => 0,
                    'max'               => 1000000,
                    'step'              => '0.01',
                    'type'              => 'number',
                    'default'           => '0',
                    'sanitize_callback' => 'floatval'
                ),
                array(
                    'name'              => 'adult_price_foreigner',
                    'label'             => __( 'Adult Price (Foreigner)', 'wedevs' ),
                    'desc'              => __( 'Fixed price for number any of adults', 'wedevs' ),
                    'placeholder'       => __( '0', 'wedevs' ),
                    'min'               => 0,
                    'max'               => 1000000,
                    'step'              => '0.01',
                    'type'              => 'number',
                    'default'           => '0',
                    'sanitize_callback' => 'floatval'
                ),
                array(
                    'name'              => 'child_price',
                    'label'             => __( 'Child Price', 'wedevs' ),
                    'desc'              => __( 'Per child price', 'wedevs' ),
                    'placeholder'       => __( '0', 'wedevs' ),
                    'min'               => 0,
                    'max'               => 1000000,
                    'step'              => '0.01',
                    'type'              => 'number',
                    'default'           => '0',
                    'sanitize_callback' => 'floatval'
                ),
                array(
                    'name'    => 'payment_page',
                    'label'   => __( 'Select Pyament Page', 'wedevs' ),
                    'desc'    => __( 'Select the payment page and put this <strong>[payment_form]</strong> shortcode anywhere you want on that page.', 'wedevs' ),
                    'type'    => 'pages',
                ),
                array(
                    'name'    => 'thank_you_page',
                    'label'   => __( 'Select thank you Page', 'wedevs' ),
                    'desc'    => __( 'Select the thank you page and put this <strong>[thank_you_form]</strong> shortcode anywhere you want on that page.', 'wedevs' ),
                    'type'    => 'pages',
                ),
                array(
                    'name'              => 'admin_email',
                    'label'             => __( 'Admin email for recieve booking email', 'wedevs' ),
                    'desc'              => __( '', 'wedevs' ),
                    'placeholder'       => __( 'Admin email address', 'wedevs' ),
                    'type'              => 'text',
                    'default'           => '',
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                array(
                    'name'    => 'setup',
                    'label'   => __( 'Setup', 'wedevs' ),
                    'desc'        => __( '
                        <strong>This 3 shortcode displayed below are available in this plugin to user proper functionality of safari booking process.</strong>
                        <div style="margin: 10px 0;"><code>[booking_form]</code> This shorcode will display basic booking fields.</div>
                        <div style="margin: 10px 0;"><code>[booking_form_gir_jungle]</code> This shorcode will display basic booking fields for gir jungle.</div>
                        <div style="margin: 10px 0;"><code>[booking_form_devalia_park]</code> This shorcode will display basic booking fields for devalia park.</div>
                        <div style="margin: 10px 0;"><code>[payment_form]</code> This shorcode will display basic booking info and fields for adults and child.</div>
                        <div style="margin: 10px 0;"><code>[booking_thank_you]</code> This shorcode will display booking information after customer booking.</div>
                    ', 'wedevs' ),
                    'type'        => 'html'
                ),
            )
        );

        return $settings_fields;
    }

    function plugin_page() {
        echo '<div class="wrap">';

        $this->settings_api->show_navigation();
        $this->settings_api->show_forms();

        echo '</div>';
    }

    /**
     * Get all the pages
     *
     * @return array page names with key value pairs
     */
    function get_pages() {
        $pages = get_pages();
        $pages_options = array();
        if ( $pages ) {
            foreach ($pages as $page) {
                $pages_options[$page->ID] = $page->post_title;
            }
        }

        return $pages_options;
    }

}
endif;

new WeDevs_Settings_API_Test();