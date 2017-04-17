<?php
include __DIR__.'/lib/init.php';
Session::destroy();
echo 'done';

?>
<html>
<head>
	<script type="text/javascript" src="<?=HTTP_URL?>scripts/lib/jquery.js"></script>
	<script type="text/javascript" src="<?=HTTP_URL?>scripts/lib/jstorage.js"></script>
	<script type="text/javascript">
		$.jStorage.deleteKey('_r2D_userData');
	</script>
</head>
</html>