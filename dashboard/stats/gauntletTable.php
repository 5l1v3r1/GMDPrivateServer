<?php
//Checking if logged in
session_start();
if(!isset($_SESSION["accountID"]) || !$_SESSION["accountID"]) exit(header("Location: ../login/login.php"));
//Requesting files
include "../../incl/lib/connection.php";
require_once "../incl/dashboardLib.php";
require_once "../../incl/lib/mainLib.php";
$gs = new mainLib();
$dl = new dashboardLib();
//Generating gauntlet table
if(isset($_GET["page"]) && is_numeric($_GET["page"]) && $_GET["page"] > 0){
	$page = ($ep->remove($_GET["page"]) - 1) * 10;
	$actualpage = $ep->remove($_GET["page"]);
}else{
	$page = 0;
	$actualpage = 1;
}
$x = $page + 1;
$gauntlettable = "";
//Getting data
$query = $db->prepare("SELECT * FROM gauntlets ORDER BY ID ASC LIMIT 10 OFFSET $page");
$query->execute();
$gauntlets = $query->fetchAll();
foreach($gauntlets as &$gauntlet){
	//Getting levels
	$lvltable;
	$lvlarray = array();
	for ($y = 1; $y < 6; $y++) $lvlarray[] = $gauntlet["level".$y];
	foreach($lvlarray as &$lvl){
		$query = $db->prepare("SELECT levelID, levelName, starStars, userID, coins FROM levels WHERE levelID = :levelID");
		$query->execute([':levelID' => $lvl]);
		$level = $query->fetch();
		$lvltable .= "<tr>
						<td>".$level["levelID"]."</td>
						<td>".$level["levelName"]."</td>
						<td>".$gs->getUserName($level["userID"])."</td>
						<td>".$level["starStars"]."</td>
						<td>".$level["coins"]."</td>
					</tr>";
	}
	$gauntlettable .= "<tr>
					<th scope='row'>$x</th>
					<td>".$gs->getGauntletName($gauntlet["ID"]).'</td>
					<td><a class="dropdown-toggle" href="#" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							Show
						</a>
						<div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownMenuLink"  style="padding:17px;">
							<table class="table">
								<thead>
									<tr>
										<th>'.$dl->getLocalizedString("ID").'</th>
										<th>'.$dl->getLocalizedString("name").'</th>
										<th>'.$dl->getLocalizedString("author").'</th>
										<th>'.$dl->getLocalizedString("stars").'</th>
										<th>'.$dl->getLocalizedString("userCoins").'</th>
									</tr>
								</thead>
								<tbody>
									'.$lvltable.'
								</tbody>
							</table>
						</div>
					</td>
					</tr>';
	$x++;
	echo "</td></tr>";
}
//Getting count
$query = $db->prepare("SELECT count(*) FROM gauntlets");
$query->execute();
$gauntletCount = $query->fetchColumn();
$pageCount = ceil($gauntletCount / 10);
//Bottom row
$bottomRow = $dl->generateBottomRow($pageCount, $actualpage);
//Printing page
$dl->printPage('<table class="table table-inverse">
  <thead>
    <tr>
      <th>#</th>
      <th>'.$dl->getLocalizedString("name").'</th>
      <th>'.$dl->getLocalizedString("levels").'</th>
    </tr>
  </thead>
  <tbody>
    '.$gauntlettable.'
  </tbody>
</table>'
.$bottomRow, true, "browse");
?>