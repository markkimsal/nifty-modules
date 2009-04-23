<?php

/**
 * Handle settings and last visit time
 */
class Forum_Settings {


	/**
	 * set a cookie with this forum's ID for one year
	 */
	function setLastThreadVisit($u,$thread) {
		if (! is_object($thread) ) {
			return 0;
		}
		if ($u->isAnonymous()) {
			return Forum_Settings::getDefaultOldTime();
		}
		$threadId = $thread->getPostId();
		$forumId = $thread->getForumId();
		$db = Cgn_Db_Connector::getHandle();
		$db->query(
			Forum_Queries::getQuery('getUserViews',
				array ($u->userId, $forumId)
			)
		);
		$db->nextRecord();
		if ( $db->getNumRows() < 1 ) {
			$queryName = 'addUserViews';
		} else {
			$queryName = 'setUserViews';
		}
		$viewStruct = unserialize(base64_decode($db->record['views']));
		$viewStruct['forum'][$forumId] = time();
		$viewStruct['thread'][$threadId] = time();
		$views = base64_encode(serialize($viewStruct));
		$db->query(
			Forum_Queries::getQuery($queryName,
				array ($views, $u->userId, $forumId)
			)
		);
	}


	function getLastForumVisit($u,$forum) {
		if (! is_object($forum) ) {
			return 0;
		}
		if ($u->isAnonymous()) {
			return Forum_Settings::getDefaultOldTime();
		}
		$forumId = $forum->getForumId();

		$db = Cgn_Db_Connector::getHandle();
		$db->query(
			Forum_Queries::getQuery('getUserViews',
				array ($u->userId, $forumId)
			)
		);
		$db->nextRecord();
		$viewStruct = unserialize(base64_decode($db->record['views']));
		return (int)$viewStruct['forum'][$forumId];
	}


	function getLastThreadVisit($u,$thread) {
		if (! is_object($thread) ) {
			return 0;
		}
		if ($u->isAnonymous()) {
			return Forum_Settings::getDefaultOldTime();
		}
		$threadId = $thread->getPostId();
		$forumId = $thread->getForumId();
		$db = Cgn_Db_Connector::getHandle();
		$db->query(
			Forum_Queries::getQuery('getUserViews',
				array ($u->userId, $forumId)
			)
		);
		$db->nextRecord();
		$viewStruct = unserialize(base64_decode($db->record['views']));
		return (int)$viewStruct['thread'][$threadId];
	}

	/**
	 * Return the default timestamp at which a message is considered to be "old"
	 *
	 * @return int   timestamp at which untracked users can consider a message to be read, or old.
	 */
	static function getDefaultOldTime() {
		//approx 1 month
		return time() - (60*60*24*30);
	}
}

