<?php
session_start();
include "../../incl/lib/connection.php";
require "../../incl/lib/generatePass.php";
require "../../incl/lib/exploitPatch.php";
require "../../incl/lib/mainLib.php";
$gs = new mainLib();
$ep = new exploitPatch();
require "../incl/dashboardLib.php";
$dl = new dashboardLib();
if(!isset($_SESSION["accountID"]) OR $_SESSION["accountID"] == 0){
	header("Location: ../login/login.php");
	exit();
}
if($gs->checkPermission($_SESSION["accountID"], "dashboardAdminTools")==false){
	exit($dl->printBox("<h1>NO NO NO</h1><p>This account do not have the permissions to access this tool.</p>"));
}
if(!empty($_POST["userName"]) AND !empty($_POST["password"]) AND !empty($_POST["packName"]) AND !empty($_POST["levels"]) AND !empty($_POST["stars"]) AND !empty($_POST["coins"]) AND !empty($_POST["difficulty"]) AND !empty($_POST["color"])){
	$userName = $ep->remove($_POST["userName"]);
	$password = $ep->remove($_POST["password"]);
	$packName = $ep->remove($_POST["packName"]);
	$levels = $ep->remove($_POST["levels"]);
	$stars = $ep->remove($_POST["stars"]);
	$coins = $ep->remove($_POST["coins"]);
	$difficulty = $ep->remove($_POST["difficulty"]);
	$color = $ep->remove($_POST["color"]);
	$generatePass = new generatePass();
	$pass = $generatePass->isValidUsrname($userName, $password);
	if ($pass == 1) {
		$query = $db->prepare("SELECT accountID FROM accounts WHERE userName=:userName");	
		$query->execute([':userName' => $userName]);
		$accountID = $query->fetchColumn();
		if($gs->checkPermission($accountID, "toolPackcreate") == false){
			$dl->printBox("<h1>".$dl->getLocalizedString("packCreate").'</h1>
	                       <p>This account do not have the permissions to access this tool. <a href="admin/packCreate.php">Try again</a></p>');
			//echo "This account doesn't have the permissions to access this tool. <a href='packCreate.php'>Try again</a>";
		}else{
			if(!is_numeric($stars) OR !is_numeric($coins) OR $stars > 10 OR $coins > 2){
			exit($dl->printBox("<h1>".$dl->getLocalizedString("packCreate").'</h1>
	                       <p>Invalid stars/coins value <a href="admin/packCreate.php">Try again</a></p>'));
				//exit("Invalid stars/coins value");
			}
			if(!is_numeric($difficulty) OR $difficulty > 10){
			exit($dl->printBox("<h1>".$dl->getLocalizedString("packCreate").'</h1>
	                       <p>Invalid difficulty value <a href="admin/packCreate.php">Try again</a></p>'));
				//exit("Invalid difficulty value");
			}
			if(strlen($color) != 6){
			exit($dl->printBox("<h1>".$dl->getLocalizedString("packCreate").'</h1>
	                       <p>Invalid color value <a href="admin/packCreate.php">Try again</a></p>'));
				//exit("Unknown color value");
			}
			$rgb = hexdec(substr($color,0,2)).
				",".hexdec(substr($color,2,2)).
				",".hexdec(substr($color,4,2));
			$lvlsarray = explode(",", $levels);
			foreach($lvlsarray AS &$level){
				if(!is_numeric($level)){
			exit($dl->printBox("<h1>".$dl->getLocalizedString("packCreate")."</h1>
	                       <p>$level isn't a number <a href='modtools/packCreate.php'>Try again</a></p>"));
					//exit("$level isn't a number");
				}
				$query = $db->prepare("SELECT levelName FROM levels WHERE levelID=:levelID");	
				$query->execute([':levelID' => $level]);
				if($query->rowCount() == 0){
				exit($dl->printBox("<h1>".$dl->getLocalizedString("packCreate")."</h1>
	                       <p>Level #$level doesn't exist. <a href='modtools/packCreate.php'>Try again</a></p>"));
					//exit("Level #$level doesn't exist.");
				}
			}
			$dl->printBox("<h1>".$dl->getLocalizedString("packCreate")."</h1>
			                                        AccountID: $accountID <br>
				                                    Pack Name: $packName <br>
				                                    Levels: $levels <br>
				                                    Stars: $stars <br>
				                                    Coins: $coins <br>
				                                    Difficulty: $difficulty <br>
				                                    RGB Color: $rgb
													<p><a href='stats/packTable.php'>MAP PACK LIST</a></p>");
			$query = $db->prepare("INSERT INTO mappacks     (name, levels, stars, coins, difficulty, rgbcolors)
													VALUES (:name,:levels,:stars,:coins,:difficulty,:rgbcolors)");
			$query->execute([':name' => $packName, ':levels' => $levels, ':stars' => $stars, ':coins' => $coins, ':difficulty' => $difficulty, ':rgbcolors' => $rgb]);
		}
	}else{
		$dl->printBox("<h1>".$dl->getLocalizedString("packCreate").'</h1>
	                       <p>Invalid password or nonexistant account. <a href="admin/packCreate.php">Try again</a></p>');
		//echo "Invalid password or nonexistant account. <a href='packCreate.php'>Try again</a>";
	}
}else{
		$dl->printBox('<h1>'.$dl->getLocalizedString("packCreate").'</h1>
		        <script src="incl/jscolor/jscolor.js"></script>
				<form action="" method="post">
					<div class="form-group">
								<label for="usernameField">Admin Data</label>
								<input type="text" class="form-control" id="usernameField" name="userName" placeholder="Enter username">
								<input type="password" class="form-control" id="passwordField" name="password" placeholder="Enter password">
								<label for="packnameField">Pack Name</label>
								<input type="text" class="form-control" id="packnameField" name="packName" placeholder="Enter packName">
								<label for="levelsField">Level IDs</label>
								<input type="text" class="form-control" id="levelsField" name="levels" placeholder="Enter Level IDs (separate by commas)">
								<label for="starsField">Stars</label>
								<input type="text" class="form-control" id="starsField" name="stars" placeholder="Enter Stars (max 10 stars)">
								<label for="coinsField">Coins</label>
								<input type="text" class="form-control" id="coinsField" name="coins" placeholder="Enter Coins (max 2 coins)">
								<label for="difficultyField">Difficulty</label>
								<input type="text" class="form-control" id="difficultyField" name="difficulty" placeholder="Enter Difficulty">
								<label for="colorField">Color</label>
								<input type="text" class="jscolor" id="colorField" name="color" placeholder="Color">
							</div>
					<button type="submit" class="btn btn-primary btn-block">'.$dl->getLocalizedString("packCreate").'</button>
				</form>',"mod");
        $dl->printPage2('<p><b1>Dificulty Set
		                           <br>0 = Auto
								   <br>1 = Easy
								   <br>2 = Normal
								   <br>3 = Hard
								   <br>4 = Harder
								   <br>5 = Insane
								   <br>6 = Hard Demon
								   <br>7 = Easy Demon
								   <br>8 = Medium Demon
								   <br>9 = Insane Demon
								   <br>10 = Extreme Demon</p>');
}
?>