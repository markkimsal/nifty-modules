<h2>Project: <?= $t['proj']->title;?></h2>

<?=cgn_applink('Organize Changelog Entries','nx-changelog','main','clEntries',array('id'=>$t['cl']->getPrimaryKey()));?>
	
<form method="POST" action="<?=cgn_appurl('nx-changelog','main','saveCl');?>">
<h2>Prepare Changelog</h2>
Changelog Title:
<input type="text" size="40" name="title" id="title" value="<?=$t['cl']->get('title');?>" />
<br/>
<br/>

Release Notes:
<textarea name="notes" id="notes" rows="7" cols="50"><?=$t['cl']->get('description');?></textarea>
<br/>

Trunk URL:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="text" size="65" name="url1" id="url1" value="<?=$t['cl']->get('trunk_url');?>" />
<br/>
<br/>

Last Tag URL:&nbsp;&nbsp;&nbsp;&nbsp;
<input type="text" size="65" name="url2" id="url2" value="<?=$t['cl']->get('last_tag_url');?>" />
<br/>
<br/>

Trunk Rev:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="text" size="4" name="trunk_rev" value="<?=$t['cl']->get('trunk_rev');?>" />
<br/>
<br/>

Last Tag Rev:&nbsp;&nbsp;&nbsp;&nbsp;
<input type="text" size="4" name="last_tag_rev" value="<?=$t['cl']->get('last_tag_rev');?>" />
<br/>
<br/>


Force Re-download of Commit Messages?:&nbsp;&nbsp;&nbsp;&nbsp;
<input type="checkbox" value="on" name="force-dl">

<br/>
<br/>

<input type="hidden" name="proj_id"  value="<?=$t['proj']->get('nxc_project_id');?>" />
<input type="hidden" name="id"  value="<?=$t['cl']->get('nxc_changelog_id');?>" />
<input type="submit" name="sbmt-button" />
</form>
