<?php

/*
include_once(INSTALLED_SERVICE_PATH."classforums/classforums_lib.php");

include_once(LIB_PATH."PBDO/ClassGroup.php");
include_once(LIB_PATH."PBDO/ClassGroupMember.php");
include_once(LIB_PATH."ClassUtility.php");

 */

include_once(CGN_LIB_PATH."/html_widgets/lib_cgn_widget.php");
include_once(CGN_LIB_PATH."/lib_cgn_mvc.php");
include_once(CGN_LIB_PATH."/lib_cgn_mvc_table.php");


Cgn::loadModLibrary('Forum::Forum_Model');
Cgn::loadModLibrary('Forum::Forum_Mvc_Table');
Cgn::loadModLibrary('Forum::Forum_Queries');

/**
 * Class Forums
 *
 * A redisign of the original forums
 */
class Cgn_Service_Forum_Main extends Cgn_Service {

	function mainEvent(&$req, &$t) {

		$dm = new Cgn_Mvc_Table_ForumList($req->getUser());

		$table = new Cgn_Mvc_TableView($dm);
        $table->setColRenderer(0, new Cgn_Mvc_Table_NewMessageRenderer($req->getUser()) );

		$table->attribs = array('width'=>'100%','border'=>0,'cellpadding'=>'1','cellspacing'=>'1');
		$dm->headers = array('','Forum Name',' Posts', 'Replies','Latest&nbsp;Post&nbsp;Info');
		$table->setColWidth(1, '100%');
		$table->setColAlign(1, 'left');
		$table->cssPrefix = 'forum_1';


		$table->setColRenderer(1, new Cgn_Mvc_Table_ForumRenderer());

		$table->setColRenderer(4, new Cgn_Mvc_Table_DateRenderer('M j, Y - h:i A'));
		$t['table'] = $table;

        /*
		$col_b = &$columnModel->getColumnAt(2);
		$col_b->maxWidth=100;

		$col_b = &$columnModel->getColumnAt(3);
		$col_b->maxWidth=100;


		//make sub headers for forum categories

		//TODO use real class id
		$categories = ClassForum_Categories::getAll($u->activeClassTaken->id_classes);

		foreach($categories as $k=>$v) {
			$thisCount = $dm->getForumsInCategory($v->getCategoryId());
			if ($thisCount < 1) { continue; }

			$subHeaderModel = new LC_TableDefaultColumnModel();
			$sub_col_a = new LC_TableColumn();
			$sub_col_a->name = lct('Category').': '. $v->getName();
			$subHeaderModel->addColumn($sub_col_a);
			$subHeader = new LC_DefaultTableHeader($subHeaderModel);
			$subHeader->row = $forumCount;

			$forumCount += $thisCount;

			$table->addSubHeader($subHeader);
		}
		$t['table'] = new LC_TableRenderer($table);
         */
	}


	/**
	 * Show search form
	 */
	function searchRun (&$db, &$u, &$lc, &$t) {
		$lc->templateName = 'main_search';
	}


	/**
	 * Search the posts for usernames
	 * 
	 * Only supports search by username right now.
	 * Filter usernames down to ones that exist in this class
	 */
	function doSearchRun (&$db, &$u, &$lc, &$t) {
		$lc->templateName = 'main_search';
		$username = trim($lc->getvars['username']);
		$class_id = (int)$u->activeClassTaken->id_classes;
		$t['search'] = $username;

		if ($class_id == 0 ) {
			$class_id = (int)$u->activeClassTaught->id_classes;
		}
		//get all the users of the class
		$userList = ClassUtility::getUsernameList($class_id);

		$foundMatch = false;
		foreach($userList as $k=>$v) {
			if ($v == $username) {
				$foundMatch = true;
				//overwrite input with result from DB for security
				$username = $v;
				break;
			}
		}
		if (!$foundMatch) {
			$t['message'] = "No such username in your class";
			return;
		}


		$db->RESULT_TYPE = MYSQL_ASSOC;
		$posts = ClassForum_Posts::getClassPostsByUser($class_id,$username);

		$dm = new LC_TableModel_SearchTopicList($posts,20,1);
		$table = new LC_TablePaged_TopicList($dm);

		$columnModel = &$table->getColumnModel();
		$col = &$columnModel->getColumnAt(1);
		$col->maxWidth=64;
//		$col->cellRenderer = new LC_TableForumCategoryNameRenderer();

//		$col = &$columnModel->getColumnAt(2);
//		$col->maxWidth=64;
//		$col->cellRenderer = new Cgn_Mvc_Table_ForumRenderer();

		$col = &$columnModel->getColumnAt(3);
		$col->justify='left';

		$col = &$columnModel->getColumnAt(4);
		$col->justify='left';


	 	//$table = new LC_TablePaged($dm);
		$t['renderer'] = new LC_TableRendererPaged($table);
	}
}



class Cgn_Mvc_Table_ForumList extends Cgn_Mvc_TableModel {

	var $forums = array();

	/**
	 * make a 5 x 10 grid of nonsense
	 */
	function Cgn_Mvc_Table_ForumList($u) {
		$f = new Forum_Model_List();
		$this->forums = $f->loadVisibleList($u);
		$newList = array();
		//throw out "invisible" ones
		foreach ($this->forums as $k=>$v) {
			if ( !$v->isVisible() ) {
				continue;
			}
			$newList[] = $v;
		}
		$this->forums = $newList;
	}


	//sub-class
	/**
	 * Returns the number of rows in the model.
	 */
	function getRowCount() {
		return (count($this->forums));
	}


	/**
	 * Returns the number of cols in the model.
	 */
	function getColumnCount() {
		return 5;
	}


	/**
	 * Returns the name of a column.
	 */
	function getColumnName($columnIndex) {
		switch ($columnIndex) {
			case '0':
				return '&nbsp;'; break;

			case '1':
				return 'Forum'; break;

			case '2':
				return 'Topics'; break;

			case '3':
				return 'Replies'; break;

			case '4':
				return 'Last Post'; break;

			case '5':
				return 'Admin'; break;
		}
	}


	/**
	 * return the value at an x,y coord
	 */
	function getValueAt($x,$y) {
		$forum = $this->forums[$x];
		switch ($y) {
			case 0:
				return $forum;
			case 1:
				return $forum;
			case 2:
				return $forum->getTopicCount();
			case 3:
				return $forum->getPostCount();
			case 4:
				return $forum->getLastPostTime();
			case 5:
				return $forum->_item;
		}
	}



	/**
	 * Custom function to get number of forums
	 * in a certain category
	 * saves from hitting the DB, since all the forums are loaded already
	 */
	function getForumsInCategory($catId) {
		$ret = 0;
		foreach($this->forums as $k => $v ) {
			if ($v->getCategoryId() == $catId) {
				$ret++;
			}
		}
		return $ret;
	}
}





/*
class LC_TableModel_SearchTopicList extends LC_TableModel_TopicList {


	function LC_TableModel_SearchTopicList($topics, $rpp=-1, $cp=-1) {
		$this->rowsPerPage = $rpp;
		$this->currentPage = $cp;

		$this->posts = $topics;
		$this->maxPosts = count($topics);
	}
 */



	/**
	 * Returns the number of cols in the model.
	 */
/*
	function getColumnCount() {
		return 6;
	}

 */

	/**
	 * Returns the name of a column.
	 */
/*
	function getColumnName($columnIndex) {
		switch ($columnIndex) {
			case '0':
				return 'Username'; break;

			case '1':
				return 'Category'; break;

			case '2':
				return 'Forum'; break;

			case '3':
				return 'Post'; break;

			case '4':
				return 'Thread'; break;

			case '5':
				return 'Date'; break;
		}
	}

 */

	/**
	 * return the value at an x,y coord
	 */
/*
	function getValueAt($x,$y) {
		$post = $this->posts[$x];
		switch ($y) {
			case 0:
				return $post->_dao->userId;
			case 1:
				return $post->getForum();
			case 2:
				return $post->getForum();
			case 3:
				return $post->getSubject(). '<br/><a href="'.modurl('posts/post_id='.$post->getPostId()).'">Only This Post</a>';
			case 4:
				return $post->getSubject().'<br/><a href="'.modurl('posts/post_id='.$post->getThreadId()).'">Entire Thread</a>';

			case 5:
				return date('M d Y',$post->_dao->postDatetime);
		}
	}
}
 */
?>
