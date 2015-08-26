<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>
<meta content="text/html; charset=utf-8" http-equiv="Content-Type">
<title>系统发生错误</title>
</head>
<style>
.div {font-size:14px; clear:both; font-family:Verdana, Arial, Helvetica, sans-serif;}
.div1 {padding-left: 5px;height: 20px;background: #000000; border: 1px #000000 solid;color: white;}
.div2 {padding-left: 5px;height: 20px;background: #FF0000; border: 1px #FF0000 solid;color: white;font-weight: bold;}
</style>
<body>
<fieldset>
	<legend>SK RUNNING ERROR MESSAGE：</legend><br>
		<div class='div'>
			<div class='div1'>错误原因：</div>
			<h1><?php echo strip_tags($e['message']);?></h1><br>
			<?php if(!empty($e['file'])) {?>
			<b>FILE：</b> <?php echo $e['file'] ;?> &#12288;LINE: <?php echo $e['line'];?><br><br>
			<?php }?>
			<?php if(isset($e['trace'])) {?>
			<div class='div2'>TRACE：</div>
			<div style="margin: 15px 0;"><?php echo nl2br($e['trace']);?></div><br>
			<?php }?>
			<div class="copyright">
			<p><a title="官方网站" target="_blank" href="http://www.sk-school.com">SKPHP</a><sup>1.0.0</sup> { Share knowledge change you and me } -- [ Made In China  ]</p>
			</div>
		</div>
	</fieldset>
</body>
</html>