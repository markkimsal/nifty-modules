<?php

/**
 * Handle all aspects of forum posts
 */
class Forum_Post extends Cgn_Data_Model {

	var $dataItem;
	var $posts         = array();
	var $threadLoaded = FALSE;

	var $tableName = 'cgn_forum_post';

	var $parentIdField = 'cgn_forum_id';
	var $parentTable = 'cgn_forum';

	var $sharingModeRead   = 'parent-group';
	var $sharingModeUpdate = 'same-owner';

	var $searchIndexName = 'sforum';
	var $useSearch       = TRUE;

	/**
	 */
	function  initDataItem() {
		$this->dataItem = new Cgn_DataItem($this->tableName);

		$this->dataItem->_nuls[] = 'thread_id';
		$this->dataItem->_nuls[] = 'is_sticky';
	}

/*
	function load($postId) {
		$x = new Cgn_DataItem('cgn_forum_post');
		$x->_nuls[] = 'thread_id';
		$x->load($postId);
		$y = new Forum_Post();
		$y->dataItem = $x;
		return $y;
	}
 */


	/**
	 * Wraps a different DAO under the same user class
	 */
	function loadFromTrash($threadId) {
		$query = ' thread_id='.$threadId.' and reply_id IS NULL ';
		$list = ForumTrashPostPeer::doSelect($query);

		$x = $list[0];
		if  (!is_object($x) ) {
			$x = new ForumTrashPost();
		}
		$y = new Forum_Posts();
		$y->dataItem = $x;
		return $y;
	}


	/**
	 * Retrieve a list of all posts written by a user
	 *
	 * Only get posts of a certain group
	 *
	 * @static
	 * @param forumId int id of the forum
	 */
	function getGroupPostsByUser($class_id, $username, $start=-1, $limit=-1) {

		$forumId = intval($forumId);

		$db = Cgn_Db_Connector::getHandle();
		$db->query(
			Forum_Queries::getQuery('getGroupPostsByUser',
				array($class_id,$username)
			)
		);

		$objList = array();
		while ($db->nextRecord()) {
			$v = ForumPostPeer::row2obj($db->record);
			$x = new Forum_Post();
			$x->dataItem = $v;
			$objList[] = $x;
		}
		return $objList;
	}


	/**
	 * Retrieve a list of all 'topic' posts in a class forums 
	 * Topics have a null thread ID because they are not
	 * replying to any particular topic.
	 *
	 * @static
	 * @param forumId int id of the forum
	 */
/*
	function getTopics($forumId, $limit=-1, $start=-1) {

		$forumId = intval($forumId);
		$dataItem = new Cgn_DataItem('cgn_forum_post');
		$dataItem->andWhere('cgn_forum_id', $forumId);
		$dataItem->orderBy('post_datetime DESC');
//		$dataItem->andWhere('thread_id','cgn_forum_post_id');
		if ($limit > -1) {
			$dataItem->limit($limit,$start);
		}
		$dataItem->_rsltByPkey = false;
		$list =  $dataItem->find(array('thread_id = cgn_forum_post_id'));

		$objList = array();
		foreach ($list as $k=>$v) {
			$x = new Forum_Post();
			$x->dataItem = $v;
			$objList[] = $x;
		}
		return $objList;
	}
*/


	/**
	 * Retrieve a list of all 'topic' posts in the trash
	 * Topics have a null thread ID because they are not
	 * replying to any particular topic.
	 *
	 * @static
	 * @param forumId int id of the forum
	 */
	function getTrashTopics($classId, $limit=-1, $start=-1) {

		if ($limit > -1) {
			$query = Forum_Queries::getQuery('trashTopicsLimit',
				array($classId,$start,$limit)
				);

		} else {
			$query = Forum_Queries::getQuery('trashTopics',
				array($classId)
				);
		}

		$db = Cgn_Db_Connector::getHandle();
		$db->query(
			$query
		);
		$objList = array();
		while ( $db->nextRecord() ) {
			$x = new Forum_Post();
			$v = ForumTrashPostPeer::row2obj($db->record);
			$x->dataItem = $v;
			$objList[] = $x;
		}

		return $objList;
	}


	/**
	 * get's the entire thread, including topic starter
	 *
	 * if this post is not a topic starter, this function will
	 * return nothing.
	 */
	function getThread($limit=-1, $start=-1) {

		if ($this->threadLoaded) {
			return $this->posts;
		}
		$topicId = intval($this->getPostId());
		$dataItem = new Cgn_DataItem('cgn_forum_post');
		$dataItem->andWhere('thread_id',$topicId);
//		$dataItem->andWhere('thread_id','NULL','IS NOT');
		$dataItem->orderBy('is_sticky');
		$dataItem->orderBy('post_datetime ASC');
		if ($limit > -1) {
			$dataItem->limit($limit,$start);
		}
		$list = $dataItem->find();
		foreach ($list as $k=>$v) {
			$x = new Forum_Post();
			$x->dataItem = $v;
			$this->posts[] = $x;
		}
		$this->threadLoaded = true;
		return $this->posts;
	}


	/**
	 * get's the entire thread from the trash
	 */
	function getTrashThread($limit=-1, $start=-1) {

		$threadId = intval($this->dataItem->get('threadId'));
		$query =' thread_id='.$threadId.' ORDER BY is_sticky DESC, post_datetime ASC';
		if ($limit > -1) {
			$query .= ' LIMIT '.$start.', '.$limit;
		}
		$list = ForumTrashPostPeer::doSelect($query);

		foreach ($list as $k=>$v) {
			$x = new Forum_Post();
			$x->dataItem = $v;
			$this->posts[] = $x;
		}
		$this->threadLoaded = true;
		return $this->posts;
	}


	/**
	 */
	function getLastReplyTime() {
		$db = Cgn_Db_Connector::getHandle();
		$db->query(
			Forum_Queries::getQuery('lastReplyThread',
				array($this->dataItem->getPrimaryKey())
			)
		);
		$db->nextRecord();

		$this->lastPostTime = $db->record['last_post_time'];
		return (int)$this->lastPostTime;
	}


	/**
	 * Wrapper function so table render's can call one function
	 */
	function getLastPostTime() {
		$db = Cgn_Db_Connector::getHandle();
		$db->query(
			Forum_Queries::getQuery('lastPostThread',
				array($this->dataItem->getPrimaryKey())
			)
		);
		$db->nextRecord();

		$this->lastPostTime = $db->record['last_post_time'];
		return (int)$this->lastPostTime;
	}


	/**
	 * This is a wrapper function for the Forum_Settings so
	 * that the table renderers can call one function, be it against
	 * a forum or a post
	 */
	function getLastVisit($u) {
		return Forum_Settings::getLastThreadVisit($u,$this);
	}


	function getForum() {
		$forum = new Forum_Model();
		$forum->load($this->dataItem->cgn_forum_id);
		return $forum;
	}


	/**
	 * getThread returns the entire thread, including the topic starter
	 * so the number of posts is the thread - 1
	 *
	 * @return int reply count for this post
	 */
	function getReplyCount() {
		if ( !$this->threadLoaded ) {
			$this->getThread();
		}
		return count($this->posts)-1;
	}

	function getLastReply() {
		if ($this->threadLoaded) {
			return $this->posts[ count($this->posts)-1 ];
		}
		//just load the last reply only
		$topicId = intval($this->getPostId());
		$dataItem = new Cgn_DataItem('cgn_forum_post');
		$dataItem->andWhere('thread_id',$topicId);
		$dataItem->andWhere('thread_id','NULL','IS NOT');
		$dataItem->orderBy('is_sticky');
		$dataItem->orderBy('post_datetime ASC');
		if ($limit > -1) {
			$dataItem->limit($limit,$start);
		}
		$list = $dataItem->find();
		$x = new Forum_Post();
		$x->dataItem = next($list);
		return $x;
	}


	function getForumId() {
		return isset($this->dataItem->cgn_forum_id) ? $this->dataItem->cgn_forum_id: null;
	}


	function getPostId() {
		return isset($this->dataItem->cgn_forum_post_id) ? $this->dataItem->cgn_forum_post_id: null;
	}


	function getThreadId() {
		return $this->dataItem->threadId;
	}


	function getUser() {
		return $this->dataItem->user_name;
	}

	function getUserId() {
		return $this->dataItem->user_id;
	}

	function setThreadId($id) {
		$this->dataItem->thread_id = $id;
	}


	function setUser($userObj) {
		$this->dataItem->user_name = $userObj->getDisplayName();
		$this->dataItem->user_id = $userObj->getUserId();
	}


	function setSubject($s) {
		$this->dataItem->subject = htmlentities($s);
	}


	function setMessage($m) {
		$this->dataItem->message = htmlentities($m);
	}

	function setForumId($id) {
		$this->dataItem->cgn_forum_id = intval($id);
	}


	/**
	 * Returns unix timestamp of when this post was posted
	 *
	 */
	function getTime() {
		return $this->dataItem->post_datetime;
	}


	/**
	 * gets the raw message
	 *
	 * needed, for example, if you want to edit the message
	 * in a text area.
	 */
	function getMessage() {
		return $this->dataItem->message;
	}


	/**
	 * gets the beginning of raw message
	 *
	 * show the first 65 characters, and remove the [QUOTE] tags
	 */
	function getMessageIntro($len=65) {
		return substr(Forum_Post::removeForumTags($this->dataItem->message), 0, $len);
//		return Forum_Post::swapForumTags( substr($this->dataItem->message,0,$len));
	}


	/**
	 * gets the message for showing in HTML
	 */
	function showMessage() {
		return Forum_Post::swapForumTags($this->dataItem->message);
	}



	function getSubject() {
		return $this->dataItem->subject;
	}


	/**
	 * Changes [TAG] into <TAG>
	 *
	 * only works for QUOTE, CODE, B, I.
	 * don't use nl2br on text inside [CODE] tags because it's
	 * already in a PRE tag.
	 * @static
	 * @param string $code the forum code that needs to be converted
	 * @return string HTML ready code
	 */
	function swapForumTags($code) {
		$code = nl2br($code);

		//this is an expensive operation, only do it if we have
		// find a code tag
		if (stripos($code, '[/CODE]') > 1 ) {
		//remove BR tags inbetween CODE tags because they are going to have
		// a PRE around them
		$code = preg_replace_callback(
			"#\[CODE([^\]]*)\](((?!\[/?CODE(?:[^\]]*)\]).)*)\[/CODE\]#si",
			create_function(
				//nl2br doesn't replace the new line, it only appends to it
				'$matches',
				'return "[CODE]".str_replace("<br />","", $matches[2])."[/CODE]";'
			),
		$code);
		}


		//do regular FORUM CODE tag to html tag replacement
		$code = str_ireplace('[QUOTE]','<div class="forum_quote_shell"><b>Quote:</b><blockquote class="forum_quote">',$code);
		$code = str_ireplace('[/QUOTE]','</blockquote></div>',$code);

		//sneak the original codeText back in, after the nl2br
		$code = str_ireplace('[CODE]','<div class="forum_code_shell"><b>Code:</b><blockquote class="forum_code"><pre>',$code);
		$code = str_ireplace('[/CODE]','</pre></blockquote></div>',$code);

		//BOLD
		$code = str_replace('[B]','<B>',$code);
		$code = str_replace('[/B]','</B>',$code);

		$code = str_replace('[b]','<B>',$code);
		$code = str_replace('[/b]','</B>',$code);

		//ITALICS
		$code = str_ireplace('[I]','<I>',$code);
		$code = str_ireplace('[/I]','</I>',$code);

		$code = str_ireplace('[i]','<I>',$code);
		$code = str_ireplace('[/i]','</I>',$code);

		return $code;
	}

	function removeForumTags($code) {
		$code = nl2br($code);

		//do regular FORUM CODE tag to html tag replacement
		$code = str_ireplace('[QUOTE]','',$code);
		$code = str_ireplace('[/QUOTE]','',$code);

		//sneak the original codeText back in, after the nl2br
		$code = str_ireplace('[CODE]','',$code);
		$code = str_ireplace('[/CODE]','',$code);

		//BOLD
		$code = str_ireplace('[B]','',$code);
		$code = str_ireplace('[/B]','',$code);

		//ITALICS
		$code = str_ireplace('[I]','',$code);
		$code = str_ireplace('[/I]','',$code);
		return $code;
	}



	/**
	 * Save.
	 *
	 * put the first 25 chars of the post if there's no subject
	 *
	 */
	function save() {
		if ($this->dataItem->is_sticky == 0) {
			$this->dataItem->is_sticky = NULL;
		}
		if ( strlen ($this->dataItem->subject) < 1 ) {
			$this->dataItem->subject = 
				substr($this->dataItem->message,0,25).'...';
		}
		return parent::save();
	}


	/**
	 * Make a copy of this post and all replies in the trash table
	 */
	function trashThread() {
		$this->getThread();
		foreach ($this->posts as $x=>$v) {
			$v->trash();
		}
		//the post itself is included in the replies array
		//get thread is the entire thread
	}


	/**
	 * Make a copy of this post in the trash table
	 */
	function trash() {
		$attribs = array('classForumId','isHidden','isSticky',
			'lastEditDatetime','lastEditUsername',
			'message','postDatetime','replyId',
			'subject','threadId','userId');

		$trashPost = new ForumTrashPost();
		for ($x=0; $x < count($attribs); ++$x) {
			$trashPost->set($attribs[$x],$this->dataItem->get($attribs[$x]));
		}

		$okay = $trashPost->save();
		$okay &= $this->dataItem->delete();
		return $okay;
	}


	/**
	 * Make a copy of this post and all replies in the trash table
	 *
	 * all replies and the topic starter should share the same thread_id
	 * which is equal to the topic starter's primary key
	 */
	function unTrashThread() {
		$this->getTrashThread();
		$oldThreadId = $this->getThreadId();
		//take off the topic starter, which is included in the list of posts
		$oldTopic = array_shift($this->posts);
		$newThreadId = $oldTopic->unTrash();
		foreach ($this->posts as $x=>$v) {
			$v->unTrash();
		}
		//the post itself is included in the posts array
		//get thread is the entire thread

		//update all the old posts to the new thread id
		//fix reply_id where it is not null
		//then match the thread_id to the new pkey
		$db = Cgn_Db_Connector::getHandle();
		$db->query(
			Forum_Queries::getQuery('updateReplyId',
				array($newThreadId,$oldThreadId)
			));
		$db->query(
			Forum_Queries::getQuery('updateThreadId',
				array($newThreadId,$oldThreadId)
			));
	}


	/**
	 * Make a copy of this post in the trash table
	 */
	function unTrash() {
		$attribs = array('classForumId','isHidden','isSticky',
			'lastEditDatetime','lastEditUsername',
			'message','postDatetime','replyId',
			'subject','threadId','userId');

		$unTrashPost = new ForumPost();
		for ($x=0; $x < count($attribs); ++$x) {
			$unTrashPost->set($attribs[$x],$this->dataItem->get($attribs[$x]));
		}

		$unTrashPost->save();
		$pkey = $unTrashPost->getPrimaryKey();
		$this->dataItem->delete();
		if ( $pkey ) {
			return $pkey;
		} else { 
			trigger_error ('Cannot untrash forum thread.  Insert did not yield primary key.');
			return false;
		}
	}
}


class Forum_Post_List extends Cgn_Data_Model_List {
	var $dataItem = null;


	var $sharingModeRead   = '';
	var $sharingModeCreate = '';

	function Forum_Post_List($id=-1) {
		$this->dataItem = new Cgn_DataItem('cgn_forum_post');
		if ($id > 0) {
			$this->dataItem->load($id);
		}
	}


	/**
	 * @param $u Cgn_User the user in question
	 */
	function loadVisibleList($u = NULL, $forumId) {

		$this->dataItem->andWhere('cgn_forum_id', $forumId);
		$ret = parent::loadVisibleList($u);
		if (!$ret) { return $ret; }
		$list = array();
		foreach ($ret as $key => $val) {
			$x = new Forum_Post();
			$x->dataItem = $val;	
			$list[] = $x;
		}
		return $list;
	}


	/**
	 * @param $u Cgn_User the user in question
	 */
	function getTopics($u = NULL, $forumId) {
		$this->dataItem->andWhere('cgn_forum_id', $forumId);
		$ret = parent::loadVisibleList($u, 'thread_id = cgn_forum_post_id');
		if (!$ret) { return $ret; }
		$list = array();
		foreach ($ret as $key => $val) {
			$x = new Forum_Post();
			$x->dataItem = $val;	
			$list[] = $x;
		}
		return $list;
	}


	/**
	 * @param $u Cgn_User the user in question
	 */
	function getThread($topicId, $limit=-1, $start=-1, $u=NULL) {
		$this->dataItem->andWhere('thread_id', $topicId);
		$this->dataItem->orderBy('is_sticky');
		$this->dataItem->orderBy('post_datetime ASC');

		$ret = parent::loadVisibleList($u);
		if (!$ret) { return $ret; }
		$list = array();
		foreach ($ret as $key => $val) {
			$x = new Forum_Post();
			$x->dataItem = $val;	
			$list[] = $x;
		}
		return $list;
	}
}

