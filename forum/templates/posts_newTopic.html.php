
<div>
	<a href="<?= cgn_appurl('forum');?>">Forum Home</a> / 
	<a href="<?= cgn_appurl('forum','browse','',array('forum_id'=>$t['forumId']));?>"><?=$t['forumName'];?></a>
</div>
<hr/>

<form method="POST" action="<?php echo cgn_appurl('forum','posts','startTopic');?>">
	<? print('Subject');?>:<br/>
	<input type="text" size="45" name="subject" style="width:90%;" value="<?=$t['subject'];?>"/>
	<br/>
	<? print('Message');?>:<br/>
	<textarea rows="45" cols="80" style="height:400px;width:90%" name="message"><?= $t['message'];?></textarea>

	<br/>
	<br/>
	<input type="submit" name="submit_topic" id="submit_topic" value="Start Topic"/>
	<input type="hidden" name="forum_id" id="forum_id" value="<?=$t['forumId'];?>"/>
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
