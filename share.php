<?php
/*
Plugin Name: EELV Share Post 
Plugin URI: http://ecolosites.eelv.fr/eelv-share-post/
Description: Share a post link from a blog to another blog on the same WP multisite network and include the post content !
Version: 0.1.4
Author: bastho, n4thaniel // EELV
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
	  
	function get_blog_post_thumbnail($blog_id,$post_id){
		global $wpdb;
	  $base = $wpdb->base_prefix."".$blog_id."_posts";
	  $basemeta = $wpdb->base_prefix."".$blog_id."_postmeta";
	  if($matches[1]=='www'){
		$base = $wpdb->base_prefix."posts";
	  	$basemeta = $wpdb->base_prefix."postmeta";
	  }
	  
	  $querymeta="
	  SELECT `meta_value` FROM ".$basemeta." 
	  WHERE post_id = ".$post_id."
	  AND meta_key = '_thumbnail_id'
		LIMIT 0,1
	  ";
	   $thumb_query_id=$wpdb->get_row($querymeta);
		if(is_object($thumb_query_id)){
		  $query="
		  SELECT `guid`,`post_title` FROM ".$base." 
		  WHERE ID = ".$thumb_query_id->meta_value;
		   $thumb_query=$wpdb->get_row($query);
			if(is_object($thumb_query)){
			  return $thumb_query;
			}
		}
		return false;
	  }
	  function new_wp_trim_excerpt($text) {  
		$raw_excerpt = $text;  
		if ( '' == $text ) {  
			$text = get_the_content('');  
	  
			$text = strip_shortcodes( $text );  
	  
			$text = apply_filters('the_content', $text);  
			$text = str_replace(']]>', ']]>', $text);  
			$text = strip_tags($text, '<iframe>');  
			$excerpt_length = apply_filters('excerpt_length', 55);  
	  
			$excerpt_more = apply_filters('excerpt_more', ' ' . '[...]');  
			$words = preg_split('/(<a.*?a>)|\n|\r|\t|\s/', $text, $excerpt_length + 1, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE );  
			if ( count($words) > $excerpt_length ) {  
				array_pop($words);  
				$text = implode(' ', $words);  
				$text = $text . $excerpt_more;  
			} else {  
				$text = implode(' ', $words);  
			}  
		}  
		return apply_filters('new_wp_trim_excerpt', $text, $raw_excerpt);  
	  
	}  
	
/* IN THE LOOP */	
	
		
	remove_filter('get_the_excerpt', 'wp_trim_excerpt');
	remove_filter('get_the_excerpt', 'wp_trim_excerpt',100);  
	//add_filter('get_the_excerpt', 'new_wp_trim_excerpt'); 
	  
	  $serv = str_replace('.','\.',DOMAIN_CURRENT_SITE);
	  
	add_filter('the_excerpt','eelv_embed_exerpt',999);
	//add_filter('excerpt_more','eelv_embed_exerpt');
	add_filter('the_content_rss','eelv_embed_exerpt');
	  
	function eelv_embed_exerpt($excerpt){
	  global $serv;
	  $serv = str_replace('.','\.',DOMAIN_CURRENT_SITE);
	  $sharer=false;
	  $tumb='';
	  $original_thumbnail=has_post_thumbnail();
	  // INTERNAL LINKS
	  preg_match_all('#<a href="http://(.+)?'.$serv.'/\?p=(\d+)">(.+)?</a>#i',$excerpt,$out, PREG_PATTERN_ORDER); 
	  if(is_array($out)){
		$sharer=true;
		$thumb_output=false;
		foreach($out[0] as $id=>$match){
		  $blogname = str_replace('.','',$out[1][$id]);
		  $postid = $out[2][$id];
		  if(empty($blogname)){
			//$matches[1]=$matches[2];    
			$blogname='www';
			$blog = get_blog_details(1);
			$blog_post = get_blog_post( 1, $postid );
		  }
		  else{
			$blog = get_blog_details($blogname);    
			$blog_post = get_blog_post( $blog->blog_id, $postid );
		  }
		  $link='<a href="'.$blog_post->guid.'" target="_blank" class="embeelv_blank"><span>&raquo;</span></a>';
		  if( $original_thumbnail==false && $thumb_output==false){
			  if(false !== $image = get_blog_post_thumbnail($blog->blog_id,$blog_post->ID)){
				  $tumb.='<img src="'.$image->guid.'" alt="'.$image->post_name.'" class="embeelv_img"/>';
				  $thumb_output=true;
			  }
		  }
		//<h4>&laquo;".$blog_post->post_title."&raquo;</h4>
		  $val="<div class='embeelv_excerpt'><p>".substr(strip_tags($blog_post->post_content),0,400)."...".$link."</p></div>";
		  $excerpt=str_replace($match,$val,$excerpt); 
		}
	  }
	  
	  // INTERNAL LINKS REFERENCES
	  preg_match_all('#http://(.+)?'.$serv.'/\?p=(\d+)#i',$excerpt,$out, PREG_PATTERN_ORDER); 
	  if(is_array($out)){
		$sharer=true;
		$thumb_output=false;
		foreach($out[0] as $id=>$match){
		  $blogname = str_replace('.','',$out[1][$id]);
		  $postid = $out[2][$id];
		  if(empty($blogname)){
			//$matches[1]=$matches[2];    
			$blogname='www';
			$blog = get_blog_details(1);
			$blog_post = get_blog_post( 1, $postid );
		  }
		  else{
			$blog = get_blog_details($blogname);    
			$blog_post = get_blog_post( $blog->blog_id, $postid );
		  }
		 
		  $link='<a href="'.$blog_post->guid.'" target="_blank" class="embeelv_blank"><span>&raquo;</span></a>';
		  if( $original_thumbnail==false && $thumb_output==false){
		   if(false !== $image = get_blog_post_thumbnail($blog->blog_id,$blog_post->ID)){
		  	$tumb.='<img src="'.$image->guid.'" alt="'.$image->post_name.'" class="embeelv_img"/>';
			$thumb_output=true;
		   }
		  }
		//<h4>&laquo;".$blog_post->post_title."&raquo;</h4>
		  $val="<div class='embeelv_excerpt'><p>".substr(strip_tags($blog_post->post_content),0,400)."...".$link."</p></div>";
		  $excerpt=str_replace($match,$val,$excerpt); 
		}
	  } 
	  
	  // YOUTUBE
	   preg_match_all('#[\n\t\r\s]http://www\.youtube\.com/watch\?v=(.+)\&?(.+)?[\n\t\r\s]#i',$excerpt,$yout, PREG_PATTERN_ORDER); 
	  if(is_array($yout)){
		foreach($yout[0] as $id=>$match){
		  
		  $val="<iframe class='embeelv_iframe' src='http://www.youtube.com/embed/".$yout[1][$id]."' width='250' height='150'>video</iframe>";
		  $tumb.= $val;
		  $excerpt=str_replace($match,strip_tags($val),$excerpt);      
		}
	  }
	  
	  // DAILYMOTION
	  preg_match_all('#[\n\t\r\s]http://www\.dailymotion\.com/video/(.+)_?(.+)?[\n\t\r\s]#i',$excerpt,$dail, PREG_PATTERN_ORDER); 
	  if(is_array($dail)){
		foreach($dail[0] as $id=>$match){      
		  $val="<iframe class='embeelv_iframe' src='http://www.dailymotion.com/embed/video/".$dail[1][$id]."' width='250' height='150'>video</iframe>";
		  $tumb.= $val;
		  $excerpt=str_replace($match,strip_tags($val),$excerpt);      
		}
	  }
	  
	  
	  // TWITTER
	  preg_match_all('#[\n\t\r\s]https?://twitter\.com/(.+)/status/(.+)[\n\t\r\s]#i',$excerpt,$twi, PREG_PATTERN_ORDER); 
	  if(is_array($twi)){
		foreach($twi[0] as $id=>$match){ 
		  
		  $twit = json_decode(file_get_contents('https://api.twitter.com/1/statuses/oembed.json?id='.$twi[2][$id].'&omit_script=true&hide_media=true&hide_thread=true&lang=fr'));
		 $parser = new htmlParser($twit->html);
	$twitxt = $parser->toArray();
		  $val="<div class='embeelv_twit'>@".$twi[1][$id]." &laquo;".$twitxt[0]['innerHTML']."&raquo;</div>";
		  $excerpt=str_replace($match,$val,$excerpt);      
		}
	  }
	  
	  
	  if($sharer==false){
		//$excerpt.="<a href=\"var d=document,w=window,e=w.getSelection,k=d.getSelection,x=d.selection,s=(e?e():(k)?k():(x?x.createRange().text:0)),f='".$blogurl."/wp-admin/press-this.php',l=d.location,e=encodeURIComponent,u=f+'?u=&t=".$post->post_title."&s=".$post->guid."&v=4';a=function(){if(!w.open(u,'t','toolbar=0,resizable=1,scrollbars=1,status=1,width=720,height=570'));};if (/Firefox/.test(navigator.userAgent)) setTimeout(a, 0); else a();void(0)\">#</a>"; 
	  }
	  return $tumb.$excerpt;	  
	} 
	
	
	
	
	
/* SINGLE PAGE */	
	
	wp_embed_register_handler( 'embedInMultiSite_p', '/<p[^>]*>http:\/\/(.+)?'.$serv.'\/\?p=(\d+)<\/p>/i', 'eelv_embed_locals' );
	wp_embed_register_handler( 'embedInMultiSite', '#http://(.+)?'.$serv.'/\?p=(\d+)#i', 'eelv_embed_locals' );
	/*wp_embed_register_handler( 'embedInMultiSite_link', '#(.+)??http://(.+)?'.$serv.'/\?p=(\d+)(.+)??>(.+)?</(.+)>#i', 'eelv_embed_locals' );*/
	
	function eelv_embed_locals( $matches, $attr, $url, $rawattr ) {
	  $matches[1]=str_replace('.','',$matches[1]);
	  if(empty($matches[1])){
		//$matches[1]=$matches[2];    
		$matches[1]='www';
		$blog = get_blog_details(1);
		$blog_post = get_blog_post( 1, $matches[2] );
	  }
	  else{
		$blog = get_blog_details($matches[1]);    
		$blog_post = get_blog_post( $blog->blog_id, $matches[2] );
	  }
	  
	  //echo $query."**";
	  //print_r( $thumb_query);
	 
	  global $it;
	  $it=abs($it);
	  $embed='<!--'.$matches[2].'@'.$matches[1].'--><div class="embeelv">';
	  $embed.='<a href="'.$blog_post->guid.'" target="_blank" id="'.$matches[2].'_'.$it.'_'.$matches[1].'">';
	  $embed.=$blog_post->post_name;
	  $embed.='</a></div>';
	  $embed.='<script>var str_'.$matches[2].'_'.$it.'_'.$matches[1].'="';
	  if(is_object($blog_post)){
		if(false !== $image = get_blog_post_thumbnail($blog->blog_id,$blog_post->ID)){
		  $embed.='[img src=\"'.$image->guid.'\" alt=\"'.$image->post_name.'\"/]';
		}
		$embed.='[h4]';
		$embed.=trim(str_replace('"','\"',$blog_post->post_title));
		$embed.='[/h4][p]';
		
		$w=0;
		$txt = trim(strip_tags($blog_post->post_content));
		$txts=explode("\n",$txt);
		
		$emtxt='';
		foreach($txts as $str){
			$str=str_replace('"','\"',trim($str));	
			$w+=strlen($str);
			$emtxt.=$str.' ';
			if($w>250) break;
		}
		if(strlen($txt)>$emtxt) $emtxt.='...';
		$embed.=$emtxt;
		
		//$embed.=trim(str_replace(array("","\n",),array('&nbsp;','&nbsp;',),substr(strip_tags($blog_post->post_content),0,250)));
		if(isset($matches[3]) && !empty($matches[3])){
		  $embed.='[/p][p]'.trim(str_replace("
","&nbsp;",str_replace('"','\"',strip_tags($matches[3]))));
		}
		$embed.='[/p][p][u]'.$blog_post->guid.'[/u][div style=\"clear:both\"][/div][/p]';
	  }
	  else{
		$embed.='[h4 class=\"nondispo\"]'.__('This post isn\'t avaible any more','eelv-share-post').'[/h4]';
	  }
	  $embed.='";';
	  $embed.='while(str_'.$matches[2].'_'.$it.'_'.$matches[1].'.indexOf("[") != -1){str_'.$matches[2].'_'.$it.'_'.$matches[1].' = str_'.$matches[2].'_'.$it.'_'.$matches[1].'.replace("[","<");}';
	  $embed.='while (str_'.$matches[2].'_'.$it.'_'.$matches[1].'.indexOf("]") != -1){str_'.$matches[2].'_'.$it.'_'.$matches[1].' = str_'.$matches[2].'_'.$it.'_'.$matches[1].'.replace("]",">");}';
	  $embed.='document.getElementById("'.$matches[2].'_'.$it.'_'.$matches[1].'").innerHTML=str_'.$matches[2].'_'.$it.'_'.$matches[1].';document.getElementById("wp-admin-bar-embed_post_menu").style.display="none";</script>';
	  $it++;  
	  return $embed;
	}

}



/************************** SHARING ACTIONS ***/
add_action( 'admin_bar_menu', 'eelv_embed_post', 999 );
function eelv_embed_post( $wp_admin_bar ) {
  if(is_single()){
    // add a parent item
    $args = array('id' => 'embed_post_menu', 'title' => '<span class="ab-icon"></span> <span class="ab-label">'.__('Share on','eelv-share-post').'</span>'); 

  
  $user_id = get_current_user_id(); 
  $cb=get_current_blog_id();
  $user_blogs = get_blogs_of_user( $user_id ); 
  // add a child item to a our parent item
  
  foreach ($user_blogs as $user_blog) {
   $wp_admin_bar->add_node($args);
    $html="<a class='ab-item' onclick=\"var d=document,w=window,e=w.getSelection,k=d.getSelection,x=d.selection,s=(e?e():(k)?k():(x?x.createRange().text:0)),f='http://".$user_blog->domain ."/wp-admin/press-this.php',l=d.location,e=encodeURIComponent,u=f+'?u=&t=".get_the_title()."&s=".wp_get_shortlink()."&v=4';a=function(){if(!w.open(u,'t','toolbar=0,resizable=1,scrollbars=1,status=1,width=720,height=570'));};if (/Firefox/.test(navigator.userAgent)) setTimeout(a, 0); else a();void(0)\">".$user_blog->blogname."</a>";
    $args = array('html'=>$html,'id' => $n, 'title' => $user_blog->blogname, 'parent' => 'Embed_post_menu', 'href'=> '?sharecontent=yes&site='.$user_blog->domain); 
   
    $wp_admin_bar->add_node($args);

    $args = array(
              'id' => $n, 
              'parent' => 'embed_post_menu',
              'meta' => array('class' => 'Embed_post_menu')
            );  
  	$n++;    
  }      
    $wp_admin_bar->add_group($args);
  }
}
 
 add_action( 'wp_footer', 'eelv_share_on_page', 999 );
function eelv_share_on_page(){
	$sharecontent=$_REQUEST['sharecontent'];
	if(isset($sharecontent)){
		$title=get_the_title();
		
		$site=$_REQUEST['site'];
		$blog_id=get_current_blog_id();
		$blog_details = get_blog_details($blog_id);
		$domain=$blog_details->siteurl;
		
		$post_id=get_the_ID();
		
		$link=$domain.'/?p='.$post_id;
		//echo $link;
	echo '<script>';	
	echo"var d=document,w=window,e=w.getSelection,k=d.getSelection,x=d.selection,s=(e?e():(k)?k():(x?x.createRange().text:0)),f='http://".$site."/wp-admin/press-this.php',l=d.location,e=encodeURIComponent,u=f+'?u=&t=".str_replace(array("&rsquo;"),array("\\'"),$title)."&s=...%0A%0A".$link."%0A&v=4';a=function(){if(!w.open(u,'t','toolbar=0,resizable=1,scrollbars=1,status=1,width=720,height=570'));};if (/Firefox/.test(navigator.userAgent)) setTimeout(a, 0); else a();void(0)";  
   echo'</script>';
  }	
}