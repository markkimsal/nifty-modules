
<div style="float:left;">
	<a href="<?= cgn_appurl('forum');?>">Forum Home</a> / 
	<?=$t['forumName'];?>
</div>
<hr style="clear:both;"//>

<?php if (! $t['locked']) { ?>
<form method="GET" action="<?php echo cgn_appurl('forum','posts', 'newTopic', array('forum_id'=>$t['forumId'])); ?>" style="background-color:transparent;border:none;text-align:right;">
	<input type="submit" name="start_topic" id="start_topic" value="Start New Topic"/>
</form>
<? } ?>

<?php if ( ! $x = $t['table']->toHTML() ) { ?>
	<br style="clear:both;"/>
	There are no posts in this forum.
<?php } else {
	echo $x;
} ?>

<p>&nbsp;</p>
<?php // TODO: only do this based on permissions ?>
<?php if (! $t['locked']) { ?>
<form method="GET" action="<?php echo cgn_appurl('forum','posts', 'newTopic', array('forum_id'=>$t['forumId'])); ?>" style="background-color:transparent;border:none;">
	<input type="submit" name="start_topic" id="start_topic" value="Start New Topic"/>
</form>
<? } ?>
