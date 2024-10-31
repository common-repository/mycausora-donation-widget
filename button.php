<?php
class MyCausoraButton
{
    /**
     * Start up
     */
    public function __construct()
    {
        // init process for registering our button
        add_action('init', array($this, 'mycausora_shortcode_button_init'));
    }
    
    
    public function mycausora_shortcode_button_init() {

        //Abort early if the user will never see TinyMCE
        if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') && get_user_option('rich_editing') == 'true')
             return;

        //Add a callback to regiser our tinymce plugin   
        add_filter("mce_external_plugins", array($this, "mycausora_register_tinymce_plugin")); 

        // Add a callback to add our button to the TinyMCE toolbar
        add_filter('mce_buttons', array($this,'mycausora_add_tinymce_button'));
    }
    
    //This callback registers our plug-in
    public function mycausora_register_tinymce_plugin($plugin_array) {
        $plugin_array['mycausora'] =  plugins_url('js/button.js', __FILE__);
        return $plugin_array;
    }
    //This callback adds our button to the toolbar
    public function mycausora_add_tinymce_button($buttons) {
        //Add the button ID to the $button array
        $buttons[] = "mycausora";
        return $buttons;
    }
}

if( is_admin() )
    $my_causora_button = new MyCausoraButton();