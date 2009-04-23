<?php

class Forum_Model extends Cgn_Data_Model {

	var $dataItem = null;
	var $postCount = -1;
	var $topicCount = -1;

	function Forum_Model($id=-1) {
		$this->dataItem = new Cgn_DataItem('cgn_forum');
		if ($id > 0) {
			$this->load($id);
		}
	}


	/*
	function load($forumId) {
		$x = new Cgn_DataItem('cgn_forum');
		$x->load($forumId);
		$y = new Forum_Model();
		$y->dataItem= $x;
		return $y;
	}
	 */


	/**
	 * Save
	 */
	function save() {
		return $this->dataItem->save();
	}

	/**
	 * Load all data items
	 *
	 * @static
	 */
	function getAll() {
		$loader = new Cgn_DataItem('cgn_forum');
        $items = $loader->find();
        $allForums = array();
        foreach ($items as $dataItem) {
            $x = new Forum_Model();
            $x->dataItem = $dataItem;
            $allForums[] = $x;
            unset($x);
        }
        return $allForums;
	}



    function isVisible() {
        return true;
    }

    function isLocked() {
        return false;
    }

	/**
	 * Is this person a moderator? for right now just check if
	 * the person is a faculty member, and the forum matches their 
	 * class id.
	 */
	function isModerator($u) {
		return $u->belongsToGroup('faculty');
	}

    function getName() {
        return $this->dataItem->name;
    }

	/**
	 * Get a count of topics under this forum
	 *
	 * @static
	 */
	function staticGetTopicCount($fid=-1) {
		if ($fid < 0 ) {
			return 0;
		}

		$db = Cgn_Db_Connector::getHandle();
		$db->query(
			Forum_Queries::getQuery('topicCountForum',
				array($fid)
			)
		);
		$db->nextRecord();
		return $db->record['num'];
	}

	/**
	 * Get a list of topics  Cgn_Post_List
	 */
	function getTopics($u, $limit=-1, $start=-1) {

		$forumId = intval($this->dataItem->cgn_forum_id);

		$postList = new Forum_Post_List();

		if ($this->getBrowseMode() != '') {
			$postList->sharingModeRead   = $this->getBrowseMode();
		}
		$posts = $postList->getTopics($u, $forumId);
		return $posts;
	}



	/**
	 * Get a count of posts under this forum, excluding topics
	 * (topics are thread starters, posts that were not a reply to anything)
	 */
	function getPostCount() {
		if ($this->postCount < 0) {
			$db = Cgn_Db_Connector::getHandle();
			$db->query(
				Forum_Queries::getQuery('replyCountForum',
					array($this->dataItem->getPrimaryKey())
				)
			);
			$db->nextRecord();
			$this->postCount = $db->record['num'];
		}
		return $this->postCount;
	}

	/**
	 * Get a count of topics under this forum
	 */
	function getTopicCount() {
		if ($this->topicCount < 0) {
			$db = Cgn_Db_Connector::getHandle();
			$db->query(
				Forum_Queries::getQuery('topicCountForum',
					array($this->dataItem->getPrimaryKey())
				)
			);
			$db->nextRecord();
			$this->topicCount = $db->record['num'];
		}
		return $this->topicCount;
	}

    function getLastPostTime() {

		$db = Cgn_Db_Connector::getHandle();
		$db->query(
			Forum_Queries::getQuery('lastPostForum',
				array($this->dataItem->getPrimaryKey())
			)
		);
		$db->nextRecord();
		$this->lastPostTime = $db->record['last_post_time'];
		return (int)$this->lastPostTime;
    }

    function getLastVisit() {
        return time();
    }

    function getCategoryId() {
        return $this->dataItem->cgn_forum_category_id;
    }

	function getId() {
		return $this->dataItem->cgn_forum_id;
	}

	/**
	 * Return the sharing mode required to browse posts
	 */
	function getBrowseMode() {
		return @$this->dataItem->browse_mode;
	}
}

class Forum_Model_List extends Cgn_Data_Model_List {
	var $dataItem = null;
	var $postCount = -1;
	var $topicCount = -1;

	function Forum_Model_List($id=-1) {
		$this->dataItem = new Cgn_DataItem('cgn_forum');
		if ($id > 0) {
			$this->dataItem->load($id);
		}
	}


	/**
	 * @param $u Cgn_User the user in question
	 */
	function loadVisibleList($u = NULL) {
		$ret = parent::loadVisibleList($u);
		$list = array();
		foreach ($ret as $key => $val) {
			$x = new Forum_Model();
			$x->dataItem = $val;	
			$list[] = $x;
		}
		return $list;
	}
}

?>
