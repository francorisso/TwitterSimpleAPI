<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="">
	<meta name="author" content="">
	<link rel="shortcut icon" href="ico/favicon.ico">
	<link rel="apple-touch-icon" href="ico/apple-touch-icon.png">

	<title>Tweets</title>

	<!-- Bootstrap core CSS -->
	<link href="css/bootstrap.min.css" rel="stylesheet">

	<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
	<!--[if lt IE 9]>
	  <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
	  <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
	<![endif]-->

	<!-- Custom styles for this template -->
	<link href="css/main.css" rel="stylesheet">
	<!-- Specials Fonts -->
</head>
	
<body>

	<div class="container v-centering clearfix">

		<div class="tweets clearfix">
			<div class="tweets-shadow clearfix">
				<div class="tweets-shadow2 clearfix">
					<header>
						<h2>Tweets</h2>
					</header><!-- /header -->
					
					<section class="tweets-list clearfix">
						<ul></ul>
					</section><!-- /.tweets-list -->

					<footer class="clearfix">
						<button type="button" class="btn btn-default" onclick="javascript:twitterAPI.load();">More</button>
					</footer><!-- /footer -->
				</div><!-- /.tweets-shadow2 --> 
			</div><!-- /.tweets-shadow --> 
		</div><!-- /.tweets --> 

	</div><!-- /.container --> 
	

<!-- Bootstrap core & JavaScript
================================================== -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/docs.min.js"></script>
<script src="js/app.js"></script>
<script src="js/main.js"></script>
</body>
</html>
