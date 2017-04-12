<?php
$h1 = $ico = $lk = "";
$nbElem = 0;
?>
<div class="row <?= empty($_REQUEST['fid'])?'':'hidden'; ?>" id="list-feeds">
	<div class="list-group">
		<div class="list-group-item <?= empty($_REQUEST['fid'])?'active':'' ; ?>">
			
			<img class="pull-left img-responsive" alt="Tiny Tiny RSS" style="width:16px; margin-right: 10px;margin-top: 2px;" src="images/favicon.png">
			<?php /*
			Too long
			<a href="backend.php?op=mobile&method=refresh" class="btn btn-default btn-sm"><i class="glyphicon glyphicon-refresh"></i></a>
			*/ ?>
			<strong>Tiny Tiny RSS</strong>
			<a href="backend.php?op=mobile&method=logout" class="btn btn-danger pull-right btn-sm"><i class="glyphicon glyphicon-off"></i></a>
		</div>
		<?php foreach($feeds as $f): ?>
		<?php
		$url = "backend.php?op=mobile&fid=".$f['id'];
		if(!empty($_REQUEST['fid']) && $_REQUEST['fid'] == $f['id']){
			$h1 = $f['title'];
			$ico = $f['has_icon']?(ICONS_DIR . "/".$f['id'].".ico"):'images/feed.png';
			$nbElem = $f['unread'];
			$lk = $f['feed_url'];
			$url = "javascript:void(0);\" onclick=\"javascript:$('#list-articles').removeClass('hidden');$('#list-feeds').addClass('hidden');\"";
		} ?>
		<a href="<?= $url; ?>" class="list-group-item <?= !empty($_REQUEST['fid']) && $_REQUEST['fid'] == $f['id']?'active':'' ; ?>">
			<img class="pull-left img-responsive" alt="<?= addslashes($f['title']); ?>" style="width:16px; margin-right: 10px;margin-top: 2px;" src="<?= $f['has_icon']?(ICONS_DIR . "/".$f['id'].".ico"):'images/feed.png'; ?>">
			<span><?= $f['title']; ?></span>
			<span class="badge"><?= $f['unread']; ?></span>
		</a>
		<?php endforeach; ?>
	</div>
</div>
<div class="row <?= empty($_REQUEST['fid'])?'hidden':''; ?>" id="list-articles">
	<div style="background-color: #FFFFFF;width: 100%;" data-spy="affix" data-offset-top="10">
		<h4>
			<!-- Open menu bar -->
			<button id="open-menu" class="btn btn-default pull-left btn-sm" style="margin-right: 10px;"><i class="glyphicon glyphicon-menu-hamburger"></i></button>
			<!-- Feed icon -->
			<img class="pull-left img-responsive" style="height: 24px; margin-right: 10px;" alt="<?= addslashes($f['title']); ?>" src="<?= $ico; ?>">
			<!-- Actions -->
			<div class="btn-group pull-right" style="margin-right:16px;">
				<button type="button" class="btn btn-default dropdown-toggle btn-sm" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="glyphicon glyphicon-option-vertical"></i></span>
				</button>
				<ul class="dropdown-menu">
					<li><a href="<?= $lk; ?>" target="_blank"><i class="glyphicon glyphicon-new-window"></i> <?= __('Link'); ?></a></li>
					<?php if(empty($articles)): ?>
						<li class="disabled"><a href="javascript:voi(0);"><i class="glyphicon glyphicon-unchecked"></i> <?= __('mark feed as read'); ?></a></li>
					<?php else: ?>
						<li><a href="backend.php?op=mobile&method=markfeedread&fid=<?= $_REQUEST['fid']; ?>"><i class="glyphicon glyphicon-unchecked"></i> <?= __('mark feed as read'); ?></a></li>
					<?php endif; ?>
				</ul>
			</div>

			<!-- Title -->
			<span><?= $h1; ?></span>
		</h4>
	</div>
	<?php if(empty($articles)): ?>
		<div class="alert alert-info"><?= __('No unread articles found to display.'); ?></div>
	<?php else: ?>
		<div class="panel-group" role="tablist" id="feed-<?= empty($_REQUEST['fid'])?'':$_REQUEST['fid']; ?>" aria-multiselectable="false">
			<?php foreach($articles as $a): ?>
				<?php include dirname(__FILE__)."/_display_article.php"; ?>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>
<script type="text/javascript">
var nbElem = parseInt('<?= $nbElem; ?>'),
	p = 0,
	running = false;
$(document).ready(function(){
	$('button#open-menu').click(function(){
		$('#list-articles').addClass('hidden');
		$('#list-feeds').removeClass('hidden');
	});
	if(nbElem > $('#feed-<?= empty($_REQUEST['fid'])?'':$_REQUEST['fid']; ?> .panel-default').length){
		$(window).scroll(function(){
			if(nbElem > $('#feed-<?= empty($_REQUEST['fid'])?'':$_REQUEST['fid']; ?> .panel-default').length && $(window).scrollTop() + $(window).height() > $(document).height() - 50 && !running) {
				running = true;
				// TODO: add load icon
				$.ajax({
					method: "GET",
					url: "backend.php?op=mobile&method=load&fid=<?= empty($_REQUEST['fid'])?'':$_REQUEST['fid']; ?>&p="+(p+1),
					async: false,
					success: function(d){
						p++;
						$('#feed-<?= empty($_REQUEST['fid'])?'':$_REQUEST['fid']; ?>').append(d);
						$('#feed-<?= empty($_REQUEST['fid'])?'':$_REQUEST['fid']; ?> .panel-body a[target!="_blank"]').attr('target','_blank');
						running = false;
					}
				});
			}
		});
	}
	$('#list-articles .panel-body a').attr('target','_blank');
	$('#list-articles div[data-spy="affix"]:first').css('width',$('.container').innerWidth());
	window.addEventListener("resize", function() {
		$('#list-articles div[data-spy="affix"]:first').css('width',$('.container').innerWidth());
	});
	<?php if(!empty($articles)): ?>
		$('#list-articles .panel-heading li[class!="disabled"]').delegate('.article-read','click',function(){
			var lk = $(this);
			$.ajax({
				method: "GET",
				url: "backend.php?op=mobile&method=markarticleread&fid=<?= empty($_REQUEST['fid'])?'':$_REQUEST['fid']; ?>&aid="+$(this).attr('data-id'),
				success: function(d){
					$(lk).parent().addClass('disabled');
					$(lk).removeClass('article-read');
					$('#list-feeds .list-group-item.active .badge').text(parseInt($('#list-feeds .list-group-item.active .badge').text())-1);
				}
			});
		});
	<?php endif; ?>
});
</script>
<style type="text/css" media="all">
	.affix{
		z-index: 1001;
	}
</style>