class Voter {
	static vote(postId, vote, event) {
		if (Voter.processingVote) {
			return;
		}

		Voter.processingVote = event.currentTarget;
		Voter.processingVoteValue = vote;

		var voteData = { };

		voteData.vote = vote;
		voteData.postid = postId;

		$.post("vote.php", voteData, Voter.onVoteSuccess);
	}

	static onVoteSuccess(response) {
		var responseData = JSON.parse(response);

		if (responseData === true) {
			Voter.commitVote();
		}
		else {
			alert("Could not save the vote. " + responseData.error);
		}

		Voter.processingVote = null;
	}

	static commitVote() {
		var label = Voter.processingVote.parentNode.querySelector("span");

		var oldValue = parseInt(label.innerText);

		label.innerText = oldValue + (Voter.processingVote.className == "active" ? -Voter.processingVoteValue : Voter.processingVoteValue);

		var otherVoteButton = null;
		var buttons = Voter.processingVote.parentNode.querySelectorAll("img");
		for (var i = 0; i < buttons.length; i++) {
			var thisButton = buttons[i];
			if (thisButton != Voter.processingVote) {
				otherVoteButton = thisButton;
				break;
			}
		}

		if (otherVoteButton.className == "active") {
			otherVoteButton.className = "";
			otherVoteButton.src = otherVoteButton.src.replace("%20(active).png", ".png");

			//Add the same vote again if there was already an opposite vote.
			oldValue = parseInt(label.innerText);
			label.innerText = oldValue + (Voter.processingVote.className == "active" ? -Voter.processingVoteValue : Voter.processingVoteValue);
		}

		if (Voter.processingVote.className == "active") {
			Voter.processingVote.className = "";
			Voter.processingVote.src = Voter.processingVote.src.replace("%20(active).png", ".png");
		}
		else {
			Voter.processingVote.className = "active";
			Voter.processingVote.src = Voter.processingVote.src.replace(".png", "%20(active).png");
		}
	}
}