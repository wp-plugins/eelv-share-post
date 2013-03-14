<?php
/*
Plugin Name: EELV Share Post 
Plugin URI: http://ecolosites.eelv.fr/eelv-share-post/
Description: Share a post link from a blog to another blog on the same WP multisite network and include the post content !
Version: 0.4.0
Author: bastho, n4thaniel // EELV
Author URI: http://ecolosites.eelv.fr/
License: CC BY-NC 3.0
*/

add_action( 'wp_head', 'eelv_share_css' );
add_action( 'admin_head', 'eelv_share_css' );
function eelv_share_css(){
	echo '<link type="text/css" rel="stylesheet" href="' . WP_PLUGIN_URL . '/eelv-share-post/share.css" />' . "\n";
}

add_action( 'init', 'eelv_mk_share' );
function eelv_mk_share(){
	load_plugin_textdomain( 'eelv-share-post', false, 'eelv-share-post/languages' );
	
	require( dirname( __FILE__ ) . '/htmlparser.php' );
	require( dirname( __FILE__ ) . '/func.php' );
	require( dirname( __FILE__ ) . '/admin.php' );
	

/* IN THE LOOP */		
		
	remove_filter('get_the_excerpt', 'wp_trim_excerpt');
	remove_filter('get_the_excerpt', 'wp_trim_excerpt',100);  
	//add_filter('get_the_excerpt', 'new_wp_trim_excerpt'); 	
	
	add_filter('the_excerpt','eelv_embed_exerpt',999);
	//add_filter('excerpt_more','eelv_embed_exerpt');
	add_filter('the_content_rss','eelv_embed_exerpt');
	  
	function eelv_embed_exerpt($excerpt){
	  $eelv_share_domains=eelv_share_get_domains();
	  $sharer=false;
	  $tumb='';
	  $original_thumbnail=has_post_thumbnail();
	  
	  
   	  $eelv_share_options = get_option( 'eelv_share_options');
	  if(!isset($eelv_share_options['l']) || $eelv_share_options['l']==0 || empty($eelv_share_options['l'])){
		$eelv_share_options['l']=400;  
	  }
	  
	  $domains_to_parse=array_keys($eelv_share_domains);

	  // INTERNAL LINKS REFERENCES
	  preg_match_all('#http://([a-zA-Z0-9\-\.]+)?('.str_replace('.','\.',implode('|',$domains_to_parse)).')/\?p=(\d+)([a-zA-Z0-9=;\&]+)?[\n\t\s ]#i',$excerpt,$out, PREG_PATTERN_ORDER);
	  if(is_array($out)){
		  
		$sharer=true;
		$thumb_output=false;
		foreach($out[0] as $id=>$match){
		  $blogname = substr($out[1][$id],0,-1);
		  $domain = $out[2][$id];
		  $postid = $out[3][$id];
		  parse_str( $out[4][$id],$vars);
		  
		  if(isset($eelv_share_domains[$domain]) && $domain!=$eelv_share_domains[$domain]){
			$blogname= $eelv_share_domains[$domain];
		  }
		  if(empty($blogname)){  
			$blogname='www';
			$blog = get_blog_details($domain);
			$blog_post = get_blog_post( 1, $postid );
		  }
		  else{
			$blog = get_blog_details($blogname);    
			$blog_post = get_blog_post( $blog->blog_id, $postid );
		  }
		 
		  
		  // THUMBNAIL
		  if(isset($eelv_share_options['i']) && $eelv_share_options['i']==1){
			  if( $original_thumbnail==false && $thumb_output==false){
			   if(false !== $image = get_blog_post_thumbnail($blog->blog_id,$blog_post->ID)){
				$tumb.='<img src="'.$image->guid.'" alt="'.$image->post_name.'" class="embeelv_img"/>';
				$thumb_output=true;
			   }
			  }
		  }
		  $content = substr(strip_tags($blog_post->post_content),0,$eelv_share_options['l']).'...';
		  $val="<div class='embeelv_excerpt'><p>";
		  if(isset($eelv_share_options['a']) && $eelv_share_options['a']==1){
			  $val.='<a href="'.$blog_post->guid.'" target="_blank"class="embeelv_direct_blank">'.$content.'</a>';
		  }
		  else{
		  	$val.=$content.'<a href="'.$blog_post->guid.'" target="_blank" class="embeelv_blank"><span>&raquo;</span></a>';
		  }
		  $val.="</p></div>";
		  $excerpt=str_replace($match,$val,$excerpt); 
		}
	  } 
	  
	  // YOUTUBE
	  if(isset($eelv_share_options['y']) && $eelv_share_options['y']==1){
		   $tumb.=eelv_share_parse_youtube($excerpt);
	  }
	  
	  // DAILYMOTION
	  if(isset($eelv_share_options['d']) && $eelv_share_options['d']==1){
		  $tumb.=eelv_share_parse_dailymotion($excerpt);
	  }	  
	  
	  // TWITTER
	  if(isset($eelv_share_options['t']) && $eelv_share_options['t']==1){
		  $excerpt=eelv_share_parse_twitter($excerpt);
	  }
	  
	  
	  if($sharer==false){
		/*$excerpt.="<a href=\"var d=document,w=window,e=w.getSelection,k=d.getSelection,x=d.selection,s=(e?e():(k)?k():(x?x.createRange().text:0)),f='".$blogurl."/wp-admin/press-this.php',l=d.location,e=encodeURIComponent,u=f+'?u=&t=".$post->post_title."&s=".$post->guid."&v=4';a=function(){if(!w.open(u,'t','toolbar=0,resizable=1,scrollbars=1,status=1,width=720,height=570'));};if (/Firefox/.test(navigator.userAgent)) setTimeout(a, 0); else a();void(0)\">#</a>"; */
	  }
	  return $tumb.$excerpt;	  
	} 
	
	
	
	
	
/* SINGLE PAGE */	
	$eelv_share_domains=eelv_share_get_domains();  
	

	foreach($eelv_share_domains as $domain=>$internal_domain){	
		$id_domain = str_replace(array('.','-'),'',$domain);
		wp_embed_register_handler( 'embedInMultiSite_'.$id_domain.'_p', '/<p[^>]*>http:\/\/(.+)?('.str_replace('.','\.',$domain).')\/\?p=(\d+)(\&.+)?<\/p>/i', 'eelv_embed_locals');
		wp_embed_register_handler( 'embedInMultiSite_'.$id_domain, '#http://(.+)?('.str_replace('.','\.',$domain).')/\?p=(\d+)(\&.+)?#i', 'eelv_embed_locals' );
	}
	
	function eelv_embed_locals( $matches, $attr, $url, $rawattr ) {
	   
	  
	  // init values
	  $subdomain=substr($matches[1],0,-1);
	  $domain=$matches[2];
	  $postid=$matches[3];
	  parse_str($matches[4],$vars);
	  $eelv_share_options = get_option( 'eelv_share_options');
	  $eelv_share_domains=eelv_share_get_domains();
	  // init vars
	  $max_words=55;
	  if(isset($vars['s']) && is_numeric($vars['s'])){
			$max_words=$vars['s'];  
	  }
	  
	  
	  if(isset($eelv_share_domains[$domain]) && $domain!=$eelv_share_domains[$domain]){
	  	$subdomain= $eelv_share_domains[$domain];
	  }
	  
	  
	  if(empty($subdomain)){
		//$subdomain=$domain;    
		$subdomain='www';
		$blog = get_blog_details($domain);
		$blog_post = get_blog_post( 1, $postid );
	  }
	  else{
		$blog = get_blog_details($subdomain);    
		$blog_post = get_blog_post( $blog->blog_id, $postid );
	  }
	 
	  global $it;
	  $it=abs($it);
	  $js_var='str_'.$postid.'_'.$it.'_'.str_replace(array('-','.'),'',$subdomain);
	  $it++;  
	  
	  $embed='<div class="embeelv">';
	  $embed.='<a href="'.$blog_post->guid.'" target="_blank" id="'.$js_var.'">';
	  $embed.=$blog_post->post_name;
	  $embed.='</a></div>';
	  $embed.='<script>var '.$js_var.'="';
	  if(is_object($blog_post)){
		if(false !== $image = get_blog_post_thumbnail($blog->blog_id,$blog_post->ID)){
		  $embed.='[img src=\"'.$image->guid.'\" alt=\"'.$image->post_name.'\"/]';
		}
		$embed.='[h4]';
		$embed.=trim(str_replace('"','\"',$blog_post->post_title));
		$embed.='[/h4][p]';
		
		$w=0;
		$txt = trim(strip_tags($blog_post->post_content));
		$txts=preg_split('/\s/',$txt);
		
		$emtxt='';
		foreach($txts as $str){
			$str=str_replace('"','\"',trim($str));	
			$w++;
			$emtxt.=$str.' ';
			if($max_words>0 && $w>$max_words) break;
		}
		// YOUTUBE
		  if(isset($eelv_share_options['y']) && $eelv_share_options['y']==1){
			   $emtxt=eelv_share_untag(eelv_share_parse_youtube($emtxt,true));
		  }
		  
		  // DAILYMOTION
		  if(isset($eelv_share_options['d']) && $eelv_share_options['d']==1){
			  $emtxt=eelv_share_untag(eelv_share_parse_dailymotion($emtxt,true));
		  }	  
		  
		  // TWITTER
		  if(isset($eelv_share_options['t']) && $eelv_share_options['t']==1){
			  $emtxt=eelv_share_untag(eelv_share_parse_twitter($emtxt));
		  }
		if(sizeof($txts)>$w) $emtxt.='...';
		$embed.=$emtxt;
		
		$embed.='[/p]';
		if(sizeof($txts)>$w) $embed.='[p][u]'.__('&raquo; Read full post','eelv-share-post').'[/u]';
		$embed.='[div style=\"clear:both\"][/div][/p]';
	  }
	  else{
		$embed.='[h4 class=\"nondispo\"]'.__('This post isn\'t avaible any more','eelv-share-post').'[/h4]';
	  }
	  $embed.='";';
	  $embed.='while('.$js_var.'.indexOf("[") != -1){'.$js_var.' = '.$js_var.'.replace("[","<");}';
	  $embed.='while ('.$js_var.'.indexOf("]") != -1){'.$js_var.' = '.$js_var.'.replace("]",">");}';
	  $embed.='document.getElementById("'.$js_var.'").innerHTML='.$js_var.'; setTimeout(function(){document.getElementById("wp-admin-bar-embed_post_menu").style.display="none";},2000);</script>';
	  return $embed;
	}

}



/************************** SHARING ACTIONS ***/

add_action( 'add_meta_boxes', 'eelv_share_add_custom_box' );
function eelv_share_add_custom_box() {
add_meta_box( 
      'eelv_share_from_admin',
      __( "Share on", 'eelv-share-post' ),
      'eelv_share_from_admin',
      'post',
      'side' 
    );
}
function eelv_share_from_admin(){
  $user_id = get_current_user_id(); 
  $cb=get_current_blog_id();
  $user_blogs = get_blogs_of_user( $user_id ); 
  $shared_on=get_post_meta(get_the_ID(),'share_on_blog',true);
  wp_nonce_field( 'eelv_share_post_via_admin', 'eelv_share_post_via_admin');
  //print_r($shared_on);
  foreach ($user_blogs as $user_blog) { if($user_blog->userblog_id != $cb){
	   ?><p>
       <label for="share_on_blog_<?=$user_blog->userblog_id?>">
       	<input type="checkbox" name="share_on_blog[<?=$user_blog->userblog_id?>]" id="share_on_blog_<?=$user_blog->userblog_id?>" <?php  if(isset($shared_on[$user_blog->userblog_id])){ echo' checked="checked" value="'.$shared_on[$user_blog->userblog_id].'"';}else{ echo' value=""';} ?>/> <?=$user_blog->blogname?>
       </label>
       
       <select name="share_on_blog_cat_<?=$user_blog->userblog_id?>[]" multiple>
       <?php switch_to_blog($user_blog->userblog_id); 
	   	$cats = get_categories();
		$dogs = array();
		if(isset($shared_on[$user_blog->userblog_id])){
			 $dogs=wp_get_post_categories($shared_on[$user_blog->userblog_id],array('hide_empty'=>false));
		}
		foreach($cats as $cat){ ?>
       	<option value="<?=$cat->term_id?>" <?php if(in_array($cat->term_id,$dogs)){ echo'selected';} ?>><?=$cat->cat_name?></option>
       <?php  } switch_to_blog($cb); ?>
       </select>
       </p>
       <?php
	  }}
	  ?>
      <script>
	  jQuery(document).ready(function(e) {
        jQuery('#eelv_share_from_admin select').hide();
        jQuery('#eelv_share_from_admin input[type=checkbox]:checked').parent().parent().children('select').show();
        jQuery('#eelv_share_from_admin input').change(function(){
			if(jQuery(this).is(':checked')==true){
				jQuery(this).parent().parent().children('select').show().animate({height:100});
			}
			else{
				jQuery(this).parent().parent().children('select').animate({height:0});
			}
		});
	  });
	  </script>
      <?php
}
add_action( 'admin_head', 'eelv_share_admin_head');
function eelv_share_admin_head(){
	if ($_SERVER['PHP_SELF']=='/wp-admin/press-this.php') {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )      return;	
		$post_id=$_REQUEST['post_id'];
		if ( wp_is_post_revision( $post_id ) ) return;
		
		if(!empty($_REQUEST['content'])){			
	  		$eelv_share_domains=eelv_share_get_domains();
	        $domains_to_parse=array_keys($eelv_share_domains);
			preg_match_all('#http://([a-zA-Z0-9\-\.]+)?('.str_replace('.','\.',implode('|',$domains_to_parse)).')/\?p=(\d+)([a-zA-Z0-9=;\&]+)?[\n\t\s ]#i',$_REQUEST['content'].' ',$out, PREG_PATTERN_ORDER);
		  if(is_array($out)){
			$cb=get_current_blog_id();
			foreach($out[0] as $id=>$match){
			  $blogname = substr($out[1][$id],0,-1);
			  $domain = $out[2][$id];
			  $postid = $out[3][$id];
			  
			  if(isset($eelv_share_domains[$domain]) && $domain!=$eelv_share_domains[$domain]){
				$blogname= $eelv_share_domains[$domain];
			  }
			  if(empty($blogname)){  
				$blogname='www';
				$blog = get_blog_details($domain);
				$blog_post = get_blog_post( 1, $postid );
			  }
			  else{
				$blog = get_blog_details($blogname);    
				$blog_post = get_blog_post( $blog->blog_id, $postid );
			  }
			  switch_to_blog($blog->blog_id);
			  $share_tmp=$shared_on=get_post_meta($postid,'share_on_blog',true);
			  $share_tmp[$cb]=$post_id;
			  update_post_meta($postid,'share_on_blog',$share_tmp,$shared_on);
			  switch_to_blog($cb);
			}
		  }
		}
	}
}
		
add_action( 'save_post', 'eelv_share_save_postdata' );
function eelv_share_save_postdata($post_id){
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )      return;
	if ( wp_is_post_revision( $post_id ) ) return;
	if(!wp_verify_nonce($_POST['eelv_share_post_via_admin'],'eelv_share_post_via_admin')) return;
	 
	remove_action( 'save_post', 'eelv_share_save_postdata' );
	
	$shared_on=get_post_meta($post_id,'share_on_blog',true);
	$share_on=$_REQUEST['share_on_blog'];
	$share_tmp=array();
	if(!is_array($shared_on)) $shared_on=array($shared_on);
	if(!is_array($share_on)) $share_on=array($share_on);
	
	
	$user_id = get_current_user_id(); 
	$cb=get_current_blog_id();
	$user_blogs = get_blogs_of_user( $user_id ); 
	
	foreach ($user_blogs as $user_blog) { if($user_blog->userblog_id != $cb){
		$link=eelv_share_mk_link($user_blog->userblog_id,$post_id,false);		
		$share_on_blog_cat=$_REQUEST['share_on_blog_cat_'.$user_blog->userblog_id];
		switch_to_blog($user_blog->userblog_id);
		// blog added to list
		if(!array_key_exists($user_blog->userblog_id,$shared_on) && array_key_exists($user_blog->userblog_id,$share_on)){
			
			if(0 !== $publication = wp_insert_post( array('post_type'=>get_post_type(),'post_title' => get_the_title(),  'post_content' => $link,  'post_status' => get_post_status()))){
                 $share_tmp[$user_blog->userblog_id]=$publication;
				 wp_set_post_categories( $publication, $share_on_blog_cat );
             }
		}		
		// blog removed from list
		elseif(array_key_exists($user_blog->userblog_id,$shared_on) && !array_key_exists($user_blog->userblog_id,$share_on)){			
			wp_delete_post($shared_on[$user_blog->userblog_id],true);			
		}
		// blog stay in list
		elseif(array_key_exists($user_blog->userblog_id,$shared_on) && array_key_exists($user_blog->userblog_id,$share_on)){
			$share_tmp[$user_blog->userblog_id]=$shared_on[$user_blog->userblog_id];
			wp_set_post_categories( $shared_on[$user_blog->userblog_id], $share_on_blog_cat );
		}
		// blog really unwanted
		else{
			//nothing to do	
		}
		switch_to_blog($cb);
	}}
	add_action( 'save_post', 'eelv_share_save_postdata' );
	update_post_meta($post_id,'share_on_blog',$share_tmp,$shared_on);
}

add_action( 'admin_bar_menu', 'eelv_embed_post', 999 );
function eelv_embed_post( $wp_admin_bar ) {
  if(is_single()){
    // add a parent item
    $args = array('id' => 'embed_post_menu', 'title' => '<span class="ab-icon"></span> <span class="ab-label">'.__('Share on','eelv-share-post').'</span>'); 
	$wp_admin_bar->add_node($args);
  
  $n=0;
  $user_id = get_current_user_id(); 
  $cb=get_current_blog_id();
  $user_blogs = get_blogs_of_user( $user_id ); 
  // add a child item to a our parent item
	  foreach ($user_blogs as $user_blog) {
	   $wp_admin_bar->add_node($args);
		$args = array(
			'id' => $n, 
			'title' => $user_blog->blogname, 
			'parent' => 'embed_post_menu', 
			'target'=>'_blank',
			'href'=> 'http://'.$user_blog->domain.'/wp-admin/press-this.php?u=&t='.urlencode(get_the_title()).'&s=%20%20%0A%0A'.eelv_share_mk_link($user_blog->blog_id,get_the_ID()).'%0A&v=4' 
		); 
	   
		$wp_admin_bar->add_node($args);	
		$n++; 
	  }      
    
  }
}

function eelv_share_load_js() {
	wp_enqueue_script(
		'share',
		plugins_url('/share.js', __FILE__),
		array('jquery'),
		false,
		false
	);
}    
 
add_action('wp_enqueue_scripts', 'eelv_share_load_js');


function eelv_share_mk_link($blog_dest,$post_id,$encode=true){
	$blog_id=get_current_blog_id();
	$blog_details = get_blog_details($blog_id);
	$domain=$blog_details->siteurl;
	$tmp_blog_id=get_current_blog_id();
	
	$link=$domain.'/?p='.$post_id;
	
	$sep='&';
	if($encode) $sep='%26amp;';
	switch_to_blog($blog_dest);
		$eelv_share_options = get_option('eelv_share_options');
	switch_to_blog($tmp_blog_id);
	if(isset($eelv_share_options['s']) && is_numeric($eelv_share_options['s'])){
		$link.=$sep.'s='.$eelv_share_options['s'];  
	}
	return $link;
}

// obsolete function
//add_action( 'wp_footer', 'eelv_share_on_page', 999 );
/*
function eelv_share_on_page(){
	$sharecontent=$_REQUEST['sharecontent'];
	if(isset($sharecontent)){
		$title=get_the_title();
		
		$site=$_REQUEST['site'];
		$blog_dest=$_REQUEST['blog'];
		$post_id=get_the_ID();
		
		$link=eelv_share_mk_link($blog_dest,$post_id);
	echo '<script>';	
	echo"var d=document,w=window,e=w.getSelection,k=d.getSelection,x=d.selection,s=(e?e():(k)?k():(x?x.createRange().text:0)),f='http://".$site."/wp-admin/press-this.php',l=d.location,e=encodeURIComponent,u=f+'?u=&t=".str_replace(array("&rsquo;",'"'),array("\\'",''),$title)."&s=%20%20%0A%0A".$link."%0A&v=4';a=function(){if(!w.open(u,'t','toolbar=0,resizable=1,scrollbars=1,status=1,width=720,height=570'));};if (/Firefox/.test(navigator.userAgent)) setTimeout(a, 0); else a();void(0)";  
   echo'</script>';
  }	
}*/
