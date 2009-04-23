<?php

/**
 * Holds all the raw queries for the class forums system
 */
class Forum_Queries {

	var $queries = array();


	function getQuery($name,$args) {
		$singleton = Forum_Queries::singleton();
		$s_args = array_merge( array($singleton->queries[$name]), $args);
		return call_user_func_array('sprintf', $s_args);
	}


	/**
	 * create the SQL statements
	 */
	function init() {
		$this->queries['forumCountCategory']  = 
		'SELECT count(cgn_forum_id) as num
		FROM `cgn_forum`
		WHERE cgn_forum_category_id = %d
		AND class_id = %d';

		$this->queries['moveForum']  = 
		'UPDATE `cgn_forum`
		SET cgn_forum_category_id = %d
		WHERE cgn_forum_id = %d 
		AND class_id = %d';

		$this->queries['postCountForum']  = 
		'SELECT count(cgn_forum_post_id) as num
		FROM `cgn_forum_post`
		WHERE cgn_forum_id = %d
		AND thread_id IS NOT NULL';

		$this->queries['postCountThread']  = 
		'SELECT count(cgn_forum_post_id) as num
		FROM `cgn_forum_post`
		WHERE cgn_forum_id = %d
		AND thread_id = %d';

		$this->queries['replyCountForum']  = 
		'SELECT count(cgn_forum_post_id) as num
		FROM `cgn_forum_post`
		WHERE cgn_forum_id = %d
		AND reply_id IS NOT NULL';

		$this->queries['topicCountForum']  = 
		'SELECT count(cgn_forum_post_id) as num
		FROM `cgn_forum_post`
		WHERE cgn_forum_id = %d
		AND thread_id = cgn_forum_post_id';

		$this->queries['topicCountTrash']  = 
		'SELECT count(cgn_forum_trash_post_id) as num
		FROM `cgn_forum_trash_post` A
		LEFT JOIN cgn_forum B
		  ON A.cgn_forum_id = B.cgn_forum_id
		WHERE reply_id IS NULL
		AND B.class_id = %d';

		$this->queries['forumsSorted'] = 
		'SELECT A.*
		FROM cgn_forum A
		LEFT JOIN cgn_forum_category B
		  ON A.cgn_forum_category_id = B.cgn_forum_category_id
		WHERE A.class_id=%d ORDER BY B.name, A.cgn_forum_category_id, A.name ASC';

		$this->queries['unsetAllForums']  = 
		'UPDATE `cgn_forum`
		SET %s = 0
		WHERE class_id = %d';

		$this->queries['setForum']  = 
		'UPDATE `cgn_forum`
		SET %s = 1
		WHERE cgn_forum_id = %d
		AND class_id = %d';

		$this->queries['lastPostForum']  = 
		'SELECT MAX(post_datetime) as last_post_time
		FROM `cgn_forum_post`
		WHERE cgn_forum_id = %d';

		$this->queries['lastReplyThread']  = 
		'SELECT MAX(post_datetime) as last_post_time
		FROM `cgn_forum_post`
		WHERE thread_id = %d
		AND reply_id IS NOT NULL';

		$this->queries['lastPostThread']  = 
		'SELECT MAX(post_datetime) as last_post_time
		FROM `cgn_forum_post`
		WHERE thread_id = %d';

		$this->queries['moveThreadForum']  = 
		'UPDATE `cgn_forum_post`
		SET cgn_forum_id = %d
		WHERE thread_id = %d';

		$this->queries['getUserViews']  = 
		'SELECT views 
		FROM `cgn_forum_user_activity`
		WHERE user_id = %d
		AND cgn_forum_id = %d';

		$this->queries['setUserViews']  = 
		'UPDATE `cgn_forum_user_activity`
		SET views = "%s"
		WHERE user_id = %d
		AND cgn_forum_id = %d';

		$this->queries['addUserViews']  = 
		'INSERT INTO `cgn_forum_user_activity`
		(views, user_id, cgn_forum_id)
		VALUES ("%s", %d, %d)';

		$this->queries['trashTopics']  = 
		'SELECT A.* 
		FROM cgn_forum_trash_post A
		LEFT JOIN cgn_forum B
		  ON A.cgn_forum_id = B.cgn_forum_id
		WHERE B.class_id = %d
		AND reply_id IS NULL';

		$this->queries['trashTopicsLimit']  = 
		'SELECT A.* 
		FROM cgn_forum_trash_post A
		LEFT JOIN cgn_forum B
		  ON A.cgn_forum_id = B.cgn_forum_id
		WHERE B.class_id = %d
		AND reply_id IS NULL
		LIMIT %d, %d';

		$this->queries['updateReplyId']  = 
		'UPDATE `cgn_forum_post`
		SET reply_id = %d
		WHERE thread_id = %d
		AND reply_id IS NOT NULL';

		$this->queries['updateThreadId']  = 
		'UPDATE `cgn_forum_post`
		SET thread_id = %d
		WHERE thread_id = %d';

		$this->queries['getGroupPostsByUser']  = 
		'SELECT A.* 
		FROM cgn_forum_post A
		LEFT JOIN cgn_forum B
		  using(cgn_forum_id)
		WHERE B.group_id = %d
		AND A.user_id="%s"';
	}


	/**
	 * PHP4 has no static class variables
	 */
	function &singleton() {
		static $singleton;
		if (! is_object($singleton) ) {
			$singleton = new Forum_Queries();
			$singleton->init();
		}

		return $singleton;
	}
}


