<?php
/********************** ADMIN SETTINGS */
add_action( 'admin_menu', 'eelv_share_ajout_menu');
function eelv_share_ajout_menu() {
	add_submenu_page('options-general.php',__('Post sharing', 'eelv-share-post' ), __('Post sharing', 'eelv-share-post' ), 7, 'eelv_share_configuration', 'eelv_share_configuration');
}
function eelv_share_configuration(){
  if( $_REQUEST[ 'type' ] == 'update' ) {    
      update_option( 'eelv_share_options', $_REQUEST['eelv_share_options'] );
	       
      ?>
      <div class="updated"><p><strong><?php _e('Options saved', 'eelv-share-post' ); ?></strong></p></div>
      <?php 
    }
   $eelv_share_options = get_option( 'eelv_share_options' );
  ?>  
        <div class="wrap">
        <div id="icon-edit" class="icon32"><br/></div>
        <h2><?=_e('Post sharing', 'eelv-share-post' )?></h2>
        
    <form name="typeSite" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">  
    <input type="hidden" name="type" value="update">
    
        
        <table class="widefat" style="margin-top: 1em;">
            <thead>
                <tr>
                  <th scope="col" colspan="2"><?= __( 'Default values', 'eelv-share-post' ) ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td width="30%">
                        <label for="eelv_share_options_s"><?=_e('Default values for text length to display :', 'eelv-share-post' )?></label>
                    </td><td>
                        <input  type="number" name="eelv_share_options[s]"  size="3"  id="eelv_share_options_s"  value="<?=$eelv_share_options['s']?>" />
                        <legend><?=_e('Can be modified for each post ex: http://eelv.fr?p=[$post_ID]&s=[$size] where $size is the number of words to display. Value 0 shows all the text, default is 55 words', 'eelv-share-post' )?></legend>
                   </td>
                 </tr>
             </tbody>
             <thead>
                <tr>
                  <th scope="col" colspan="2"><?= __( 'Configuration', 'eelv-share-post' ) ?></th>
                </tr>
            </thead>
            <tbody>
           		 <tr>
                    <td width="30%">
                        <label for="eelv_share_options_l"><?=_e('number of caracters to display in the loop :', 'eelv-share-post' )?></label>
                    </td><td>
                        <input  type="number" name="eelv_share_options[l]"  size="3"  id="eelv_share_options_l"  value="<?=$eelv_share_options['l']?>" />
                        <legend><?=_e('default is 400', 'eelv-share-post' )?></legend>
                   </td>
                 </tr>
                 <tr>
                    <td width="30%">
                        <label for="eelv_share_options_a"><?=_e('Open target post directly from the loop', 'eelv-share-post' )?></label>
                    </td><td>
                        <input  type="checkbox" name="eelv_share_options[a]"  size="60"  id="eelv_share_options_a"  <?php if(abs($eelv_share_options['a'])!=0){ echo'checked';} ?> value="1"/>
                   </td>
                 </tr>
                 <tr>
                    <td width="30%">
                        <label for="eelv_share_options_i"><?=_e('Display post thumbnail in the loop', 'eelv-share-post' )?></label>
                    </td><td>
                        <input  type="checkbox" name="eelv_share_options[i]"  size="60"  id="eelv_share_options_i"  <?php if(abs($eelv_share_options['i'])!=0){ echo'checked';} ?> value="1"/>
                   </td>
                 </tr>
                 <tr>
                    <td width="30%">
                        <label for="eelv_share_options_s"><?=_e('Display youtube links', 'eelv-share-post' )?></label>
                    </td><td>
                        <input  type="checkbox" name="eelv_share_options[y]"  size="60"  id="eelv_share_options_y"  <?php if(abs($eelv_share_options['y'])!=0){ echo'checked';} ?> value="1"/>
                   </td>
                 </tr>
                 <tr>
                    <td width="30%">
                        <label for="eelv_share_options_d"><?=_e('Display dailymotion links', 'eelv-share-post' )?></label>
                    </td><td>
                        <input  type="checkbox" name="eelv_share_options[d]"  size="60"  id="eelv_share_options_d"  <?php if(abs($eelv_share_options['d'])!=0){ echo'checked';} ?> value="1"/>
                   </td>
                 </tr>
                 <tr>
                    <td width="30%">
                        <label for="eelv_share_options_t"><?=_e('Display twitter links', 'eelv-share-post' )?></label>
                    </td><td>
                        <input  type="checkbox" name="eelv_share_options[t]"  size="60"  id="eelv_share_options_t"  <?php if(abs($eelv_share_options['t'])!=0){ echo'checked';} ?> value="1"/>
                   </td>
                 </tr>
               </tbody>
               <tfoot>  
                 <tr>
                    <td colspan="2">
                        <p class="submit">
                        <input type="submit" name="Submit" value="<?php _e('Save changes', 'eelv-share-post' ) ?>" />
                        </p>                    
                    </td>
                </tr>
            </tfoot>
        </table>
        
    </form>
    </div>
    
<?php
}



/********************** NETWORK ADMN SETTINGS */
add_action( 'network_admin_menu', 'eelv_share_ajout_network_menu');
function eelv_share_ajout_network_menu() {
  add_submenu_page('settings.php', __('Post sharing', 'eelv-share-post' ), __('Post sharing', 'eelv-share-post' ), 'Super Admin', 'eelv_share_network_configuration', 'eelv_share_network_configuration');   
}

function eelv_share_network_configuration(){
  if( $_REQUEST[ 'type' ] == 'update' ) {    
      update_site_option( 'eelv_share_domains', explode(',',str_replace(' ','',$_REQUEST['eelv_share_domains'])) );
	       
      ?>
      <div class="updated"><p><strong><?php _e('Options saved', 'eelv-share-post' ); ?></strong></p></div>
      <?php 
    }
   $eelv_share_domains = get_site_option( 'eelv_share_domains' );
  ?>  
        <div class="wrap">
        <div id="icon-edit" class="icon32"><br/></div>
        <h2><?=_e('Post sharing', 'eelv-share-post' )?></h2>
        
    <form name="typeSite" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">  
    <input type="hidden" name="type" value="update">
    
        
        <table class="widefat" style="margin-top: 1em;">
            <thead>
                <tr>
                  <th scope="col" colspan="2"><?= __( 'Configuration ', 'menu-config' ) ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td width="30%">
                        <label for="eelv_share_domains"><?=_e('Other domains to parse :', 'eelv-share-post' )?></label>
                    </td><td>
                        <input  type="text" name="eelv_share_domains"  size="60"  id="eelv_share_domains"  value="<?=implode(',',$eelv_share_domains)?>" class="wide">
                        <legend><?=_e('Use it for network domain mapping, comma separated values ex : eelv.fr,urbancube.fr', 'eelv-share-post' )?></legend>
                   </td>
                 </tr>
                     
                 <tr>
                    <td colspan="2">
                        <p class="submit">
                        <input type="submit" name="Submit" value="<?php _e('Save changes', 'eelv-share-post' ) ?>" />
                        </p>                    
                    </td>
                </tr>
            </tbody>
        </table>
        
    </form>
    </div>
    
<?php
}
