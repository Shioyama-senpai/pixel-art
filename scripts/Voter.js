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
	}

	static commitVote() {
		var label = Voter.processingVote.parentNode.querySelector("span");

		var oldValue = parseInt(label.innerText);

		label.innerText = oldValue + Voter.processingVoteValue;

		Voter.processingVote = null;
	}
}