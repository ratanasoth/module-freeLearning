<?php
/*
Gibbon, Flexible & Open School System
Copyright (C) 2010, Ross Parker

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

include '../../functions.php';
include '../../config.php';

include './moduleFunctions.php';

//New PDO DB connection
try {
    $connection2 = new PDO("mysql:host=$databaseServer;dbname=$databaseName", $databaseUsername, $databasePassword);
    $connection2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $connection2->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getMessage();
}

@session_start();

//Set timezone from session variable
date_default_timezone_set($_SESSION[$guid]['timezone']);

$freeLearningBadgeID = $_GET['freeLearningBadgeID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/badges_manage_edit.php&freeLearningBadgeID=$freeLearningBadgeID&search=".$_GET['search'];

if (isActionAccessible($guid, $connection2, '/modules/Free Learning/badges_manage_edit.php') == false) {
    //Fail 0
    $URL = $URL.'&return=error0';
    header("Location: {$URL}");
} else {
    if (isModuleAccessible($guid, $connection2, '/modules/Badges/badges_manage.php') == false) {
        //Fail 0
        $URL = $URL.'&return=error0';
        header("Location: {$URL}");
    } else {
        //Proceed!
        //Check if school year specified
        if ($freeLearningBadgeID == '') {
            //Fail1
            $URL = $URL.'&return=error1';
            header("Location: {$URL}");
        } else {
            try {
                $data = array('freeLearningBadgeID' => $freeLearningBadgeID);
                $sql = 'SELECT freeLearningBadge.*, name, category, logo, description
                    FROM freeLearningBadge
                        JOIN badgesBadge ON (freeLearningBadge.badgesBadgeID=badgesBadge.badgesBadgeID)
                    WHERE freeLearningBadgeID=:freeLearningBadgeID';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                //Fail2
                $URL = $URL.'&deleteReturn=error2';
                header("Location: {$URL}");
                exit();
            }

            if ($result->rowCount() != 1) {
                //Fail 2
                $URL = $URL.'&return=error2';
                header("Location: {$URL}");
            } else {
                $row = $result->fetch();

                //Proceed!
                $badgesBadgeID = $_POST['badgesBadgeID'];
                $active = $_POST['active'];
                $unitsCompleteTotal = null;
                if ($_POST['unitsCompleteTotal'] != '') {
                    $unitsCompleteTotal = $_POST['unitsCompleteTotal'];
                }
                $unitsCompleteThisYear = null;
                if ($_POST['unitsCompleteThisYear'] != '') {
                    $unitsCompleteThisYear = $_POST['unitsCompleteThisYear'];
                }
                $unitsCompleteDepartmentCount = null;
                if ($_POST['unitsCompleteDepartmentCount'] != '') {
                    $unitsCompleteDepartmentCount = $_POST['unitsCompleteDepartmentCount'];
                }
                $unitsCompleteIndividual = null;
                if ($_POST['unitsCompleteIndividual'] != '') {
                    $unitsCompleteIndividual = $_POST['unitsCompleteIndividual'];
                }
                $unitsCompleteGroup = null;
                if ($_POST['unitsCompleteGroup'] != '') {
                    $unitsCompleteGroup = $_POST['unitsCompleteGroup'];
                }
                $difficultyLevelMaxAchieved = null;
                if ($_POST['difficultyLevelMaxAchieved'] != '') {
                    $difficultyLevelMaxAchieved = $_POST['difficultyLevelMaxAchieved'];
                }
                $specificUnitsCompleteList = null;
                if (isset($_POST['specificUnitsComplete'])) {
                    $specificUnitsComplete = $_POST['specificUnitsComplete'];
                    foreach ($specificUnitsComplete as $specificUnitComplete) {
                        $specificUnitsCompleteList .= $specificUnitComplete.',';
                    }
                    $specificUnitsCompleteList = substr($specificUnitsCompleteList, 0, -1);
                }

                if ($badgesBadgeID == '' or $active == ''or ($unitsCompleteTotal == '' and $unitsCompleteThisYear == '' and $unitsCompleteDepartmentCount == '' and $unitsCompleteIndividual == '' and $unitsCompleteGroup == '' and $difficultyLevelMaxAchieved == '' and $specificUnitsCompleteList == '')) {
                    //Fail 3
                    $URL = $URL.'&return=error3';
                    header("Location: {$URL}");
                } else {
                    //Check if badge exists and is active
                    try {
                        $data = array('badgesBadgeID' => $badgesBadgeID);
                        $sql = 'SELECT badgesBadgeID FROM badgesBadge WHERE active=\'Y\' AND badgesBadgeID=:badgesBadgeID';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        //Fail 2
                        $URL = $URL.'&return=error2';
                        header("Location: {$URL}");
                        exit();
                    }
                    if ($result->rowCount()!=1) {
                        //Fail 2
                        $URL = $URL.'&return=error2';
                        header("Location: {$URL}");
                    }
                    else {
                        //Write to database
                        try {
                            $data = array('badgesBadgeID' => $badgesBadgeID, 'active' => $active, 'unitsCompleteTotal' => $unitsCompleteTotal, 'unitsCompleteThisYear' => $unitsCompleteThisYear, 'unitsCompleteDepartmentCount' => $unitsCompleteDepartmentCount, 'unitsCompleteIndividual' => $unitsCompleteIndividual, 'unitsCompleteGroup' => $unitsCompleteGroup, 'difficultyLevelMaxAchieved' => $difficultyLevelMaxAchieved, 'specificUnitsComplete' => $specificUnitsCompleteList, 'freeLearningBadgeID' => $freeLearningBadgeID);
                            $sql = 'UPDATE freeLearningBadge SET badgesBadgeID=:badgesBadgeID, active=:active, unitsCompleteTotal=:unitsCompleteTotal, unitsCompleteThisYear=:unitsCompleteThisYear, unitsCompleteDepartmentCount=:unitsCompleteDepartmentCount, unitsCompleteIndividual=:unitsCompleteIndividual, unitsCompleteGroup=:unitsCompleteGroup, difficultyLevelMaxAchieved=:difficultyLevelMaxAchieved, specificUnitsComplete=:specificUnitsComplete WHERE freeLearningBadgeID=:freeLearningBadgeID';
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                            //Fail 2
                            $URL = $URL.'&return=error2';
                            header("Location: {$URL}");
                            exit();
                        }

                        //Success 0
                        $URL = $URL.'&return=success0';
                        header("Location: {$URL}");
                    }
                }
            }
        }
    }
}
