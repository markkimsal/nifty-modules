<div>
	 <a href="<?= cgn_appurl('forum');?>">Forum Home</a> / 
	 <a href="<?= cgn_appurl('forum','browse','',array('forum_id'=>$t['forumId']));?>"><?=$t['forumName'];?></a>
	  / 
	<a href="<?= cgn_appurl('forum','posts','',array('post_id' =>$t['threadId']));?>"><?=$t['threadName'];?></a>
	 / New Reply
</div>

<hr/>

<form method="POST" action="<?php echo cgn_appurl('forum','posts','saveReply');?>">

	<? print('Message');?>:<br/>
	<textarea rows="45" cols="80" style="height:400px;width:90%" name="message"><?= $t['quote'];?></textarea>

	<br/>
	<br/>
	<input type="submit" id="submit_post" value="Post Reply"/>
	<input type="hidden" name="post_id" value="<?=$t['postId'];?>"/>
	<input type="hidden" name="thread_id" value="<?=$t['threadId'];?>"/>
</form>

<div>
	<fieldset>
		<legend>Legend</legend>
		<table border="0" cellpadding="0" cellspacing="0" width="100%">
			<tr>
				<th style="text-align:left;">Put these tags around your text</th>
				<th style="text-align:left;">...to get</th>
			</tr>
			<tr>
				<td style="border-bottom:1px solid silver;">[QUOTE] ... [/QUOTE]</td>
				<td style="border-bottom:1px solid silver;">Quoted Text</td>
			</tr>
			<tr>
				<td style="border-bottom:1px solid silver;">[CODE] ... [/CODE]</td>
				<td style="border-bottom:1px solid silver;">Pre-Formatted Text (for HTML, C++, PHP, etc...)</td>
			</tr>
			<tr>
				<td style="border-bottom:1px solid silver;">[I] ... [/I]</td>
				<td style="border-bottom:1px solid silver;">Italicized Text</td>
			</tr>
			<tr>
				<td style="border-bottom:1px solid silver;">[B] ... [/B]</td>
				<td style="border-bottom:1px solid silver;">Bolded Text</td>
			</tr>
		</table>
	</fieldset>
</div>
