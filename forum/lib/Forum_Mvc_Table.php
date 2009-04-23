<?php

class Cgn_Mvc_Table_NewMessageRenderer extends Cgn_Mvc_Table_ColRenderer {

	var $u;

	function Cgn_Mvc_Table_NewMessageRenderer($u) {
		$this->u = $u;
	}

	function getRenderedValue($val, $x, $y) {
		if (! is_object($val) ) {
			return '';
		}

		//posts and forums both use this function, but only forums can be locked
		if ( method_exists($val, 'isLocked') && $val->isLocked() ) {
			return '<img height="32" width="32" src="'.cgn_url().'media/icons/default/messages_locked.png" title="new posts" alt="new posts"><br/>LOCKED';
		}

		$x = $val->getLastVisit($this->u);
		$y = $val->getLastPostTime();
		//echo "$x <br>$y <hr>";
		if ($y > $x ) {
			return '<img height="32" width="32" src="'.cgn_url().'media/icons/default/messages_new.png" title="new posts" alt="new posts" align="left">';//<br/>NEW MESSAGES';
		} else {
			return '<img height="32" width="32" src="'.cgn_url().'media/icons/default/messages_read.png" title="new posts" alt="new posts">';
		}
	}
}

class Cgn_Mvc_Table_ForumRenderer extends Cgn_Mvc_Table_ColRenderer {

	function getRenderedValue($val, $x, $y) {
		return '<a href="'.cgn_appurl('forum','browse','',array('forum_id'=>$val->getId())).'">'.$val->_item->name.'</a> <br/>'.$val->_item->description;
	}
}


class Cgn_Mvc_Table_TopicRenderer extends Cgn_Mvc_Table_ColRenderer {

	function getRenderedValue($val, $x, $y) {
		$intro = $val->getMessageIntro(145);
		return '<h3 style="display:inline;"><a href="'.cgn_appurl('forum','posts','',array('post_id'=>$val->getPostId())).'">'.$val->getSubject().'</a></h3><br style="clear:left;"/>'.$intro;
	}
}

class Cgn_Mvc_Table_TopicSubjectRenderer extends Cgn_Mvc_Table_ColRenderer {

	function getRenderedValue($val, $x, $y) {
		if ($val->getSubject() == '') {
			$subject = $val->getMessageIntro(25);
		} else {
			$subject = $val->getSubject();
		}
		return '<a href="'.cgn_appurl('forum','posts','',array('post_id'=>$val->getPostId())).'">'.htmlentities(strip_tags($subject), ENT_QUOTES).'</a>';
	}
}


class Cgn_Mvc_Table_ForumAuthor extends Cgn_Mvc_Table_ColRenderer {


    function getRenderedValue($val, $x, $y) {
        $ret .= $val;
        //$ret .= '<br/><img height="32" width="32" src="http://dev.logicampus.com/images/messages_new.png" title="new posts" alt="new posts"><br/>';
        //$ret .= '<br/>[PENDING PHOTO]';

        $ret .= '<br/><a href="'.cgn_appurl('users/view/'.$val).'">View Profile</a>';
        return $ret;
    }
}



class Cgn_Mvc_TableModel_ForumThread extends Cgn_Mvc_TableModel {

	var $topicId;
	var $topicObj;
	var $thread = array();

	function Cgn_Mvc_TableModel_ForumThread($u, $postId, $cp, $rpp) {
		$this->rowsPerPage = $rpp;
		$this->currentPage = $cp;
		$this->topicId = $postId;
		//$this->topicObj = new Forum_Post_List();
		$postList = new Forum_Post_List();
		$this->thread = $postList->getThread($postId, $this->rowsPerPage, (($this->currentPage-1) * $this->rowsPerPage), $u);
		$this->thread = $this->topicObj->getThread($this->rowsPerPage, (($this->currentPage-1) * $this->rowsPerPage));
		//if getThread returns nothing, the post is not
		// a thread starter, so we will just show only this post.
		if (count($this->thread) <1) {
			$this->thread[] =$this->topicObj;
		}
		$this->topicObj = $this->thread[0];
	}


	function getRowCount() {
		//FIXME this won't work on the last page
		// maybe it will (?)
		$x = count($this->thread);
		if ($x > $this->rowsPerPage) {
			return $this->rowsPerPage;
		}
		return $x;
	}


	function getMaxRows() {
		return 100;
	}


	/**
	 * Returns the number of cols in the model.
	 */
	function getColumnCount() {
		return 2;
	}


	/**
	 * Returns the name of a column.
	 */
	function getColumnName($columnIndex) {
		switch ($columnIndex) {
			case '0':
				return 'Author'; break;

			case '1':
				if (is_object($this->topicObj) ) {
					return 'Post: '.$this->topicObj->_dao->subject;
				} else {
					return 'Post';
				}
				break;

		}
	}

	/**
	 * Return the forum id of this thread
	 */
	function getForumId() {
		if ( is_object($this->topicObj) ) {
			return $this->topicObj->getForumId();
		} else {
			return false;
		}
	}

	/**
	 * return the value at an x,y coord
	 */
	function getValueAt($x,$y) {
		$post = $this->thread[$x];

		switch ($y) {
			case 0:
				return $post->getUser();
			case 1:
				return $post;
		}
	}
}


class Cgn_Mvc_Table_ForumContent extends Cgn_Mvc_Table_ColRenderer {

	var $dateFormat = 'M d y';
	var $dateTimeFormat = 'M j, Y - h:i A';
	var $userIsModerator = false;
	var $username = '';

	function getRenderedValue($val, $x, $y) {
		$ret  = '<div style="float:left">posted on : '.date($this->dateTimeFormat,$val->getTime());
		if ($val->_dao->last_edit_datetime > 0 ) {
			$ret .= "<br/><span style=\"font-weight:bold\">edited on: " .date($this->dateTimeFormat,$val->_dao->last_edit_datetime). " by: ".$val->_dao->last_edit_username."</span>";
		}
		$ret .= '</div>';

		$ret .= '<div align="right">';
		$forum = $val->getForum();
		if ( !$forum->isLocked() ) {
		$ret .= '<a href="'.cgn_appurl('forum','posts','reply',array('post_id'=>$val->getPostId())).'">Reply</a> | ';
		$ret .= '<a href="'.cgn_appurl('forum','posts','reply',array('quote'=>'true','post_id'=>$val->getPostId())).'">Reply &amp; Quote</a> ';
		}

		if ($this->userIsModerator
			|| $val->getUser() == $this->username) {
			$ret .= ' | <a href="'.cgn_appurl('forum','posts','edit/post_id='.$val->getPostId()).'">Edit</a> ';
		}
		$ret .= '</div>';

		$ret .= "<hr style=\"clear:both\">\n\t\t";
		$ret .= $val->showMessage();
		//$ret .= "<hr>\n\t\t";
		//$ret .= $val->getPostId();

		$ret .= "\n<br><p>&nbsp;</p>\n\t\t";

		return $ret;
	}
}

