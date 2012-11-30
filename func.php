<?php
function eelv_share_get_domains(){
	$eelv_share_domains = get_site_option( 'eelv_share_domains' ,array(), true);
	$domains_map=array();
	foreach ($eelv_share_domains as $domain){ 
	 if(is_string($domain)) $domain = array($domain,$domain);
	 $domains_map[$domain[0]]=$domain[1];
	}
	$domains_map[DOMAIN_CURRENT_SITE]=DOMAIN_CURRENT_SITE;
	return $domains_map;
}
	
	
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
function eelv_share_untag($str){
	return trim(str_replace(array('<','>',"\\r\\n",'"'),array('[',']',' ','\''),$str));
}
function eelv_share_parse_youtube($excerpt,$insert=false){
	$thumb='';
	preg_match_all('#[\n\t\r\s]http://www\.youtube\.com/watch\?v=(.+)\&?(.+)?[\n\t\r\s]#i',$excerpt,$yout, PREG_PATTERN_ORDER); 
	if(is_array($yout)){
	  foreach($yout[0] as $id=>$match){
		$url=explode(' ',$yout[1][$id]);
		  $url=$url[0];
		$val="<iframe class='embeelv_iframe' src='http://www.youtube.com/embed/".$url."' width='250' height='150'>video</iframe>";
		if(!$insert) $tumb.= $val;
		else $excerpt=str_replace($match,$val,$excerpt);      
	  }
	}
	if(!$insert) return $tumb;
	else return $excerpt;
}
function eelv_share_parse_dailymotion($excerpt,$insert=false){
	$thumb='';
	preg_match_all('#[\n\t\r\s]http://www\.dailymotion\.com/video/(.+)_??(.+)??[\n\t\r\s]#i',$excerpt,$dail, PREG_PATTERN_ORDER); 
	if(is_array($dail)){
	  foreach($dail[0] as $id=>$match){
		  $url=explode(' ',$dail[1][$id]);
		  $url=$url[0];
		$val="<iframe class='embeelv_iframe' src='http://www.dailymotion.com/embed/video/".$url."' width='250' height='150'>video</iframe>";
		if(!$insert) $tumb.= $val;
		else $excerpt=str_replace($match,$val,$excerpt);      
	  }
	}
	if(!$insert) return $tumb;
	else return $excerpt;
}
function eelv_share_parse_twitter($excerpt){
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
	return $excerpt;
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