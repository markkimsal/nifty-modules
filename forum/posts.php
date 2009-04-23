<?php

include_once(CGN_LIB_PATH."/html_widgets/lib_cgn_widget.php");
include_once(CGN_LIB_PATH."/lib_cgn_mvc.php");
include_once(CGN_LIB_PATH."/lib_cgn_mvc_table.php");


Cgn::loadModLibrary('Forum::Forum_Model');
Cgn::loadModLibrary('Forum::Forum_Post');
Cgn::loadModLibrary('Forum::Forum_Mvc_Table');
Cgn::loadModLibrary('Forum::Forum_Settings');
Cgn::loadModLibrary('Forum::Forum_Queries');

/**
 * Class Forums : Posts
 *
 * A redisign of the original forums
 * This is intended to provide a more robust
 * solution.
 */
class Cgn_Service_Forum_Posts extends Cgn_Service_Trusted {

	function Cgn_Service_Forum_Posts() {
		$this->screenPosts();
		$this->trustPlugin('requireCookie');
		$this->trustPlugin('throttle',7);
		$this->trustPlugin('html',10);
//		$this->trustPlugin('secureForm');
	}


	/**
	 * Signal whether or not the user can access
	 * this service given event $e
	 */
	function authorize($e, $u) {
		switch ($e) {
			case 'reply':
			case 'saveReply':
			case 'startTopic':
			case 'newTopic':
				if ( $u->isAnonymous() ) {
					return false;
				}
		}
		return true;
	}

	/**
	 * show a post and any replies
	 */
	function mainEvent(&$req, &$t) {

		$u = $req->getUser();
		$postId = (int) $req->cleanInt('post_id');
		$page = (int) $req->cleanInt('page');
//		$topic = new Forum_Post($postId);

		if($page < 1) {
			$page = 1;
		}
		$rpp = 10;


		$dataModel = new Cgn_Mvc_TableModel_ForumThread($u, $postId, $page, $rpp);
		$topic = $dataModel->topicObj;
		$forum = new Forum_Model($dataModel->getForumId());
		//wasteful, decision to load thread should be done before loading
		if ($forum->getBrowseMode() == 'registered' && $u->isAnonymous()) {
			$this->templateName = 'permission_denied';
			return;
		}

		//permission failure
		if ($topic->dataItem->_isNew) {
			$this->templateName = 'forum_invisible';
			return;
		}

		$dataModel->headers = array('Author', 'Post');
		$table = new Cgn_Mvc_TableView($dataModel);
		$table->attribs = array('width'=>'100%','border'=>0,'cellpadding'=>'1','cellspacing'=>'1');
		$table->cssPrefix = 'forum_1';

		$t['locked'] = $forum->isLocked();

		if ( ! $forum->isVisible() ) {
			$this->templateName = 'forum_invisible';
			return;
		}

		//renderer for the first User column
		$userRenderer = new Cgn_Mvc_Table_ForumAuthor();
		//$userRenderer->username = $u->username;
		//$userRenderer->userIsModerator = $forum->isModerator($u);
		$table->setColRenderer(0, $userRenderer);
		$table->setColWidth(0,160);

		//renderer to render forum post content
		$contentRenderer = new Cgn_Mvc_Table_ForumContent();
		$contentRenderer->username = $u->getDisplayName();
		$contentRenderer->userIsModerator = $forum->isModerator($u);
		$table->setColRenderer(1, $contentRenderer);

//		$t['threadTable'] = new Cgn_Mvc_TableView($table);
		/*
		$columnModel = &$table->getColumnModel();
		$col = &$columnModel->getColumnAt(0);
		$col->maxWidth=100;
		$col->cellRenderer = new LC_TableRenderer_ForumAuthor();

		$col_d = &$columnModel->getColumnAt(1);
		$col_d->cellRenderer = new LC_TableRenderer_ForumPost();
		$col_d->cellRenderer->userIsModerator = $forum->isModerator($u);
		$col_d->cellRenderer->username = $u->username;
		$col_d->justify = 'left';

		$t['threadTable'] = new LC_TableRendererPaged($table);
		 */


		$x = Forum_Settings::getLastThreadVisit($u,$topic);

		Forum_Settings::setLastThreadVisit($u,$topic);


		$t['forumName'] = $forum->getName();
		$t['threadTable'] =$table;
		$this->forumId = $dataModel->getForumId();
		$t['moderator'] = $forum->isModerator($u);
		$t['postId'] = $postId;
		$t['forumId'] = $dataModel->getForumId();
		$t['topicName']  = $dataModel->topicObj->getSubject();
		$t['topicId']  = $dataModel->topicObj->getPostId();
	}


	/**
	 * Show form for editing post
	 */
	function editEvent(&$req, &$t) {

		$u = $req->getUser();
		$postId = (int) $req->getvars['post_id'];
		$post = new Forum_Post($postId);
		$forum = $post->getForum();
		if ( ($post->getUserId() != $u->getUserId()) &&
			! $forum->isModerator($u) ) {
			$t['message'] = "You are trying to edit a thread that you do not own.";
			$this->templateName = 'forum_locked';
			return false;
		}

		$t['subject'] = $post->getSubject();
		$t['message'] = $post->getMessage();
		$t['postId'] = $postId;
		$t['forumId'] = $forum->getId();
		$this->forumId = $forum->getId();
		$t['forumName'] = $forum->getName();
	}

	function updatePostEvent ($req, &$t) {

		$u = $req->getUser();
		$postId = (int) $req->postvars['post_id'];
		$post = new Forum_Post($postId);
		$forum = $post->getForum();
		$forumId = $forum->getId();
		if ( ($post->getUserId() != $u->getUserId()) &&
			! $forum->isModerator($u) ) {
			$t['message'] = "You are trying to edit a thread that you do not own.";
			$this->templateName = 'forum_locked';
			return false;
		}

		if (! is_object($forum) ) {
			trigger_error("Error loading forum");
			return false;
		}

		if ($forum->isLocked()) {
			$this->templateName = 'posts_forumLocked';
			return;
		}

		if ( ! $forum->isVisible() ) {
			$this->templateName = 'forum_invisible';
			return;
		}


		$post->setSubject($req->cleanString('subject'));
		$post->setMessage($req->cleanString('message'));
		$post->dataItem->last_edit_datetime = time();
		$post->dataItem->last_edit_username = $u->getDisplayName();

		$post->save();

		$t['url'] = cgn_appurl('support', 'browse', '', array('forum_id'=>$forumId));
		$this->presenter = 'redirect';
	}


	/*
	function updatePostRun (&$db, &$u, &$lc, &$t) {

		$postId = (int) $lc->postvars['post_id'];
		$post = ClassForum_Posts::load($postId);
		$forum = $post->getForum();
		$forumId = $forum->getForumId();
		if ( ($post->getUser() != $u->username) &&
			! $forum->isModerator($u) ) {
			$t['message'] = "You are trying to move a thread that you do not own.";
			$this->presentor = 'errorMessage';
			return false;
		}

		if (! is_object($forum) ) {
			trigger_error("Error loading forum");
			return false;
		}

		if ($forum->isLocked()) {
			$lc->templateName = 'posts_forumLocked';
			return;
		}

		if ( ! $forum->isVisible() ) {
			$lc->templateName = 'forum_invisible';
			return;
		}


		$post->setSubject($lc->postvars['subject']);
		$post->setMessage($lc->postvars['message']);
		$post->dataItem->set('lastEditDatetime',time());
		$post->dataItem->set('lastEditUsername',$u->username);

		$post->save();

		$t['url'] = appurl('classforums/forum/forum_id='.$forumId);
		$this->presentor = 'redirectPresentation';
	}
	 */


	function replyEvent(&$req, &$t) {

		$postId = (int) $req->cleanInt('post_id');
		$post = new Forum_Post($postId);
		if ($post->dataItem->_isNew) {
			Cgn_ErrorStack::throwError('Cannot load post.', 500);
			return false;
		}
		if ($post->dataItem->thread_id > 0 ) {
			$threadId = $post->dataItem->thread_id;
		} else {
			$threadId = $postId;
		}

		if ($req->cleanString('quote') == 'true') {
			$t['quote'] = '[QUOTE]'.$post->getMessage().'[/QUOTE]';
		}

		$forum = new Forum_Model($post->getForumId());
		if ($forum->isLocked()) {
			$this->templateName = 'posts_forumLocked';
			return;
		}

		if ( ! $forum->isVisible() ) {
			$this->templateName = 'forum_invisible';
			return;
		}

		$t['postId'] = $postId;
		$t['threadId'] = $threadId;
		$t['threadName'] = $post->getSubject();
		$t['forumId'] = $forum->getId();
		$this->forumId = $forum->getId();
		$t['forumName'] = $forum->getName();
	}


	function newTopicEvent(&$req, &$t) {

		$forum = new Forum_Model($req->cleanInt('forum_id'));
		if ($forum->dataItem->_isNew) {
			$this->templateName = 'posts_forumLocked';
			return false;
		}
		if ($forum->isLocked()) {
			$this->templateName = 'posts_forumLocked';
		}
		if ( ! $forum->isVisible() ) {
			$this->templateName = 'forum_invisible';
			return;
		}

		$t['forumId'] = $forum->getId();
		$this->forumId = $forum->getId();
		$t['forumName'] = $forum->getName();
	}


	/**
	 * Save a new topic in the DB
	 */
	function startTopicEvent(&$req, &$t) {

		$forumId = $req->cleanInt('forum_id');
		$forum = new Forum_Model($forumId);

		if ($forum->dataItem->_isNew) {
			$this->templateName = 'posts_forumLocked';
			return false;
		}

		if (! is_object($forum) ) {
			trigger_error("Error loading forum");
			return false;
		}

		if ($forum->isLocked()) {
			$this->templateName = 'posts_forumLocked';
			return;
		}

		if ( ! $forum->isVisible() ) {
			$this->templateName = 'forum_invisible';
			return;
		}

		$post = new Forum_Post();
		$post->setSubject($req->cleanString('subject'));
		$post->setMessage($req->cleanString('message'));
		$u = $req->getUser();
		$post->setUser($u);

		$post->setForumId($forumId);
//		$post->set('postDatetime',time());
/* do these in forum->postMessage
		$post->setForumId(0);
		$post->set('postDatetime',time());
*/
		$post->dataItem->post_datetime = time();

		$post->save();
		$post->setThreadId($post->getPostId());
		$post->save();

		$t['url'] = cgn_appurl('forum','browse','', array('forum_id'=>$forumId));
		$this->presenter = 'redirect';
	}


	/**
	 * Save a reply in the DB
	 */
	function saveReplyEvent(&$req, &$t) {

		$postId = $req->cleanInt('post_id');
		$threadId = $req->cleanInt('thread_id');
		if ($threadId < 1) { $threadId = $postId; }
		$topic = new Forum_Post($threadId);

		$forumId = $topic->getForumId();

		$forum = new Forum_Model($forumId);
		if (! is_object($forum) ) {
			trigger_error("Error loading forum");
			return false;
		}

		if ($forum->isLocked()) {
			$this->templateName = 'posts_forumLocked';
			return;
		}

		if ( ! $forum->isVisible() ) {
			$this->templateName = 'forum_invisible';
			return;
		}

		$post = new Cgn_DataItem('cgn_forum_post');
		$post->subject = htmlentities($req->cleanString('subject'));
		$post->message = htmlentities($req->cleanString('message'));
		$post->user_id = $req->getUser()->getUserId();
		$post->user_name = $req->getUser()->getDisplayName();
		$post->cgn_forum_id = $forumId;
		$post->reply_id = $postId;
		$post->thread_id = $threadId;
		$post->post_datetime = time();

		$post->save();

		$user = $req->getUser();
		$user->addSessionMessage("Post Saved.");

		$t['url'] = cgn_appurl('forum','browse','',array('forum_id'=>$forumId));
		$this->presenter = 'redirect';
	}

}


?>
