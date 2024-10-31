<?php
/**
 * Plugin Name: MyCausora Donation Widget
 * Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
 * Description: MyCausora is a free fundraising solution for non-profits and websites that want to support their favorite cause. MyCausora enables you to easily accept donations for a cause of your choice on your website, all while your users receive an equal amount of gift cards from over 150+ merchants for every dollar they donate (win-win-win).
 * Version: 0.1.0
 * Author: mybuddyhoward
 * Author URI: http://causora.com
 * License: A "Slug" license name e.g. GPL2
 */
class MyCausoraPlugin{
    /**
    * Internal, global storage for defaults
    */
    public static $default_atts;
    public function __construct() {
        add_shortcode('mycausora', array(&$this, 'mycausora_shortcode'));
        
        // set some defaults
        $options = get_option('mycausora_options');
        
        self::$default_atts = array(
                'affiliate_code' => (isset($options['affiliate_code']) && $options['affiliate_code'] != '') ? sanitize_text_field($options['affiliate_code']): 'causora',
                'charity' => (isset($options['charity'])) ? sanitize_text_field($options['charity']) : '0',
                'height' => (isset($options['size']) && $options['size'] == '600') ? sanitize_text_field($options['size']) : '250'
        );
    }
    
    public function mycausora_shortcode($atts){
        $shortcode_params = shortcode_atts(self::$default_atts, $atts);

        return $this->embed($shortcode_params);
    }
    public function embed($shortcode_params) {
        extract($shortcode_params);
        /* catch the echo output, so we can control where it appears in the text  */
        ob_start();
        $charityArray = explode("||",$charity);
        ?>
        <div id="mycausora-widget-placeholder">
                <a href="http://causora.com/charity/<?=$charityArray[1]?>/" target="_blank" title="Causora - Rewarding Donations">Donate Now On Causora And Get Rewards!</a>
        </div>
        <script id="causora-widget-src" type="text/javascript" src="//causora.com/mycausora-1.0.0-min.js"></script>
        <script type="text/javascript">
                MYCAUSORA.widget({
                    id:'mycausora-widget-placeholder',
                    affiliateCode: '<?=$affiliate_code?>',
                    charityId:'<?=$charityArray[0]?>',
                    height:<?=$height?>
                });
        </script>
        <?php
        return ob_get_clean();	//return the output (and stop the buffer)
    }
}
define("MYCAUSORA_BASENAME", plugin_basename(__FILE__) );
include_once('settings.php');
include_once('button.php');
$my_causora_plugin = new MyCausoraPlugin();