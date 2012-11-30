<?php
/*
Plugin Name: EELV Share Post 
Plugin URI: http://ecolosites.eelv.fr/eelv-share-post/
Description: Share a post link from a blog to another blog on the same WP multisite network and include the post content !
Version: 0.2.2
Author: bastho, n4thaniel // EELV
Author URI: http://ecolosites.eelv.fr/
License: CC BY-NC 3.0
*/

add_action( 'wp_head', 'eelv_share_css' );
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
	  preg_match_all('#http://([a-zA-Z0-9\-\.]+)?('.str_replace('.','\.',implode('|',$domains_to_parse)).')/\?p=(\d+)(\&[a-zA-Z0-9=\&]+)?[\n\t\s ]#i',$excerpt,$out, PREG_PATTERN_ORDER); 
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
		
		$embed.='[/p][p][u]'.$blog_post->guid.'[/u][div style=\"clear:both\"][/div][/p]';
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
add_action( 'admin_bar_menu', 'eelv_embed_post', 999 );
function eelv_embed_post( $wp_admin_bar ) {
  if(is_single()){
    // add a parent item
    $args = array('id' => 'embed_post_menu', 'title' => '<span class="ab-icon"></span> <span class="ab-label">'.__('Share on','eelv-share-post').'</span>'); 
	$wp_admin_bar->add_node($args);
  
  
  $user_id = get_current_user_id(); 
  $cb=get_current_blog_id();
  $user_blogs = get_blogs_of_user( $user_id ); 
  // add a child item to a our parent item
	  foreach ($user_blogs as $user_blog) {
	   $wp_admin_bar->add_node($args);
		$html="<a class='ab-item' onclick=\"var d=document,w=window,e=w.getSelection,k=d.getSelection,x=d.selection,s=(e?e():(k)?k():(x?x.createRange().text:0)),f='http://".$user_blog->domain ."/wp-admin/press-this.php',l=d.location,e=encodeURIComponent,u=f+'?u=&t=".get_the_title()."&s=".wp_get_shortlink()."&v=4';a=function(){if(!w.open(u,'t','toolbar=0,resizable=1,scrollbars=1,status=1,width=720,height=570'));};if (/Firefox/.test(navigator.userAgent)) setTimeout(a, 10); else a();void(0)\">".$user_blog->blogname."</a>";
		$args = array('html'=>$html,'id' => $n, 'title' => $user_blog->blogname, 'parent' => 'embed_post_menu', 'href'=> '?sharecontent=yes&site='.$user_blog->domain.'&blog='.$user_blog->userblog_id ); 
	   
		$wp_admin_bar->add_node($args);	
		$n++; 
	  }      
    
  }
}
 
 add_action( 'wp_footer', 'eelv_share_on_page', 999 );
function eelv_share_on_page(){
	$sharecontent=$_REQUEST['sharecontent'];
	if(isset($sharecontent)){
		$title=get_the_title();
		
		$site=$_REQUEST['site'];
		$blog_dest=$_REQUEST['blog'];
		$blog_id=get_current_blog_id();
		$blog_details = get_blog_details($blog_id);

		$domain=$blog_details->siteurl;
		
		$post_id=get_the_ID();
		
		$link=$domain.'/?p='.$post_id;
		
		$tmp_blog_id=get_current_blog_id();
		switch_to_blog($blog_dest);
			$eelv_share_options = get_option('eelv_share_options');
		switch_to_blog($tmp_blog_id);
		if(isset($eelv_share_options['s']) && is_numeric($eelv_share_options['s'])){
			$link.='%26s='.$eelv_share_options['s'];  
		 }
	echo '<script>';	
	echo"var d=document,w=window,e=w.getSelection,k=d.getSelection,x=d.selection,s=(e?e():(k)?k():(x?x.createRange().text:0)),f='http://".$site."/wp-admin/press-this.php',l=d.location,e=encodeURIComponent,u=f+'?u=&t=".str_replace(array("&rsquo;"),array("\\'"),$title)."&s=%20%20%0A%0A".$link."%0A&v=4';a=function(){if(!w.open(u,'t','toolbar=0,resizable=1,scrollbars=1,status=1,width=720,height=570'));};if (/Firefox/.test(navigator.userAgent)) setTimeout(a, 0); else a();void(0)";  
   echo'</script>';
  }	
}
