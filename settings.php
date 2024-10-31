<?php
class MyCausoraSettings
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
        add_filter( 'plugin_action_links_' . MYCAUSORA_BASENAME, array($this, 'add_plugin_settings_link'));
    }
  
    /**
     * Add a settings link to our plugin on the Plugins list page.
     * 
     * @param array $links An array with all the links for htat plugin.
     * @return array $links All the links including our new settingg link.
     */
    public function add_plugin_settings_link( $links ) {
        return array_merge(array('settings'=>'<a href="'. get_admin_url(null, 'options-general.php?page=MyCausora') .'">Settings</a>'), $links);
    } 
    
    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Settings Admin', 
            'MyCausora', 
            'manage_options', 
            'MyCausora', 
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'mycausora_options' );
        ?>
        <div class="wrap">
            <?php screen_icon(); ?>          
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'mycausora_options' );   
                do_settings_sections( 'mycausora-donation-form-settings' );
                submit_button(); 
            ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {        
        register_setting(
            'mycausora_options', // Option group
            'mycausora_options', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );
        
        /**Documentation Info**/
        add_settings_section(
          'mycausora-options-docs',
          __('How to Add a MyCausora Donation Widget to Your Website'),
          array($this, 'options_docs_text'),
          'mycausora-donation-form-settings'
        );

        add_settings_section(
            'mycausora_section_main', // ID
            'My Custom Settings', // Title
            array( $this, 'print_section_info' ), // Callback
            'mycausora-donation-form-settings' // Page
        );  

        add_settings_field(
            'affiliate_code', // ID
            'Affiliate Code', // Title 
            array( $this, 'affiliate_code_callback' ), // Callback
            'mycausora-donation-form-settings', // Page
            'mycausora_section_main' // Section           
        );
        
        add_settings_field(
            'charity', // ID
            'Charity', // Title 
            array( $this, 'charity_callback' ), // Callback
            'mycausora-donation-form-settings', // Page
            'mycausora_section_main' // Section           
        );
        
        add_settings_field(
            'size', 
            'Size', 
            array( $this, 'size_callback' ), 
            'mycausora-donation-form-settings', 
            'mycausora_section_main'
        );      
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();
        if( isset( $input['id_number'] ) )
            $new_input['id_number'] = absint( $input['id_number'] );

        if( isset( $input['title'] ) )
            $new_input['title'] = sanitize_text_field( $input['title'] );
        
        if( isset($input['affiliate_code']))
            $new_input['affiliate_code'] = sanitize_text_field($input['affiliate_code']);
        
        if( isset($input['charity']))
            $new_input['charity'] = $input['charity'];
        
        if( isset($input['size']))
            $new_input['size'] = sanitize_text_field($input['size']);

        return $new_input;
    }
    
    public function options_docs_text(){ ?>
    <p><?php _e('To add the <a href="https://causora.com/mycausora/" target="_blank">MyCausora Donation Widget</a> to your website follow these steps:'); ?></p>
    <ol>
      <li><?php _e('Complete the settings below to select what cause you want to support and the size of widget. If you want to enable reporting of donations from this widget, click on \'Get your own affiliate code here\' and follow steps to select cause, size, enter your information, and you will get your affiliate code. Enter the affiliate code below. You, then save your changes.'); ?></li>
      <li><?php _e('Go to the edit screen for the page or post where you want to add the MyCausors Donation Widget.'); ?></li>
      <li><?php _e('Place your cursor within the editor\'s text where you want the widget to be added.'); ?></li>
      <li><?php echo __('Click the Causora icon <img class="editor-icon" src="' .  plugins_url() . '/mycausora-donation-widget/images/toolbar_button.png" width="20px" height="20px" /> in the WordPress editor toolbar.'); ?></li>
      <li><?php _e('Click the "Publish" or "Update" button to save your changes and add the form to your live website.'); ?></li>
      <li><?php _e('You can also add MyCausora Donation Widget anywhere with the short code [mycausora]'); ?></li>
    </ol>
    <?php
    }

    /** 
     * Print the Section text
     */
    public function print_section_info()
    {
        print 'Enter your settings below: You can register to be a Causora affiliate <a href="http://causora.com/mycausora" target="_blank">here</a>';
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function id_number_callback()
    {
        printf(
            '<input type="text" id="id_number" name="mycausora_options[id_number]" value="%s" />',
            isset( $this->options['id_number'] ) ? esc_attr( $this->options['id_number']) : ''
        );
    }
    
    /** 
     * Get the settings option array and print one of its values
     */
    public function affiliate_code_callback()
    {
        printf(
            '<input type="text" id="affiliate_code" name="mycausora_options[affiliate_code]" value="%s" /> <a href="https://causora.com/mycausora/">Get your own affiliate code here</a>',
            isset( $this->options['affiliate_code'] ) ? esc_attr( $this->options['affiliate_code']) : 'causora'
        );
    }
    
    /** 
     * Get the settings option array and print one of its values
     */
    public function charity_callback()
    {
        $defaultValue = isset( $this->options['charity'] ) ? esc_attr( $this->options['charity']) : '';
        $charityInput = explode("||",$defaultValue);
        $response = json_decode($this->getUrl("https://causora.com/webservices/?post=charity&fields=name"));
        $options = '<option value="">-------</option>';
        foreach($response as $charity){
            $selected = ($charityInput[0] == $charity->id) ? " selected":"";
            //$value = '{"id":"'.$charity->id.'", "slug":"'.$charity->slug.'"}';
            $value = $charity->id."||".$charity->slug."]";
            $options.= '<option value="'. htmlentities($value).'"'.$selected.'>'.$charity->name.'</option>';
        }
        printf(
            '<select id="charity" name="mycausora_options[charity]">%s</select>',
            $options
        );
    }
    
    /** 
     * Get the settings option array and print one of its values
     */
    public function size_callback()
    {
        $sizes = array("250"=>"300x250", "600"=>"300x600");
        $defaultValue = isset( $this->options['size'] ) ? esc_attr( $this->options['size']) : 250;
        $options = '';
        
        foreach($sizes as $key=>$value){
            $selected = ($defaultValue == $key) ? " selected":"";
            $options.= '<option value="'.$key.'"'.$selected.'>'.$value.'</option>';
        }
        printf(
            '<select id="size" name="mycausora_options[size]">%s</select>',
            $options
        );
        
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function title_callback()
    {
        printf(
            '<input type="text" id="title" name="mycausora_options[title]" value="%s" />',
            isset( $this->options['title'] ) ? esc_attr( $this->options['title']) : ''
        );
    }
    
    protected function getUrl($url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true );
        // This is what solved the issue (Accepting gzip encoding)
        curl_setopt($ch, CURLOPT_ENCODING, "gzip,deflate");     
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
}

if( is_admin() )
    $my_causora_settings = new MyCausoraSettings();