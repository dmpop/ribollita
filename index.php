<?php
error_reporting(E_ERROR);
include('config.php');
?>
<html lang="en" data-theme="<?php echo $theme ?>">
<!-- Author: Dmitri Popov, dmpop@linux.com
         License: GPLv3 https://www.gnu.org/licenses/gpl-3.0.txt -->

<head>
	<meta charset="utf-8">
	<title>Ribollita</title>
	<link rel="shortcut icon" href="favicon.png" />
	<link rel="stylesheet" href="css/classless.css">
	<link rel="stylesheet" href="css/themes.css">
	<link href="css/featherlight.min.css" type="text/css" rel="stylesheet" />
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<style>
		/* Grid: https://wiki.selfhtml.org/wiki/CSS/Tutorials/Grid/responsive_Raster_ohne_Media_Queries */

		*,
		::before,
		::after {
			box-sizing: border-box;
		}

		.square-container {
			display: grid;
			grid-template-columns: repeat(auto-fill, minmax(15rem, 1fr));
			grid-auto-rows: 1fr;
			grid-auto-flow: dense;
			margin-bottom: .5rem;
		}

		.square-container::before {
			content: '';
			height: 0;
			padding-bottom: 100%;
			grid-row: 1 / 1;
			grid-column: 1 / 1;
		}

		.square-container>*:first-child {
			grid-row: 1 / 1;
			grid-column: 1 / 1;
		}

		.square-container>* {
			background: rgba(0, 0, 0, 0.1);
			border: .3rem solid transparent;
			position: relative;
		}

		.square-container>*:focus,
		.square-container>*:hover {
			opacity: .55;
		}

		.square-container img {
			position: absolute;
			top: 0;
			left: 0;
			object-fit: cover;
			width: 100%;
			height: 100%;
		}
	</style>
</head>

<body>
	<script src="js/jquery.min.js"></script>
	<script src="js/featherlight.min.js" type="text/javascript" charset="utf-8"></script>
	<div class="card">
	<div style="text-align: center; margin-bottom: 2em; margin-top: 1em;">
	<img style="display: inline; height: 3em; vertical-align: middle;" src="favicon.png" alt="logo" />
		<h1 class="text-center" style="display: inline; margin-left: 0.19em; vertical-align: middle; letter-spacing: 3px; margin-top: 0em;">Ribollita</h1>
	</div>
		<form class="text-center" style="margin-top: 1em;" method='POST' action=''>
			<button type="submit" name="refresh">Refresh</button>
		</form>
		<div class="text-center">
			<?php

			// FUNCTIONS ---
			function extract_preview_jpeg($raw_dir, $jpg_dir)
			{
				shell_exec('exiftool -b -PreviewImage -w ' . $jpg_dir . '%f.JPG -r ' . $raw_dir);
			}
			function is_dir_empty($dir)
			{
				if (!is_readable($dir)) return NULL;
				return (count(scandir($dir)) == 2);
			}
			function auto_level($jpg_dir)
			{
				$files = glob($jpg_dir . "*.JPG");
				foreach ($files as $file) {
					shell_exec('mogrify -auto-level ' . $jpg_dir . basename($file));
				}
			}
			// --- FUNCTIONS

			if (!file_exists($jpg_dir)) {
				shell_exec('mkdir -p ' . $jpg_dir);
			}
			if (!file_exists($raw_dir)) {
				shell_exec('mkdir -p ' . $raw_dir);
			}
			if (!file_exists($lut_dir)) {
				shell_exec('mkdir -p ' . $lut_dir);
			}

			if (!is_dir_empty($jpg_dir)) {
				echo "<form style='margin-top: .5em;' action='process.php' method='POST'>";
				echo "<select name='img'>";
				$files = glob("$jpg_dir/*");
				foreach ($files as $file) {
					$img = basename($file);
					echo "<option value='$img'>$img</option>";
				}
				echo "</select>";
				echo "<select style='margin-left:0.5em;' name='lut'>";
				$files = glob($lut_dir . "*");
				foreach ($files as $file) {
					$lut_name = basename($file);
					$lut = basename($file, ".png");
					echo "<option value='$lut_name'>$lut</option>";
				}
				echo "</select>";
				echo '<button type="submit" name="process">Process</button>';
				echo "</form>";
				echo '</div>';
				echo '<hr>';
			}

			if (is_dir_empty($raw_dir)) {
				echo '<div class="text-center">
			<img src="wtf-cow.jpg" alt="WTF Cow" width="600">
			<div style="margin-top: 1em;">No RAW files. WTF?</div>
			</div>';
			exit();
			}

			define('IMAGEPATH', $jpg_dir);
			echo '<div class="square-container">';
			foreach (glob(IMAGEPATH . "*.JPG") as $filename) {
				echo '<a target="_blank" href="' . $filename . '" data-featherlight="image">';
				echo '<img src="' . $filename . '" alt="' . $filename . '" title= "' . basename($filename) . '">';
				echo '</a>';
			}
			echo '</div>';

			if (isset($_POST["refresh"]) || is_dir_empty($jpg_dir)) {
				shell_exec('rm -rf ' . $jpg_dir);
				shell_exec('mkdir -p ' . $jpg_dir);
				if ($darktable) {
					shell_exec('for file in ' . $raw_dir . '*.*; do darktable-cli "$file" "${file%.*}.jpg"; done');
					shell_exec('cd ' . $raw_dir . ' && for file in *.jpg; do mv "${file}" ../' . $jpg_dir . '"${file%.*}.JPG"; done');
				} else {
					extract_preview_jpeg($raw_dir, $jpg_dir);
				}
				if ($enable_auto_level) {
					auto_level($jpg_dir);
				}
				echo '<meta http-equiv="refresh" content="0">';
			}
			?>
			<hr>
			<div class="text-center"><?php echo $footer; ?></div>
		</div>
</body>

</html>