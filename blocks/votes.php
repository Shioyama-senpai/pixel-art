<div class="votes-overlay">
	<img src="images/Upvote<?php if ($post["user_vote"] > 0) { echo " (active)"; } ?>.png" class="<?php if ($post["user_vote"] > 0) { echo "active"; } ?>" onclick="Voter.vote(<?php echo $post["postid"]; ?>, 1, event);">
	<span><?php echo $post["total_votes"]; ?></span>
	<img src="images/Downvote<?php if ($post["user_vote"] < 0) { echo " (active)"; } ?>.png" class="<?php if ($post["user_vote"] < 0) { echo "active"; } ?>" onclick="Voter.vote(<?php echo $post["postid"]; ?>, -1, event);">
</div>