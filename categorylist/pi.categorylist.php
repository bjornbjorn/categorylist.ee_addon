<?php 

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$plugin_info = array(
  'pi_name' => 'Category list',
  'pi_version' => '1.0',
  'pi_author' => 'Bjorn Borresen',
  'pi_author_url' => 'http://bybjorn.com/',
  'pi_description' => 'Will return a ul/li nested list of all categories',
  'pi_usage' => Categorylist::usage()
  );

/**
 * Memberlist Class
 *
 * @package			ExpressionEngine
 * @category		Plugin
 * @author			Bjørn Børresen
 * @copyright		Copyright (c) 2009, Bjørn Børresen
 * @link			http://bybjorn.com/ee/categorylist
 */

class Categorylist
{

var $return_data = "";

	// --------------------------------------------------------------------

  /**
   * Get HTML
   * 
   * @param $arr
   * @param $default_css_class
   * @param $current_css_class
   * @param $path
   * @param $current_cat_url_title
   * @param $open_html
   * @param $ul_class_children
   * @param $level
   */
  function getCategoryHTML( $arr, $default_css_class, $current_css_class, $path, $current_cat_url_title, $open_html, $ul_class_children, $level = 0 ) 
  {
		$html = $open_html;
		foreach($arr as $category) {
			
			$css_class = ($category['cat_url_title'] == $current_cat_url_title ? $current_css_class : $default_css_class);
			
			$html .= '<li class="'.$css_class.'"><a href="'.$path.$category['cat_url_title'].'"><span>' . $category['cat_name'] . '</a></span>';
			if(isset($category['children'])) {	// has kids!
				$html .= $this->getCategoryHTML( $category['children'], $default_css_class, $current_css_class, $path, $current_cat_url_title, "<ul".(($level > 0 && $ul_class_children != '')?' class="'.$ul_class_children.'">':'>'), $ul_class_children, $level+1 );
			}
		}
		$html .= "</li></ul>";
		
		return $html;
  }	 

  function Categorylist()
  {
	$this->EE =& get_instance();
	
	$group_id = intval($this->EE->TMPL->fetch_param('group_id'));
	$current_css_class = $this->EE->TMPL->fetch_param('current_css_class');
	$default_css_class = $this->EE->TMPL->fetch_param('default_css_class');
	$ul_class_children = $this->EE->TMPL->fetch_param('ul_class_children');
	$ul_html = $this->EE->TMPL->fetch_param('ul_html');
	$addtourl = $this->EE->TMPL->fetch_param('add_to_url');
				
	$siteurl = $this->EE->config->slash_item('site_url');
	$indexphp = $this->EE->config->slash_item('site_index');	
	$path = $siteurl . $indexphp . ($addtourl != "" ? $addtourl."/" : "") . $this->EE->config->item('reserved_category_word') .'/';
		
	$current_cat_url_title = $this->EE->TMPL->fetch_param('current_cat_url_title');
	$home_link = $this->EE->TMPL->fetch_param('home_link');
		
    $query = $this->EE->db->query("SELECT * FROM exp_categories WHERE group_id='{$group_id}' ORDER BY cat_order");
	
	$categories = array();
    foreach($query->result_array() as $row)
    {
      $cat_id_to_cat[ $row['cat_id'] ] = $row;
    }
	
	// categories
	$categories = array();
    foreach($query->result_array() as $row)
    {
	  $cat_id = $row['cat_id'];
	  $parent_id = $row['parent_id'];
	  
	  if($parent_id == 0) {
			$categories[] =& $cat_id_to_cat[$cat_id];
		} else {
			if(!isset($cat_id_to_cat[$parent_id]['children'])) {
				$cat_id_to_cat[$parent_id]['children'] = array();
			}
			$cat_id_to_cat[$parent_id]['children'][] =& $cat_id_to_cat[$cat_id];
		}
    }
        
	if($home_link != "") {
		$css_class = ($current_cat_url_title == '' ? $current_css_class : $default_css_class); 
		$open_html = '<ul '.$ul_html.'><li class="'.$css_class.'"><a href="'.$home_link.'"><span>'.$this->EE->TMPL->fetch_param('home_title').'</span></a></li>';
		
	} else {
		$open_html = '<ul '.$ul_html.'>';
	}    
	$this->return_data = $this->getCategoryHTML($categories, $default_css_class, $current_css_class, $path, $current_cat_url_title, $open_html, $ul_class_children, 1);
	
  }
  


	// --------------------------------------------------------------------
	/**
	 * Usage
	 *
	 * This function describes how the plugin is used.
	 *
	 * @access	public
	 * @return	string
	 */
	
  //  Make sure and use output buffering

  function usage()
  {
  ob_start(); 
  ?>
	Will output categories as a nested <ul> / <li class=''> tags

	{exp:categorylist group_id="1" default_css_class="cat_item" current_css_class="current_page_item" path="archives"}

  <?php
  $buffer = ob_get_contents();
	
  ob_end_clean(); 

  return $buffer;
  }
  // END

}
/* End of file pi.categorylist.php */ 

/* Location: ./system/expressionengine/third_party/categorylist/pi.categorylist.php */ 