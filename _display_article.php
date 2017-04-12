<div class="panel panel-default">
	<div class="panel-heading" role="tab" id="h-<?= $a['id']; ?>">
		<!-- Actions -->
		<div class="btn-group pull-right">
			<button type="button" class="btn btn-default dropdown-toggle btn-sm" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="glyphicon glyphicon-option-vertical"></i></span>
			</button>
			<ul class="dropdown-menu">
				<li><a href="<?= $a['link']; ?>" target="_blank"><i class="glyphicon glyphicon-new-window"></i> <?= __('Link'); ?></a></li>
				<li><a href="javascript:void(0);" class="article-read" data-id="<?= $a['id']; ?>"><i class="glyphicon glyphicon-unchecked"></i> <?= __('Mark as read'); ?></a></li>
				<li class="hidden"><a href="#"><i class="glyphicon glyphicon-unchecked"></i> <?= __('Mark as unread'); ?></a></li>
				<?php /* ADD options: share by example
				<li role="separator" class="divider"></li>
				<li><a href="#">Do something</a></li> */ ?>
			</ul>
		</div>

		<!-- Open content -->
		<a class="collapsed" href="#a-<?= $a['id']; ?>" data-parent="feed-<?= empty($_REQUEST['fid'])?'':$_REQUEST['fid']; ?>" role="button" data-toggle="collapse" aria-expanded="true" aria-controls="a-<?= $a['id']; ?>"><?= $a['title']; ?></a>
	</div>
	<div class="panel-collapse collapse" aria-labelledby="h-<?= $a['id']; ?>" role="tabpanel" id="a-<?= $a['id']; ?>">
		<div class="panel-body"><?= $a['content']; ?></div>
	</div>
</div>