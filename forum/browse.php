<?php

include_once(CGN_LIB_PATH."/html_widgets/lib_cgn_widget.php");
include_once(CGN_LIB_PATH."/lib_cgn_mvc.php");
include_once(CGN_LIB_PATH."/lib_cgn_mvc_table.php");


Cgn::loadModLibrary('Forum::Forum_Queries');
Cgn::loadModLibrary('Forum::Forum_Model');
Cgn::loadModLibrary('Forum::Forum_Post');
Cgn::loadModLibrary('Forum::Forum_Mvc_Table');
Cgn::loadModLibrary('Forum::Forum_Settings');

/**
 * Class Forums
 *
 * A redisign of the original forums
 * This is intended to provide a more robust
 * solution 
 *
 */
class Cgn_Service_Forum_Browse extends Cgn_Service {

	var $forumId = 0;

	function mainEvent (&$req, &$t) {

		$forumId = (int)$req->cleanInt('forum_id');
		$this->forumId = $forumId;

		$forum = new Forum_Model($forumId);
		$t['locked'] = $forum->isLocked();

		if ( ! $forum->isVisible() ) {
			$this->templateName = 'forum_invisible';
			return;
		}

		//how many topcis are read?
		/*
		$x = ClassForum_Settings::getLastForumVisit($u,$forum);
		 */


		$rpp = 15; //rows per page
		$cp = (int) $req->cleanInt('page'); //current page
		if ($cp < 1) {
			$cp = 1;
		}

		//FIXME check for closed, visibility, etc
		$dm = new Cgn_Mvc_TableModel_ForumTopic($forumId, $rpp, $cp);
		$dm->headers = array('&nbsp;','Topics', 'Started By', 'Replies', 'Last Reply');

//		$table = new Cgn_Mvc_TableView($dm);
		$table = new Cgn_Mvc_TableView_BrowseTopics($dm);
		$table->rowsPerPage = $rpp;
		$table->currentPage = $cp;
		$table->forumId = $forumId;
        $table->setColRenderer(0, new Cgn_Mvc_Table_NewMessageRenderer($req->getUser()) );
		$table->setColWidth(0, '64');
		$table->setColAlign(0, 'left');


		$table->attribs = array('width'=>'100%','border'=>0,'cellpadding'=>'3','cellspacing'=>'0');
		$table->setColWidth(1, '100%');
		$table->setColAlign(1, 'left');

		$table->setColWidth(3, '60');

		$table->setColWidth(4, '160');

		$table->setColRenderer(1, new Cgn_Mvc_Table_TopicRenderer());
		$table->setColRenderer(4, new Cgn_Mvc_Table_TopicSubjectRenderer());

		$table->toHtml();

		/*
		$columnModel = &$table->getColumnModel();

		$col_c = &$columnModel->getColumnAt(4);
		$col_c->maxWidth=160;
		$col_c->cellRenderer = new LC_TableRenderer_ForumLastReply();
		$col_c->cellRenderer->dateFormat = 'M j, Y - h:i A';

		 */

		$t['forumId'] = $forumId;
		$t['forumName'] = $forum->getName();
		$t['table'] = $table;
	}
}




class Cgn_Mvc_TableModel_ForumTopic extends Cgn_Mvc_TableModel {

	var $posts = array();

	/**
	 * Paged topic listing of a forum
	 */
	function Cgn_Mvc_TableModel_ForumTopic($fid, $rpp, $cp) {
		$this->rowsPerPage = $rpp;
		$this->currentPage = $cp;

		$this->posts = Forum_Post::getTopics($fid, $rpp, ($cp-1) * $rpp);
		$this->maxPosts = Forum_Model::staticGetTopicCount($fid);
	}


	//sub-class
	/**
	 * Returns the number of rows in the model.
	 */
	function getRowCount() {
		return (count($this->posts));
	}


	//sub-class
	/**
	 * Returns the number of rows in the model.
	 */
	function getMaxRows() {
		return $this->maxPosts;
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
				return 'Topics'; break;

			case '2':
				return 'Started By'; break;

			case '3':
				return 'Replies'; break;

			case '4':
				return 'Last Reply'; break;
		}
	}


	/**
	 * return the value at an x,y coord
	 */
	function getValueAt($x,$y) {
		$post = $this->posts[$x];
		switch ($y) {
			case 0:
				return $post;
			case 1:
				return $post;
			case 2:
				return $post->_dao->user_id;
			case 3:
				return $post->getReplyCount();
			case 4:
				return $post->getLastReply();
		}
	}
}



class Cgn_Mvc_TableView_BrowseTopics 
	extends Cgn_Mvc_TableView {



	function toHtml($id='') {
		$html  = '';
		$html .= $this->printOpen();
		$rows = $this->_model->getRowCount();
		$cols = $this->_model->getColumnCount();

		//do table headers
		/*
		$headers = $this->_model->headers;
		if (count($headers) > 0) { 
			$html .= '<tr class="'.$this->cssPrefix.'_tr_h">'."\n";
			for($y=0; $y < $cols; $y++) {
				$datum = $this->_model->getHeaderAt(null,$y);
				$colWidth = $this->getColWidth($y);
				$colAlign = $this->getColAlign($y);
				$html .= '<th class="'.$this->cssPrefix.'_th" '.$colWidth.' '.$colAlign.'>'.$datum.'</th>'."\n";
			}
			$html .= '</tr>'."\n";
		}
		 */

		for($x=0; $x < $rows; $x++) {
			if ($x%2==0) {
				$rowclass = $this->cssPrefix.'_tr even_row';
				$cellclass = $this->cssPrefix.'_td even_cell';
			} else {
				$rowclass = $this->cssPrefix.'_tr odd_row';
				$cellclass = $this->cssPrefix.'_td odd_cell';
			}
			//first row is icon and title
			$html .= '<tr class="'.$rowclass.'">'."\n";

			$img = $this->_model->getValueAt($x,0);
			if (isset ($this->colRndr[0]) &&
				$this->colRndr[1] instanceof Cgn_Mvc_Table_ColRenderer) {
					$img = $this->colRndr[0]->getRenderedValue($img, $x, 0);
				}
			$title = $this->_model->getValueAt($x,1);
			if (isset ($this->colRndr[1]) &&
				$this->colRndr[1] instanceof Cgn_Mvc_Table_ColRenderer) {
					$title = $this->colRndr[1]->getRenderedValue($title, $x, 1);
				}

			$lastPost = $this->colRndr[4]->getRenderedValue(
			    $this->_model->getValueAt($x,4),
				$x, 1);


			$html .= '<td class="'.$cellclass.'">'.$img.' '.$title.'</td>';
			$html .= '</tr>'."\n";
			$html .= '<tr class="'.$rowclass.'">'."\n";
			$html .= '<td class="'.$cellclass.'" style="border-top:1px dashed silver">'.
				'&nbsp;Replies: '.$this->_model->getValueAt($x,3)
				.'&nbsp;&mdash;&nbsp;By: '.$this->_model->getValueAt($x,0)->getUser()
				.'&nbsp;&mdash;&nbsp;Last Reply: '.$lastPost
				.'</td>';
			$html .= '</tr>'."\n";

		}


		if ($rows < 1) {
			$html .= '<tr class="'.$rowclass.'"><td class='.$cellclass.'><em>No records found.</em></td></tr>';
		}
		$html .= $this->printClose();
		return $html;
	}

}
?>
