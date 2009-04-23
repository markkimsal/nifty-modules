<style type="text/css">

#table001 li.li_bul_off {
 list-style-type:none;list-style-image:none
}


#table001 li.li_bul_on {
list-style-image: url(http://<?= Cgn_Template::url()?>images/bullet.gif);
}

#table001 li.li_bul_err {
list-style-image: url(http://<?= Cgn_Template::url()?>images/bullet_err.gif);
}

</style>

<script language="Javascript"> 
	function jqsetCategory(link) {
		jQuery.getJSON($(link).attr('href')+'?xhr=1',
			function(data){
				if (data["status"] == 'okay') {
					toggleBulletStatus($(link));
				} else {
					toggleBulletError($(link));
				}
			}
		);
		}
	function toggleBulletStatus(link) {
		if ($(link).parent().attr('class') == 'li_bul_on') {
			$(link).parent().attr('class','li_bul_off');
			return false;
		}

		if ($(link).parent().attr('class') == 'li_bul_off') {
			$(link).parent().attr('class','li_bul_on');
			return false;
		}
//		$(link).parent().css('list-style-type', 'disc');
	}

	function toggleBulletError(link) {
		$(link).parent().attr('class','li_bul_err');
		return false;
	}

	</script>


<?php
echo    cgn_applink('Download in TXT','nx-changelog','main','download',array('id'=>$t['cl']->getPrimaryKey(),'format'=>'txt'));
echo    '&nbsp;|&nbsp;';
echo    cgn_applink('Download in XML','nx-changelog','main','download',array('id'=>$t['cl']->getPrimaryKey(),'format'=>'xml'));
echo    '&nbsp;|&nbsp;';
echo    cgn_applink('Download in Wiki','nx-changelog','main','download',array('id'=>$t['cl']->getPrimaryKey(),'format'=>'wiki'));


echo $t['logEntryTable']->toHtml();
?>
