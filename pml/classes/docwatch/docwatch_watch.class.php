<?php
// +-------------------------------------------------+
// © 2002-2014 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: docwatch_watch.class.php,v 1.41.4.1 2015-08-28 09:15:54 jpermanne Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($class_path."/docwatch/docwatch_root.class.php");
require_once($class_path."/docwatch/docwatch_category.class.php");
require_once($class_path."/docwatch/datasources/docwatch_datasource.class.php");
require_once($class_path."/docwatch/datasources/docwatch_datasource_notices.class.php");
require_once($class_path."/docwatch/datasources/docwatch_datasource_notices_rss.class.php");
require_once($class_path."/docwatch/datasources/docwatch_datasource_articles.class.php");
require_once($class_path."/docwatch/datasources/docwatch_datasource_sections.class.php");
require_once($class_path."/docwatch/datasources/docwatch_datasource_rss.class.php");
require_once($class_path."/docwatch/datasources/docwatch_datasource_external_sources.class.php");
require_once($class_path."/docwatch/docwatch_item.class.php");

/**
 * class docwatch_watch
 * 
 */
class docwatch_watch extends docwatch_root{

	/** Aggregations: */

	/** Compositions: */

	 /*** Attributes: ***/

	/**
	 * Identifiant de la veille dans la base de donn�es
	 * 
	 */
	protected $id;
	
	/**
	 * Nom de la veille
	 * @access protected
	 */
	protected $title;

	/**
	 * Date de dernier remplissage
	 * @access protected
	 */
	protected $last_date;

	/**
	 * Cr�ateur de la veille. Attention, on ne met que les items notices visibles par
	 * le documentaliste s'ils viennent de PMB
	 * @access protected
	 */
	protected $owner;

	/**
	 * Tableau des utilisateurs autoris�s
	 * @access protected
	 */
	protected $allowed_users;
	/**
	 * 
	 * @access protected
	 */
	protected $num_category;

	/**
	 * Tableau des items contenus dans la veille
	 * @access protected
	 */
	protected $items;

	
	/**
	 * Temps de validit� des items de la veille 
	 * @access protected
	 */
	protected $ttl;
	
	/**
	 * Description de la source
	 * @access protected
	 */
	protected $desc;
	
	/**
	 * Url du logo de la source
	 * @access protected
	 */
	protected $logo_url;
	
	/**
	 * Tableau des sources de donn�es autoris�es
	 * @access protected
	 */
	protected $datasources = array();
	
	/**
	 * Tableau des instances de sources de donn�es autoris�es
	 * @access protected
	 */
	protected $datasources_objects = array();
	
	/**
	 * Tableau des parametre de la veille
	 * @access protected
	 */
	protected $parameters = array();
	
	/**
	 * Tableau des id / dates des datasources mises � jour
	 * @access protected
	 */
	protected $datasources_synced = array();
	
	/**
	 * Id du type de notice par d�faut pour les notices cr�es � partir des items de cette veille
	 * @access protected
	 */
	protected $record_default_type;
	
	/**
	 * Id du status de notice par d�faut pour les notices cr�es � partir des items de cette veille
	 * @access protected
	 */
	protected $record_default_status;
	
	/**
	 * Id du parent par d�faut pour les articles cr�es via les items de cette veille
	 * @access protected
	 */
	protected $article_default_parent;
	
	/**
	 * Id du type de contenun par d�faut pour les articles cr�es via les items de cette veille
	 * @access protected
	 */
	protected $article_default_content_type;
	
	/**
	 * Id du status de publication par d�faut pour les articles cr�es via les items de cette veille
	 * @access protected
	 */
	protected $article_default_publication_status;
	
	/**
	 * Id du parent par d�faut pour les rubriques cr�es via les items de cette veille
	 * @access protected
	 */
	protected $section_default_parent;
	
	/**
	 * Id du type de contenun par d�faut pour les rubriques cr�es via les items de cette veille
	 * @access protected
	 */
	protected $section_default_content_type;
	
	/**
	 * Id du status de publication par d�faut pour les rubriques cr�es via les items de cette veille
	 * @access protected
	 */
	protected $section_default_publication_status;
	
	/**
	 * 
	 * @access proptected
	 */
	protected $error;
	
	
	/**
	 * @return void
	 * @access public
	 */
	public function __construct($id=0) {
		$this->id = $id+0;
		$this->fetch_datas();
	} // end of member function __construct
	
	
	/**
	 * Fetch datas
	 * 
	 */
	public function fetch_datas(){
		global $dbh;
		$this->title = "";
		$this->last_date = "0000-00-00 00:00:00";
		$this->num_category = 0;
		$this->allowed_users = array();
		$this->owner = 0;
		$this->ttl = 0;
		$this->desc = "";
		$this->logo_url = "";
		$this->parameters = array();
		if($this->id){
			//Query
			$query = "select * from docwatch_watches where id_watch = '".$this->id."'";
			$result=pmb_mysql_query($query, $dbh);
			if (pmb_mysql_num_rows($result)) {
				$row = pmb_mysql_fetch_object($result);
				$this->title = $row->watch_title;
				$this->last_date = $row->watch_last_date;
				$this->num_category = $row->watch_num_category;
				$this->allowed_users = explode(",",$row->watch_allowed_users);
				$this->owner = $row->watch_owner;
				$this->ttl = $row->watch_ttl;
				$this->desc = $row->watch_desc;
				$this->logo_url = $row->watch_logo_url;
				$this->record_default_status = $row->watch_record_default_status;
				$this->record_default_type = $row->watch_record_default_type;
				$this->article_default_content_type = $row->watch_article_default_content_type;
				$this->article_default_parent = $row->watch_article_default_parent;
				$this->article_default_publication_status = $row->watch_article_default_publication_status;
				$this->section_default_content_type = $row->watch_section_default_content_type;
				$this->section_default_parent = $row->watch_section_default_parent;
				$this->section_default_publication_status = $row->watch_section_default_publication_status;
				$query = "select id_datasource, datasource_type from docwatch_datasources where datasource_num_watch = ".$this->id;
				$result = pmb_mysql_query($query,$dbh);
				if($result && pmb_mysql_num_rows($result)){
					$this->datasources = array();
					while ($row=pmb_mysql_fetch_object($result)) {
						$this->datasources[$row->id_datasource+0] = $row->datasource_type;
					}
				}
			}
		}
	}
	
	/**
	 * Renvoie le formulaire
	 *
	 */
	public function get_form(){
		return $form;
	}
	
	/**
	 * Set les propri�t�s de l'instance depuis le formulaire
	 *
	 * @return void
	 * @access public
	 */
	public function set_from_form() {
		global $datasources_choice;
		global $docwatch_watch_title;
		global $docwatch_watch_owner;
		global $docwatch_watch_allowed_users;
		global $docwatch_watch_num_category;
		global $docwatch_watch_ttl;
		global $docwatch_watch_desc;
		global $docwatch_watch_logo_url;
	
		if (is_array($datasources_choice) && count($datasources_choice)) {
			foreach ($datasources_choice as $datasource_choice) {
				$this->parameters['datasources'][] = $datasource_choice;
			}
		}
	
		$this->title = strip_tags(stripslashes($docwatch_watch_title));
		$this->owner = $docwatch_watch_owner;
		$this->allowed_users = $docwatch_watch_allowed_users;
		$this->num_category = $docwatch_watch_num_category;
		$this->ttl = $docwatch_watch_ttl;
		$this->desc = $docwatch_watch_desc;
		$this->logo_url = $docwatch_watch_logo_url;
		
	} // end of member function set_from_form
	
	/**
	 * Sauvegarde des propri�t�s
	 *
	 * @return void
	 * @access public
	 */
	public function save(){
		global $dbh;
		
		if($this->id){
			$query = "update docwatch_watches set ";
			$clause = " where id_watch = ".$this->id;
		}else{
			$query = "insert into docwatch_watches set ";
			$clause= "";
		}
		$query.= "
			watch_title = '".addslashes($this->title)."',
			watch_owner = '".$this->owner."',
			watch_allowed_users = '".implode(",", $this->allowed_users)."',
			watch_num_category = '".$this->num_category."',
			watch_last_date = now(),
			watch_ttl = '".$this->ttl."',
			watch_desc = '".addslashes($this->desc)."',
			watch_logo_url = '".$this->logo_url."',
			watch_record_default_type = '".addslashes($this->record_default_type)."',
			watch_record_default_status = '".$this->record_default_status."',
			watch_article_default_parent = '".$this->article_default_parent."',
			watch_article_default_content_type = '".$this->article_default_content_type."',
			watch_article_default_publication_status = '".$this->article_default_publication_status."',
			watch_section_default_parent = '".$this->section_default_parent."',
			watch_section_default_content_type = '".$this->section_default_content_type."',
			watch_section_default_publication_status = '".$this->section_default_publication_status."'
			".$clause;
	
		$result = pmb_mysql_query($query,$dbh);
		if($result){
			if(!$this->id){
				$this->id = pmb_mysql_insert_id($dbh);
			}
			if($this->parameters['datasources']){
				foreach ($this->parameters['datasources'] as $key=>$datasource_type) {
					if (in_array($datasource_type, $this->datasources)) {
						$datasource_id = array_search($datasource_type,$this->datasources);
					} else {
						$datasource_id = 0;
					}
					$datasource = new $datasource_type($datasource_id);
					$datasource->set_num_watch($this->id);
					$result = $datasource->save();
					if($result){
						$this->datasources[$datasource->get_id()] = $datasource_type;
					}
				}
			}
			return true;
		}
		return false;
	}
	
	/**
	 * M�thode de suppression
	*/
	public function delete(){
		global $dbh;
		global $msg;
		if($this->id && $this->check_rights()){
			//on commence par �liminer les sources de donn�es et s�lecteurs associ�s...
			$query = "select id_datasource from docwatch_datasources where datasource_num_watch = ".$this->id;
			$result = pmb_mysql_query($query,$dbh);
			if(pmb_mysql_num_rows($result)){
				while($row = pmb_mysql_fetch_object($result)){
					$query = "select id_selector from docwatch_selectors where selector_num_datasource = ".$row->id_datasource;
					$sel_result = pmb_mysql_query($query,$dbh);
					if(pmb_mysql_num_rows($sel_result)){
						while($sel_row = pmb_mysql_fetch_object($sel_result)){
							$query = "delete from docwatch_selectors where selector_num_datasource = ".$row->id_datasource;
							pmb_mysql_query($query,$dbh);
						}
					}
					$query = "delete from docwatch_datasources where datasource_num_watch = ".$this->id;
					pmb_mysql_query($query,$dbh);
				}
			}
			//il faut ensuite �liminer les items..
			$query = "select id_item from docwatch_items where item_num_watch = ".$this->id;
			$result = pmb_mysql_query($query,$dbh);
			if($result){
				while($row = pmb_mysql_fetch_object($result)){
					$docwatch_item = new docwatch_item($row->id_item);
					$docwatch_item->delete();
				}
			}
			$query = "delete from docwatch_watches where id_watch = ".$this->id;
			$result = pmb_mysql_query($query,$dbh);
			if($result){
				return true;
			}else{
				$this->error = $msg['dsi_docwatch_watch_error_database'];
				return false;
			}
		}else{
			$this->error = $msg['dsi_docwatch_watch_error_dont_exist'];
		}
	}
	
	/**
	 * R�cup�ration des donn�es de la source...
	*/
	public function get_datas(){

	}
	
	/**
	 * Retourne les sources de donn�es associ�es
	 *
	 */
	public function get_datasources() {
		if(!count($this->datasources_objects)) {
			foreach($this->datasources as $id => $classe){
				$this->datasources_objects[] = new $classe($id);
			}
			//TODO: checkifisuptodate
		}
	}
	
	public function get_items() {
		return $this->items;
	}
	
	/**
	 * Renvoie le nombre d'items collect�s
	 *
	 * @return int
	 * @access public
	 */
	public function fill_items( ) {
	} // end of member function fill_items

	/**
	 * 
	 *
	 * @param int item_id Identifiant de l'item � supprimer

	 * @return bool
	 * @access public
	 */
	public function del_item( $item_id ) {
	} // end of member function del_item

	/**
	 * 
	 *
	 * @return bool
	 * @access public
	 */
	public function del_outdated( ) {
		global $dbh;
		$query = "select id_item from docwatch_items where date_add(item_added_date, INTERVAL ".$this->ttl." hour) < now() and item_num_watch = '".$this->id."'";
		$result = pmb_mysql_query($query, $dbh);
		if (pmb_mysql_num_rows($result)) {
			while($row = pmb_mysql_fetch_object($result)){
				$item = new docwatch_item($row->id_item);
				$item->mark_as_deleted();
			}
			return true;
		}	
		return false;
	} // end of member function del_outdated

	/**
	 * del_outdated + fill_items
	 *
	 * @return bool
	 * @access public
	 */
	public function sync() {
		if($this->check_rights()){
			$this->datasources_synced = array();
			$this->del_outdated();
			$this->get_datasources();
			foreach($this->datasources_objects as $datasource){
				if(!$datasource->get_is_up_to_date()){
					//TODO: Do update (with get new items)
					//la methode sync va appeler les mises a jour des items dans la datasource
					$datasource->sync($this->owner);
					$this->datasources_synced[] = array('id'=>$datasource->get_id(), 'last_date'=>$datasource->get_last_date(), 'formated_last_date'=>$datasource->get_formated_last_date());
				}
			}
			$this->update_last_date();
		}
	} // end of member function sync

	public function update_last_date(){
		global $dbh;
		$this->set_last_date(date("Y-m-d H:i:s"));
		$query = "update docwatch_watches set watch_last_date = '".$this->last_date."' where id_watch = '".$this->id."'";
		if(!pmb_mysql_query($query, $dbh)){
			return false;
		}
		return true;
	}
	
	public function get_id() {
		return $this->id;
	}
	
	public function set_id($id) {
		$this->id = $id+0;
	}
	
	public function get_title() {
		return $this->title;
	}
	
	public function set_title($title) {
		$this->title = $title;
	}
	
	public function get_last_date() {
		return $this->last_date;
	}
	
	public function set_last_date($last_date) {
		$this->last_date = $last_date;
	}
	
	public function get_owner() {
		return $this->owner;
	}
	
	public function set_owner($owner) {
		$this->owner = $owner+0;
	}
	 
	public function get_allowed_users() {
		return $this->allowed_users;
	}
	
	public function set_allowed_users($allowed_users) {
		foreach ($allowed_users as $key => $value){
			$allowed_users[$key] = $value+0;	
		}
		$this->allowed_users = $allowed_users;
	}
	
	public function get_num_category() {
		return $this->num_category;
	}
	
	public function set_num_category($num_category) {
		$this->num_category = $num_category+0;
	}
	
	public function set_items($items) {
		$this->items = $items;
	}
	 
	public function get_ttl() {
		return $this->ttl;
	}
	
	public function set_ttl($ttl) {
		$this->ttl = $ttl+0;
	}
	
	public function get_desc() {
		return $this->desc;
	}
	
	public function set_desc($desc) {
		$this->desc = $desc;
	}
	
	public function get_logo_url() {
		return $this->logo_url;
	}
	
	public function set_logo_url($logo_url) {
		$this->logo_url = $logo_url;
	}
	    
	public function get_record_default_type() {
	  return $this->record_default_type;
	}
	
	public function set_record_default_type($record_default_type) {
	  $this->record_default_type = $record_default_type;
	}
	    
	public function get_record_default_status() {
	  return $this->record_default_status;
	}
	
	public function set_record_default_status($record_default_status) {
	  $this->record_default_status = $record_default_status+0;
	}
	    
	public function get_article_default_parent() {
	  return $this->article_default_parent;
	}
	
	public function set_article_default_parent($article_default_parent) {
	  $this->article_default_parent = $article_default_parent+0;
	}
	    
	public function get_article_default_content_type() {
	  return $this->article_default_content_type;
	}
	
	public function set_article_default_content_type($article_default_content_type) {
	  $this->article_default_content_type = $article_default_content_type+0;
	}
	
	public function get_article_default_publication_status() {
	  return $this->article_default_publication_status;
	}
	
	public function set_article_default_publication_status($article_default_publication_status) {
	  $this->article_default_publication_status = $article_default_publication_status+0;
	}
	
	public function get_section_default_parent() {
		return $this->section_default_parent;
	}
	
	public function set_section_default_parent($section_default_parent) {
		$this->section_default_parent = $section_default_parent+0;
	}
	 
	public function get_section_default_content_type() {
		return $this->section_default_content_type;
	}
	
	public function set_section_default_content_type($section_default_content_type) {
		$this->section_default_content_type = $section_default_content_type+0;
	}
	
	public function get_section_default_publication_status() {
		return $this->section_default_publication_status;
	}
	
	public function set_section_default_publication_status($section_default_publication_status) {
		$this->section_default_publication_status = $section_default_publication_status+0;
	}

	public function get_informations(){
		global $dbh, $pmb_opac_url;
		$datas = new stdClass();
		$datas->id = $this->id;
		$datas->type = "watch";
		$datas->title = $this->title;
		$datas->num_category = $this->num_category;
		$datas->owner = $this->owner;
		$datas->ttl = $this->ttl;
		$datas->desc = $this->desc;
		$datas->logo_url = $this->logo_url;
		$datas->last_date = $this->last_date;
		$datas->formated_last_date = date("c",strtotime($this->last_date));
		$datas->record_default_type = $this->record_default_type;
		$datas->record_default_status = $this->record_default_status;
		$datas->article_default_parent = $this->article_default_parent;
		$datas->article_default_content_type = $this->article_default_content_type;
		$datas->article_default_publication_status = $this->article_default_publication_status;
		$datas->section_default_parent = $this->section_default_parent;
		$datas->section_default_content_type = $this->section_default_content_type;
		$datas->section_default_publication_status = $this->section_default_publication_status;
		$datas->opac_link = $pmb_opac_url.'docwatch.php?id='.$this->id;
		$datas->allowed_users = $this->allowed_users;
		$datas->sources = array();
		$query = "select id_datasource, datasource_title from docwatch_datasources where datasource_num_watch = ".$this->id;
		$result = pmb_mysql_query($query,$dbh);
		if($result && pmb_mysql_num_rows($result)){
			while ($row=pmb_mysql_fetch_object($result)) {
				$datas->sources[] = array("id"=>1*$row->id_datasource, "title"=>$row->datasource_title, "type"=>"source");
			}
		}
		$datas->nb_sources = 0;
		if (count($datas->sources)) {
			$datas->nb_sources = count($datas->sources);
		}
		return $datas;
	}
	
	public function get_normalized_watch(){
		global $dbh;
		global $pmb_opac_url;
		$categories = array();
		if ($this->num_category) {
			$query = "select id_category, category_title from docwatch_categories where id_category = ".$this->num_category;
			$result = pmb_mysql_query($query,$dbh);
			if($result && pmb_mysql_num_rows($result)){
				while ($row=pmb_mysql_fetch_object($result)) {
					$categories = array("id"=>$row->id_category, "title"=>$row->category_title);
				}
			}
		}
		$items = array();
		if($this->items){
			foreach($this->items as $item){
				$items[] = $item->get_normalized_item();
			}
		}
		
		return array(
			'id' => $this->id,
			'title' => $this->title,
			'desc' => $this->desc,
			'logo_url' => $this->logo_url,
			'last_date' => $this->last_date,
			'rss_link' => $pmb_opac_url."docwatch?id=".$this->get_id(),
			'category' => $categories,
			'items' => $items
		);
	}
	
	public function get_normalized_datasources(){
		$this->get_datasources();
		$array_datasources_normalized = array();
		$array_retour = array();
		foreach($this->datasources_objects as $datasource){
			$array_datasources_normalized[] = $datasource->get_normalized_datasource();
		}
		$array_retour["sources"] = $array_datasources_normalized;
		$array_retour["watch_id"] = $this->id;
		return $array_retour;
	}
	
	public function get_normalized_items(){
		$array_items_normalized = array();
		$array_retour = array();
		if($this->items){
			foreach($this->items as $item){
				$array_items_normalized[] = $item->get_normalized_item();
			}	
		}
		$array_retour["items"] = $array_items_normalized;
		$array_retour["watch_id"] = $this->id;
		return $array_retour;
	}
	
	public function fetch_items($interesting_only=false){
		global $dbh;
		if($this->check_rights()){
			$query = "select id_item from docwatch_items where item_num_watch = ".$this->id." ";
			if($interesting_only){
				$query.= " and item_interesting= 1";
			}
			$result = pmb_mysql_query($query,$dbh);
			if(pmb_mysql_num_rows($result)){
				while($row = pmb_mysql_fetch_object($result)){
					if(!$this->items){
						$this->items = array();
					}
					if(!$this->items[$row->id_item]){
						$this->items[$row->id_item] = new docwatch_item($row->id_item);
					}
				}
			}
		}
	}
	
	public static function get_available_datasources(){
		global $msg;
		return array(
			array(
				'class' => 'docwatch_datasource_articles',
				'label'=>$msg['dsi_docwatch_datasource_articles']
			),
			array(
				'class' => 'docwatch_datasource_sections',
				'label'=>$msg['dsi_docwatch_datasource_sections']
			),
			array(
				'class' => 'docwatch_datasource_notices',
				'label'=>$msg['dsi_docwatch_datasource_notices']
			),
			array(
				'class' => 'docwatch_datasource_notices_rss',
				'label'=>$msg['dsi_docwatch_datasource_notices_rss']
			),
			array(
				'class' => 'docwatch_datasource_rss',
				'label'=>$msg['dsi_docwatch_datasource_rss']
			),
			array(
				'class' => 'docwatch_datasource_external_sources',
				'label'=>$msg['dsi_docwatch_datasource_external_sources']
			)
		);
		
	}

	/**
	 * Renvoie une structure XML au format RSS
	 *
	 */
	public function get_xmlrss(){
		global $charset, $pmb_bdd_version;
		
		if (!$this->id) return;
		$xmlrss = "<?xml version=\"1.0\" encoding=\"".$charset."\"?>
			<!-- RSS generated by PMB on ".addslashes(date("D, d/m/Y H:i:s"))." -->
			<rss version=\"2.0\" xmlns:dc=\"http://purl.org/dc/elements/1.1/\">
				<channel>
					<title>".htmlspecialchars ($this->title,ENT_QUOTES, $charset)."</title>
					<link></link>
					<description></description>
					<language></language>
					<copyright></copyright>
					<generator>PMB Version ".$pmb_bdd_version."</generator>
					<lastBuildDate>".addslashes(date("D, d M Y H:i:s O",strtotime($this->last_date)))."</lastBuildDate>
					<ttl>".$this->ttl."</ttl>
					<category></category>
				!!items!!
				</channel>
			</rss>";								
					
		$xmlrss = str_replace("!!items!!", $this->get_items_xmlrss(), $xmlrss);
		if($charset=='utf-8') {
			$xmlrss = preg_replace('/[\x00-\x08\x10\x0B\x0C\x0E-\x19\x7F]'.
					'|[\x00-\x7F][\x80-\xBF]+'.
					'|([\xC0\xC1]|[\xF0-\xFF])[\x80-\xBF]*'.
					'|[\xC2-\xDF]((?![\x80-\xBF])|[\x80-\xBF]{2,})'.
					'|[\xE0-\xEF](([\x80-\xBF](?![\x80-\xBF]))|(?![\x80-\xBF]{2})|[\x80-\xBF]{3,})/S',
					'', $xmlrss );
		} else {
			$xmlrss = preg_replace('/[\x00-\x08\x10\x0B\x0C\x0E-\x19\x7F]/S',
					'', $xmlrss );
		}
		return $xmlrss;
	}
	
	function get_items_xmlrss() {
		global $charset;
		
		$items_xmlrss = "";
		
		if (is_array($this->items) && count($this->items)) {
			foreach ($this->items as $item) {
				if ($item->get_interesting()) {
					if ($item->get_logo_url() != "") {
						$image = "<img src='".$item->get_logo_url()."' align='right' hspace='4' vspace='2' />";
					} else {
						$image = "";
					}
					$publication_date="";
					if($item->get_publication_date())$publication_date=date("D, d M Y H:i:s O",strtotime($item->get_publication_date()));
					$items_xmlrss .= "
					<item>
						<title>".htmlspecialchars ($item->get_title(),ENT_QUOTES, $charset)."</title>
						<pubDate>".$publication_date."</pubDate>
						<link>".htmlspecialchars ($item->get_url(),ENT_QUOTES, $charset)."</link>
						<description>
						".htmlspecialchars(strip_tags($image.$item->get_summary(),"<table><tr><td><br/><img>"),ENT_QUOTES, $charset)."
						</description>";
					$descriptors = $item->get_descriptors();
					if (is_array($descriptors) && count($descriptors)) {
						foreach ($descriptors as $descriptor) {
							$items_xmlrss .= "
						<category>".htmlspecialchars ($descriptor,ENT_QUOTES, $charset)."</category>";
						}
					}
					$items_xmlrss .= "
					</item>";
				}
			}
		}
		return $items_xmlrss;
	}
	public function get_synced_datasources(){
		return $this->datasources_synced;
	}
	
	public static function get_format_data_structure(){
		global $msg;
		$datas = array(
			array(
				'var' => "id",
				'desc'=> $msg['cms_module_watch_datasource_desc_id']
			),
			array(
				'var' => "title",
				'desc' => $msg['cms_module_watch_datasource_desc_title']
			),
			array(
				'var' => "desc",
				'desc' => $msg['cms_module_watch_datasource_desc_desc']
			),
			array(
				'var' => "logo_url",
				'desc' => $msg['cms_module_watch_datasource_desc_logo_url']
			),
			array(
				'var' => "last_date",
				'desc' => $msg['cms_module_watch_datasource_desc_last_date']
			),
			array(
				'var' => "rss_link",
				'desc' => $msg['cms_module_watch_datasource_desc_rss_link']
			),
			array(
				'var' => "category",
				'desc' => $msg['cms_module_watch_datasource_desc_category'],
				'children' => array(
					array(
						'var' => "category.id",
						'desc' => $msg['cms_module_watch_datasource_desc_category_id']
					),
					array(
						'var' => "category.title",
						'desc' => $msg['cms_module_watch_datasource_desc_category_title']
					)
				)
			),
			array(
				'var' => "items",
				'desc' => $msg['cms_module_watch_datasource_desc_items'],
				'children' => docwatch_root::prefix_var_tree(docwatch_item::get_format_data_structure(), "items[i]")
			)
		);
		return $datas;
	}
	/**
	 * M�thode appell�e apr�s le fetch_datas
	 * @return true if user have rights, false otherwise
	 */
	public function check_rights(){
		if(in_array(SESSuserid, $this->allowed_users)){
			return true;
		}
		return false;
	}
	
	public static function check_watch_rights($watch_id){
		global $dbh;
		if($watch_id){
			$query = "select watch_allowed_users from docwatch_watches where id_watch = '".$watch_id."'";
			$result = pmb_mysql_query($query, $dbh);
			if(pmb_mysql_num_rows($result)){
				$row = pmb_mysql_fetch_object($result);
				$tab_users = explode(",",$row->watch_allowed_users);
			}
			if(in_array(SESSuserid, $tab_users)){
				return true;
			}
			return false;
		}else{
			return true;
		}
	}
	
	public function get_error(){
		return $this->error;
	}
} // end of docwatch_watch
