<?

/**
 * This code is licensed under a proprietary license.
 * THE SOFTWARE IS LICENSED, NOT SOLD.
 *
 * BY INSTALLING ALL OR ANY PORTION OF THE SOFTWARE 
 * (OR AUTHORIZING ANY OTHER PERSON TO DO SO) , YOU ACCEPT 
 * ALL THE TERMS AND CONDITIONS OF THIS LICENSE. IF YOU 
 * ACQUIRED THE SOFTWARE WITHOUT AN OPPORTUNITY TO REVIEW 
 * THIS LICENSE AND DO NOT ACCEPT THE LICENSE, YOU MAY 
 * OBTAIN A REFUND OF THE AMOUNT YOU ORIGINALLY PAID FOR 
 * THE SOFTWARE IF YOU: (A) DO NOT USE THE SOFTWARE AND 
 * (B) RETURN THE SOFTWARE, WITH PROOF OF PAYMENT, WITHIN 
 * THIRTY (30) DAYS OF THE PURCHASE DATE.
 *
 * @Copyright Mark Kimsal
 */
include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc_table.php');

Cgn::loadModLibrary('Nx-Changelog::Nxc_Project_Model');
Cgn::loadModLibrary('Nx-Changelog::Nxc_Changelog_Model');


class Cgn_Service_Nxchangelog_Main extends Cgn_Service {


	public function getBreadcrumbs() {
		$crumbs = array( cgn_applink('All Projects', 'nx-changelog'));
		if (!is_object($this->project)) {
			$this->project = new Cgn_DataItem('nxc_project');
			$this->cl = new Cgn_DataItem('nxc_changelog');
		}
		switch($this->eventName) {
			case 'newProj':
				$crumbs[] = 'New Project';
				break;

			case 'editCl':
				$crumbs[] = 'Project: '.cgn_applink(htmlentities($this->project->get('title')),
					'nx-changelog', 'main', 'editProj', array('id'=>$this->project->getPrimaryKey()));
				$crumbs[] = 'Edit Changelog';
				break;

			case 'editProj':
				$crumbs[] = 'Project: '.htmlentities($this->project->get('title'));
				break;

			case 'clEntries':
				//$crumbs[] = cgn_applink('Project: '.htmlentities($this->project->title, 'nx-changelog', 'main'));
				$crumbs[] = 'Project: '.cgn_applink(htmlentities($this->project->get('title')),
					'nx-changelog', 'main', 'editProj', array('id'=>$this->project->getPrimaryKey()));
				$crumbs[] = 'Changelog: '.cgn_applink(htmlentities($this->cl->get('title')),
					'nx-changelog', 'main', 'editCl', array('id'=>$this->cl->getPrimaryKey()));
				$crumbs[] = 'Organize Entries';
				break;

		}
		return $crumbs;
	}

	/**
	 * Show a list of projects
	 */
	public function mainEvent($req, &$t) {
		$nxcProjects = new Nxc_Project_Model_List();
//		$finder = new Cgn_DataItem('nxc_project');
		$u = $req->getUser();
		if ($u->isAnonymous()) {
			$this->templateName = 'main_anon';
			return TRUE;
		}
		$recordList = $nxcProjects->loadVisibleList($u);


		$t['projectList'] = array();
		//cut up the data into table data
		foreach ($recordList as $project) {
			//put project in template
			$t['projectList'][$project->nxc_project_id] = $project;

			//find changelogs
			$finder = new Cgn_DataItem('nxc_changelog');
			$finder->andWhere('nxc_project_id', $project->nxc_project_id);
			$logs = $finder->find();
			$list = new Cgn_Mvc_TableModel();
			foreach ($logs as $object) {
				$urlParts = parse_url($object->trunk_url);
				$list->data[] = array(
				cgn_applink(
				   $object->title? $object->title: 'No Title',
				   'nx-changelog','main','editCl',array('id'=>$object->getPrimaryKey())),
				date('M-d-Y', $object->created_on),
				cgn_applink('Organize Entries','nx-changelog','main','clEntries',array('id'=>$object->getPrimaryKey())),
				cgn_applink('TXT','nx-changelog','main','download',array('id'=>$object->getPrimaryKey(),'format'=>'txt')).', '.
				cgn_applink('XML','nx-changelog','main','download',array('id'=>$object->getPrimaryKey(),'format'=>'xml')),
				);

			}

			$list->headers = array('Title','Created','Changelog Entries', 'Download');
			$t['logTableList'][$project->nxc_project_id] = new Cgn_Mvc_TableView($list);
			unset($list);
		}
	}


	/**
	 * Show a form for a new project
	 */
	public function newProjEvent($req, &$t) {

		$t['form'] = $this->_loadProjForm();
	}

	/**
	 * Show a form for editing a project
	 */
	public function editProjEvent($req, &$t) {
		$projId = $req->cleanInt('id');

		$proj = new Nxc_Project_Model();
		$this->project = $proj;

		if(!$proj->load($projId)) {
			$u = $req->getUser();
			$u->addMessage('no permission', 'msg_warn');

			return false;
		}


		$values = $proj->dataItem->valuesAsArray();
		$t['form'] = $this->_loadProjForm($values);
	}


	/**
	 * Save a new project, redirect home
	 */
	public function saveProjEvent($req, &$t) {

		$projId = $req->cleanInt('id');
		$proj = new Nxc_Project_Model();

		//they might be making a new project, or they might be editing.
		if ($projId && ! $proj->load($projId) ) {
			$u->addMessage('no permission', 'msg_warn');
			return FALSE;
		}

		$u = $req->getUser();
		$proj->set('title', $req->cleanString('title'));
		$proj->set('trunk_url', $req->cleanString('trunk_url'));
		if (! $proj->get('created_on') || $proj->get('created_on') == 0) {
			$proj->set('created_on', time());
		}
		$proj->set('edited_on', time());
		$proj->set('user_id', $u->getUserId());
		$proj->save();
		$this->redirectHome($t);
	}


	/**
	 * Show a form for a new changelog
	 */
	public function newClEvent($req, &$t) {
		$u = $req->getUser();
		$t['proj'] = new Nxc_Project_Model();
		if (! $t['proj']->load($req->cleanInt('id')) ) {
			$u->addMessage('no permission', 'msg_warn');
			return FALSE;
		}
	}

	/**
	 * Show a form for editing a changelog
	 */
	public function editClEvent($req, &$t) {

		$u = $req->getUser();
		$t['cl'] = new Nxc_Changelog_Model();
		$clId = $req->cleanInt('id');
		if (! $t['cl']->load($clId) ) {
			$u->addMessage('no permission', 'msg_warn');
			return false;
		}

		$t['proj'] = new Cgn_DataItem('nxc_project');
		$t['proj']->load($t['cl']->get('nxc_project_id'));
		$this->project = $t['proj'];
	}


	/**
	 * Save a new project, redirect home
	 */
	public function saveClEvent($req, &$t) {

		$cl = new Nxc_Changelog_Model();
		$u = $req->getUser();

		$isEditing = FALSE;
		$forceDl   = FALSE;
		$clId = $req->cleanString('id');
		if ($clId) {
			$isEditing = TRUE;
		}
		//they might be making a new changelog.
		if ($clId && ! $cl->load($clId) ) {
			$u->addMessage('no permission: changelog', 'msg_warn');
			return false;
		}
		//verify the Project ownership
		$projId = $req->cleanInt('proj_id');
		$proj = new Nxc_Project_Model();
		if (! $proj->load($projId)) {
			$u->addMessage('no permission: proj', 'msg_warn');
			return false;
		}
		if (!$projId) {
			$u->addMessage('Error, missing project ID', 'msg_warn');
			return false;
		}


		$force = $req->cleanString('force-dl');
		if ($force === 'on') {
			$forceDl = TRUE;
		}

		$cl->set('nxc_project_id', $projId);
		$cl->set('title', $req->cleanString('title'));
		$cl->set('description', $req->cleanString('notes'));
		$cl->set('trunk_url', $req->cleanString('url1'));
		$cl->set('last_tag_url', $req->cleanString('url2'));

		if (! isset($cl->dataItem->created_on) || $cl->get('created_on') == 0) {
			$cl->set('created_on', time());
		}
		$cl->set('edited_on', time());

		if ($cl->get('trunk_rev') == 0 || $cl->get('last_tag_rev') == 0) {
			$this->setClRevs($cl);
		} else {
			if ($req->cleanInt('trunk_rev') != $cl->get('trunk_rev')) {
				$cl->set('trunk_rev', $req->cleanInt('trunk_rev'));
			}

			if ($req->cleanInt('last_tag_rev') != $cl->get('last_tag_rev')) {
				$cl->set('last_tag_rev', $req->cleanInt('last_tag_rev'));
			}
		}

		$cl->save();

		//if we want to force the download OR we're doing a new insert (not editing)
		if ($forceDl || !$isEditing ) {
			$entries = $this->fetchLogEntries($cl);
		}

		//if we're just editing, go back home
		//  unless we are forcing a re-download of the changelog entires.
		if ($isEditing && !$forceDl) {
			$this->redirectHome($t);
		} elseif ($forceDl) {
			// any force redownload goes right to the organization page.
			$this->presenter = 'redirect';
			$t['url'] = cgn_appurl('nx-changelog', 'main', 'clEntries', array('id'=>$cl->getPrimaryKey()));
		} elseif ($isEditing) {
		} else {
			//jump right to the entry organization page.
			$this->presenter = 'redirect';
			$t['url'] = cgn_appurl('nx-changelog', 'main', 'clEntries', array('id'=>$cl->getPrimaryKey()));
		}
	}


	/**
	 * Show a form for editing a changelog
	 */
	public function clEntriesEvent($req, &$t) {

		$t['cl'] = new Nxc_Changelog_Model();
		if(!$t['cl']->load($req->cleanInt('id'))) {
			$u = $req->getUser();
			$u->addMessage('no permission', 'msg_warn');

			$list = new Cgn_Mvc_TableModel();
			$t['logEntryTable'] = new Cgn_Mvc_TableView($list);
			return false;
		}


		$this->project = new Cgn_DataItem('nxc_project');
		$this->project->load($t['cl']->get('nxc_project_id'));

		$this->cl = $t['cl'];

		//load up entry categories
		$categoryList = array('New Features', 'Minor Changes', 'Bug Fixes', 'Database Changes', 'Config Changes', 'Hidden');
		$categoryForm = $this->_loadCategoryForm($categoryList);

		//pagination

		//set up pagination variables
		$curPage = $req->cleanInt('p');
		$rpp = 100;
		if ($curPage == 0 ) {
			$curPage = 1;
		}

		$finder = new Cgn_DataItem('nxc_changelog_entry');
		$finder->andWhere('nxc_changelog_id', $t['cl']->getPrimaryKey());
		$finder->limit($rpp, ($curPage-1));

		$objectList = $finder->find();
		$list = new Cgn_Mvc_TableModel();
		$list->setUnlimitedRowCount($finder->getUnlimitedCount());
		foreach ($objectList as $object) {
			$finder = new Cgn_DataItem('nxc_entry_cat_link');
			$finder->andWhere('nxc_changelog_entry_id', $object->getPrimaryKey());

			$author = $object->author? $object->author: 'Unknown';

			//organize the entries into a table
			$list->data[] = array(
				$author .'<br/> (rev:'.$object->revision.')', 
				$object->message,
				array($object->getPrimaryKey(), $finder->find()),
			/* cgn_applink('done','nx-changelog','main','addEntryCat', array('table_id'=>$object->getPrimaryKey())), */
			);

		}
		$list->headers = array('Title','SVN Domain','Categories');
		$t['logEntryTable'] = new Cgn_Mvc_TableView_Paged($list);

		//pagination
		$t['logEntryTable']->setCurPage($curPage);
		$t['logEntryTable']->setRpp($rpp);
		$pageParams = array('id'=>$req->cleanInt('id'), 'p'=>'%d');
		$t['logEntryTable']->setNextUrl( cgn_appurl('nx-changelog', 'main', 'clEntries', $pageParams));
		$t['logEntryTable']->setPrevUrl( cgn_appurl('nx-changelog', 'main', 'clEntries', $pageParams));
		unset($pageParams['p']);
		$t['logEntryTable']->setBaseUrl( cgn_appurl('nx-changelog', 'main', 'clEntries', $pageParams));


		$t['logEntryTable']->setColWidth(2, '30%');
		$sortColumn = new Cgn_Mvc_ColumnRenderer_Changelog();

		$t['logEntryTable']->setColRenderer(2,$sortColumn);
	}

	/**
	 * Toggle category <=> changelog entry relationships
	 */
	public function addEntryCatEvent($req, &$t) {
		$categoryList = array('New Features', 'Minor Changes', 'Bug Fixes', 'Database Changes', 'Config Changes', 'Hidden');

		$id = $req->cleanInt('id');
		$catId = $req->cleanInt('cat');

		//security:
		//load the changelog by linking to the changelog entry
		// use parent-owner security
		$changelog = new Nxc_Changelog_Model();
		$changelog->dataItem->hasOne('nxc_changelog_entry', 'nxc_changelog_id', 'Tentry');
		$changelog->dataItem->andWhere('Tentry.nxc_changelog_entry_id', $id);
		if(!$changelog->load(NULL)) {
			return FALSE;
		}

		$finder = new Cgn_DataItem('nxc_entry_cat_link', NULL);
		$finder->andWhere('nxc_changelog_entry_id', $id);
		$finder->andWhere('category', $categoryList[$catId]);
		$entries = $finder->find();
		if (count($entries)) {
			//remove them all
			$finder->delete();
			$t['status'] = 'okay';
		} else {
			//add a new link
			$entry = new Cgn_DataItem('nxc_entry_cat_link');
			$entry->nxc_changelog_entry_id = $id;
			$entry->category = $categoryList[$catId];
			if ($entry->save()) {
				$t['status'] = 'okay';
			}
		}
		if (!isset($t['status'])) {
			$t['status'] = 'error';
		}
	}


	public function setClRevs($cl) {
		$output = array();
		$output2 = array();
		$ret = NULL;
//		$cmd = escapeshellcmd('svn log --xml --stop-on-copy '.$cl->get('last_tag_url'));
		$cmd = escapeshellcmd('bash -c "HOME=/var/www svn log --xml --stop-on-copy '.$cl->get('last_tag_url').'"');
		exec($cmd, $output, $ret);
		$sxml = simplexml_load_string(implode("\n", $output));
		$tagRev = $sxml->logentry['revision'];
		$cl->set('last_tag_rev', (int)$tagRev);

		$output = array();
		$cmd = escapeshellcmd('bash -c "HOME=/var/www svn info --xml '.$cl->get('trunk_url').'"');
		exec($cmd, $output);
		$sxml = simplexml_load_string(implode("\n", $output));
		$trunkRev = $sxml->entry['revision'];
		$cl->set('trunk_rev', (int)$trunkRev);

//		cgn::debug($tagRev);
	}

	public function fetchLogEntries($cl) {
		$output = array();
		$cmd = escapeshellcmd( 'bash -c "HOME=/var/www svn log --xml -r'.$cl->get('last_tag_rev').':'.$cl->get('trunk_rev').' '.$cl->get('trunk_url').'"');
		exec($cmd, $output);
		$sxml = simplexml_load_string(implode("\n", $output));
		$entries = array();
		if(!isset($sxml->logentry)) {
			return $entries;
		}
		foreach ($sxml->logentry as $entry) {
			$x = new Cgn_DataItem('nxc_changelog_entry');
			$x->andWhere('nxc_changelog_id', $cl->getPrimaryKey());
			$x->andWhere('revision', (string)$entry['revision']);
			$x->load();

			$x->nxc_changelog_id = $cl->getPrimaryKey();
			$x->author = (string)$entry->author;
			$x->message = (string)$entry->msg;
			$x->revision = (string)$entry['revision'];
			$x->entry_date = (int) strtotime($entry->date);
			if (! isset($x->created_on) || $x->created_on == 0) {
				$x->created_on = time();
			}
			$x->edited_on = time();
			$x->save();
			$entries[] = $x;
		}
		return $entries;
	}

	public function downloadEvent($req) {
		$clid = $req->cleanInt('id');
		$format = $req->cleanString('format');
		$cl = new Nxc_Changelog_Model();
		if(!$cl->load($clid)) {
			$u = $req->getUser();
			$u->addMessage('no permission', 'msg_warn');
			return false;
		}
		$finder = new Cgn_DataItem('nxc_changelog_entry');
		$finder->andWhere('nxc_changelog_id', $clid);
//		$finder->hasMany('nxc_entry_cat_link');
		$finder->hasOne('nxc_entry_cat_link', 'nxc_changelog_entry_id', 'Tlink');
		$finder->andWhere('Tlink.nxc_changelog_entry_id', 'NULL', 'IS NOT');
		$finder->_rsltByPkey = false;
		$entries  = $finder->find();

		$cat = array();
		//resort by categories
		foreach ($entries as $e) {
			if ($e->category == 'Hidden') { continue;}
			if (!isset($cat[$e->category])) {
				$cat[$e->category] = array();
			}
			$cat[$e->category][] = $e;
		}

		//resort categories
		$sortCat = array('New Features', 'Bug Fixes', 'Minor Changes', 'Database Changes', 'Config Changes');
		$newCat = array();
		foreach ($sortCat as $sc) {
			if (is_array($cat[$sc]))
			$newCat[$sc] = $cat[$sc];
		}
		$cat = $newCat;
		unset($newCat);

		if ($format == 'txt') {
			$this->printAsTxt($cl, $cat);
		}

		if ($format == 'xml') {
			$this->printAsXml($cl, $cat);
		}

		if ($format == 'wiki') {
			$this->printAsWiki($cl, $cat);
		}
		$this->presenter = 'none';
	}

	public function printAsTxt($cl, $cList) {
		header('Content-type: text/plain');
		echo "Changelog: ".$cl->get('title')."\n";
		echo str_repeat('=', 80);
		echo "\n";
		if ($cl->get('description') != '' ) {
			echo "Release Notes:\n\n". wordwrap($cl->get('description'));
			echo "\n";
		}

		foreach ($cList as $c) {
			echo "\n".trim($c[0]->category).":\n\n";
			foreach ($c as $e) {
				echo "  * ".trim($e->message)."\n";
			}
			echo "\n";
		}
	}


	public function printAsWiki($cl, $cList) {
		header('Content-type: text/plain');
		echo "======Changelog: ".$cl->title."======\n";
		echo str_repeat('-', 80);
		echo "\n";
		if ($cl->description != '' ) {
			echo "=====Release Notes=====\n\n". wordwrap($cl->description);
			echo "\n";
		}

		foreach ($cList as $c) {
			echo "\n====".trim($c[0]->category)."====\n\n";
			foreach ($c as $e) {
				echo "  * ".trim($e->message)."\n";
			}
			echo "\n";
		}
	}

	public function printAsXml($cl, $cList) {
		header('Content-type: text/xml');
		echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
		echo "<changelog>\n";
		if ($cl->description != '' ) {
			echo "\t<notes>\n". wordwrap($cl->description);
			echo "\n";
			echo "\t</notes>\n";
		}

		foreach ($cList as $c) {
			echo "\n\t<category>".trim($c[0]->category)."\n";
			foreach ($c as $e) {
				echo "\t\t<entry><![CDATA[".trim($e->message)."]]></entry>\n";
			}
			echo "\t</category>\n";
		}
		echo "</changelog>";
	}

	function _loadProjForm($values=array()) {
		include_once(CGN_LIB_PATH.'/form/lib_cgn_form.php');
		include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
		$f = new Cgn_Form('content_01');
		$f->action = cgn_appurl('nx-changelog','main','saveProj');
		$f->label = 'Add new project';
		$f->appendElement(new Cgn_Form_ElementInput('title'), $values['title']);
		$input = new Cgn_Form_ElementInput('trunk_url','Trunk URL', 60);
		$f->appendElement($input, $values['trunk_url']);
		$f->appendElement(new Cgn_Form_ElementHidden('id'), $values['nxc_project_id']);
		return $f;
	}

	function _loadCategoryForm($values=array()) {
		include_once(CGN_LIB_PATH.'/form/lib_cgn_form.php');
		include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
		$f = new Cgn_Form('entry_cat_01');
		$f->style['width'] = 'auto';
		$f->style['margin'] = '0px';
		$f->action = cgn_appurl('nx-changelog','main','addEntryCat');

		$check = new Cgn_Form_ElementCheck('sec', '');
		foreach ($values as $cat) {
			$check->addChoice($cat);
		}
		$f->appendElement($check);
		return $f;
	}
}
/*
 * svn log --xml output looks like this
 * <?xml version="1.0"?>
 * <log>
 * <logentry
 *    revision="1374">
 *    <author>username</author>
 *    <date>2008-07-25T18:08:42.342143Z</date>
 *    <msg>Tagging release 14
 *    </msg>
 *    </logentry>
 *    </log>
 *
 */

/*
 * svn info --xml output looks like this
 * <?xml version="1.0"?>
 * <info>
 * <entry
 *    kind="dir"
 *       path="trunk"
 *          revision="1407">
 *          <url>https://niftyphp.svn.sourceforge.net/svnroot/niftyphp/cognifty/trunk</url>
 *          <repository>
 *          <root>https://niftyphp.svn.sourceforge.net/svnroot/niftyphp</root>
 *          <uuid>3241a3d7-c3ea-42e9-b823-2be48aa1cd2b</uuid>
 *          </repository>
 *          <commit
 *             revision="1407">
 *             <author>hardcorelamer</author>
 *             <date>2008-08-10T15:56:09.963084Z</date>
 *             </commit>
 *             </entry>
 *             </info>
 *
 */

class Cgn_Mvc_ColumnRenderer_Changelog extends Cgn_Mvc_Table_ColRenderer {

	var $baseUrl;
	var $categoryList = array('New Features', 'Minor Changes', 'Bug Fixes', 'Database Changes', 'Config Changes', 'Hidden');
	function Cgn_Mvc_ColumnRenderer_Changelog() { }

	/**
	 * returns two links, up and down
	 */
	function getRenderedValue($val, $x, $y) {
		$entryId = $val[0];
		$linkList = $val[1];
		$html  = '<ul>';
		foreach ($this->categoryList as $catIdx => $cat) {

			$class = 'li_bul_off';
			foreach ($linkList as $link) {
				if ($cat == $link->category) {
					$class = 'li_bul_on';
					break;
				}
			}
			$html .= '
			<li class="'.$class.'">
			<a onclick="jqsetCategory(this); return false;" href="'.cgn_appurl('nx-changelog','main','addEntryCat', array('cat'=>$catIdx, 'id'=>$entryId)).'">'.$cat.'</a>
			</li>';
		}
		$html .= '</ul>';
//		$html = '<a href="?event=rankUp&id='.$data['id'].'">Up</a> &nbsp; <a href="?event=rankDown&id='.$data['id'].'">Down</a> &nbsp;'.$data['rank'];
		return $html;
	}
}

?>
