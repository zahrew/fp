<?php
session_start();

if (isset($_SESSION['username'])) {

	include 'app/db.conn.php';

	include 'app/helpers/user.php';
	include 'app/helpers/conversations.php';
	include 'app/helpers/timeAgo.php';
	include 'app/helpers/last_chat.php';
	include 'app/helpers/update_convs.php';

	$_b;
	$user_id = $_SESSION['user_id'];

	# gereftane data az user
	$user = getUser($_SESSION['username'], $conn);

	# greftane conversation haye user
	$conversations = getConversation($user['user_id'], $conn);

	?>
	<!DOCTYPE html>
	<html lang="en">

	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Chat App - Home</title>
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet"
			integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
		<link rel="stylesheet" href="styles/style.css">
		<link rel="icon" href="img/logo.png">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

	</head>

	<body class="d-flex
									 justify-content-center
									 align-items-center
									 vh-100">
		<div class="p-2 w-400
										rounded shadow">
			<div>
				<div class="d-flex
												mb-3 p-3 bg-light
												justify-content-between
												align-items-center">
					<div class="d-flex
													align-items-center">
						<img src="uploads/<?= $user['p_p'] ?>" class="w-25 rounded-circle">
						<h3 class="fs-xs m-2">
							<?= $user['name'] ?>
						</h3>
					</div>
					<a href="logout.php" class="btn btn-dark">Logout</a>
				</div>

				<div class="input-group mb-3">
					<input type="text" placeholder="Search..." id="searchText" class="form-control">
					<button class="btn btn-primary" id="serachBtn">
						<i class="fa fa-search"></i>
					</button>
				</div>
				<ul id="chatList" class="list-group mvh-50 overflow-auto">
					<?php if (!empty($conversations)) { ?>
						<?php

						foreach ($conversations as $conversation) { ?>
							<li class="list-group-item">
								<a href="chat.php?user=<?= $conversation['username'] ?>" class="d-flex
																										  justify-content-between
																										  align-items-center p-2">
									<div class="d-flex
																												align-items-center">
										<img src="uploads/<?= $conversation['p_p'] ?>" class="w-10 rounded-circle">
										<h3 class="fs-xs m-2">
											<?= $conversation['name'] ?><br>
											<small>
												<?php
												echo lastChat($_SESSION['user_id'], $conversation['user_id'], $conn);
												?>
											</small>
										</h3>
									</div>
									<?php if (last_seen($conversation['last_seen']) == "Active") { ?>
										<div title="online">
											<div class="online"></div>
										</div>
									<?php } ?>
								</a>
							</li>
						<?php } ?>
					<?php } else { ?>
						<div class="alert alert-info 
																				text-center">
							<i class="fa fa-comments d-block fs-big"></i>
							No messages yet, Start the conversation
						</div>
					<?php } ?>
				</ul>
			</div>
		</div>


		<div id="liveToast" class="toast toast-container position-fixed bottom-0 end-0 p-3" role="alert"
			aria-live="assertive" aria-atomic="true">
			<div class="toast-header">
				<img src="img/logo.png" class="rounded me-2 w-10" alt="...">
				<strong class="me-auto">New Message</strong>
				<small>Just Now</small>
				<button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
			</div>
			<div class="toast-body">
				You Have New Message!
			</div>
		</div>



		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

		<script>



			$(document).ready(function () {

				// Searche mohtaviate searchtext
				$("#searchText").on("input", function () {
					var searchText = $(this).val();
					if (searchText == "") return;
					$.post('app/ajax/search.php',
						{
							key: searchText
						},
						function (data, status) {
							$("#chatList").html(data);
						});
				});

				// Search bade feshordane btn search
				$("#serachBtn").on("click", function () {
					var searchText = $("#searchText").val();
					if (searchText == "") return;
					$.post('app/ajax/search.php',
						{
							key: searchText
						},
						function (data, status) {
							$("#chatList").html(data);
						});
				});


				//update kardane khodkare last seen har 10 sec

				let lastSeenUpdate = function () {
					$.get("app/ajax/update_last_seen.php");
				}
				lastSeenUpdate();

				setInterval(lastSeenUpdate, 10000);

				var old_message = 0;
				var message = 0;

				let fetchConvs = function () {
					$.post('app/ajax/update_convs.php',
						function (data, status) {
							message = data;
							console.log(data);

							if (message > old_message) {
								old_message = message;
								$('#liveToast').addClass('show');
								setTimeout(function () {
									$('#liveToast').removeClass('show');
								}, 3000);
								
							}
						});
				}

				setInterval(fetchConvs, 5000);
			});


		</script>
		<script type="module">

			function toast() {
				/* const toastLiveExample = document.getElementById('liveToast')
				const toast = new bootstrap.Toast(toastLiveExample)

				toast.show() */
				alert('new message');
			}
		</script>
	</body>

	</html>
	<?php
} else {
	header("Location: index.php");
	exit;
}
?>