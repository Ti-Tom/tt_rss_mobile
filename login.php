<header style="margin-top:10px;">
	<img src="images/logo_wide.png" class="img-responsive" />
</header>
<hr class="clearfix" />
<form class="form-horizontal" action="" method="POST" role="form">
	<input type="hidden" name="return" id="return" value="backend.php?op=mobile" />
	<input type="hidden" name="method" id="method" value="login" />
	<input type="hidden" name="remember_me" id="remember_me" value="1" />
	<div class="form-group">
		<label for="login" class="col-sm-2 control-label"><?= __('Login:'); ?></label>
		<div class="col-sm-10">
			<input type="text" class="form-control" id="login" name="login" placeholder="<?= __('Login'); ?>" required="1" />
		</div>
	</div>
	<div class="form-group">
		<label for="password" class="col-sm-2 control-label"><?= __('Password:'); ?></label>
		<div class="col-sm-10">
			<input type="password" class="form-control" id="password" name="password" placeholder="<?= __('Password'); ?>" required="1" />
		</div>
	</div>
	<div class="form-group">
		<div class="col-sm-12 text-right">
			<button type="submit" class="btn btn-default"><?= __('Log in'); ?></button>
		</div>
	</div>
</form>
<hr class="clearfix" />
<footer class="text-center">
	<a href="http://tt-rss.org/">Tiny Tiny RSS</a>
	&copy; 2005&ndash;<?php echo date('Y') ?> <a href="http://fakecake.org/">Andrew Dolgov</a>
</footer>
