<?php require_once "blocks/header.php"; ?>

<h1>Latest artworks</h1>

<?php
	$displayMode = isset($_GET["displaymode"]) ? $_GET["displaymode"] : (isset($_COOKIE["displaymode"]) ? $_COOKIE["displaymode"] : "thumbnail");
	if (!in_array($displayMode, array("thumbnail", "list"))) {
		$displayMode = "thumbnail";
	}
	echo "<script>Utilities.setCookie(\"displaymode\", \"" . $displayMode . "\", 300000000);</script>";

	$sorting = isset($_GET["sorting"]) ? $_GET["sorting"] : (isset($_COOKIE["sorting"]) ? $_COOKIE["sorting"] : "most-popular");
	if (!in_array($sorting, array("most-popular", "most-recent"))) {
		$sorting = "most-popular";
	}
	echo "<script>Utilities.setCookie(\"sorting\", \"" . $sorting . "\", 300000000);</script>";
?>

<form class="display-form">
	<table>
		<tr>
			<td>
				Sorting
			</td>
			<td>
				<select name="sorting">
					<option value="most-popular" <?php echo $sorting == "most-popular" ? "selected" : ""; ?>>Most popular</option>
					<option value="most-recent" <?php echo $sorting == "most-recent" ? "selected" : ""; ?>>Most recent</option>
				</select>
			</td>
			<td></td>
		</tr>
		<tr>
			<td>
				Display
			</td>
			<td>
				<select name="displaymode">
					<option value="thumbnail" <?php echo $displayMode == "thumbnail" ? "selected" : ""; ?>>Thumbnail</option>
					<option value="list" <?php echo $displayMode == "list" ? "selected" : ""; ?>>List</option>
				</select>
			</td>
			<td>
				<input type="submit" name="submitsorting" value="Load">
			</td>
		</tr>
	</table>
</form>

<?php require "blocks/posts-list.php"; ?>

<?php require_once "blocks/footer.php"; ?>