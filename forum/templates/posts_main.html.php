<div>
	 <a href="<?= cgn_appurl('forum');?>">Forum Home</a> / 
	 <a href="<?= cgn_appurl('forum','browse','',array('forum_id'=>$t['forumId']));?>"><?=$t['forumName'];?></a>
	  / <?=$t['topicName'];?>
</div>
<hr/>
<form style="text-align:right;border:none;background-color:transparent;" method="GET" action="<?= cgn_appurl('forum','posts','reply',array('post_id'=>$t['topicId']));?>">
<input type="submit" name="sbmt-button" value="Reply"/>
</form>
<?php
echo $t['threadTable']->toHtml();
?>
