<!-- 
    Name: HUGH TRUNG-HIEU PHUNG
    StudentNo: s3842508
    Course: ISYS1101 / 1102 DATABASE APPLICATIONS
    Project: ASSIGNMENT 4: WEB DATABASE APPLICATIONS
    Year: SEMESTER 2 2022
    
    Page Summary: Handles insertion of vote preferences and stores it into database.

 -->

 <!DOCTYPE html>
<head>
    <style>
        body {
            margin:0;
        }
        .headerBlock{
            min-height:5vh;
            min-width:100%;
            background-color:#E1E1E1;

        }

        .footerBlock {
            min-height:10vh;
            min-width:100%;
            margin-top:20vh;
            background-color:#E1E1E1;
            text-align:center;
            font-family: roboto,Helvetica,Arial,sans-serif;
            
        }
        .mainForm {
            margin-top: 3%;
            margin-left:40%;
            font-family: roboto,Helvetica,Arial,sans-serif;
            height:60vh;

            width:400px;
        } 
        
        .ballotInput {
            width:50px;
            height:50px;
            font-size:28px;
            text-align:center;
        }
        </style>
   
</head>

<?php
$username = 's3842508';
$password = 'Monkey123';
$servername = 'talsprddb01.int.its.rmit.edu.au';
$servicename = 'CSAMPR1.ITS.RMIT.EDU.AU';
$connection = $servername."/".$servicename;

$conn = oci_connect($username, $password, $connection);

$ballotCreated = False;
$globalElectorateID = null;
$globalVoterId = null;

// Checks the electorate ID that was passed on from the previous form
if (isset($_POST['currentElectorateId'])){
    global $globalElectorateID;
    $globalElectorateID = $_POST['currentElectorateId'];
}

// Gets the voter id from the previous page
if (isset($_POST['currentVoterId'])){
    global $globalVoterId;
    $globalVoterId = $_POST['currentVoterId'];
}

?>
<?php if(!$conn) :
{
    $e = oci_error();
    trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
}
?>
<?php else :
    
    // Finds all candidates in electorate.
    // The reason for this is because the name of the ballot posts in the previous form use the candidate partycodes.

    $query = 'SELECT candidatename, candidate.partycode, partyname, electorateid, electioncode, electevtid
                  FROM candidate JOIN political_party 
                  ON political_party.partycode = candidate.partycode
                  WHERE lower(electorateid) = :electorateid_bv';

    $stid = oci_parse($conn, $query);




    global $globalElectorateID;
    global $globalElectionEvtId;
    global $globalElectionCode;


    // Run-time binding of PHP variables to Oracle bind variables.
    oci_bind_by_name($stid, ":electorateid_bv", $globalElectorateID);

    // Gathers data from Oracle database and assigns them to variables
    oci_define_by_name($stid, 'CANDIDATENAME',$candidateName);
    oci_define_by_name($stid, 'PARTYNAME',$partyName);
    oci_define_by_name($stid, 'ELECTIONCODE',$electionCode);
    oci_define_by_name($stid, 'ELECTEVTID',$electionEvtId);
    oci_define_by_name($stid, 'PARTYCODE',$partyCode);

    oci_execute($stid);

    // Loops through all the candidates to find the ballot votes from the previous page
    while (oci_fetch($stid)) {
        if (isset($_POST[$partyCode])){
            $currentCandidatePreference = $_POST[$partyCode];    
            

            // Checks if ballot has been entered first
            // This is because the ballot serial number is the primary key and needs to exist in the ballot table first.
            if (!$ballotCreated)
            {
                $query = 'INSERT INTO ballot VALUES(:id_bv, :electioncode_bv, :electionevtid_bv, :electorateid_bv)';

                $stid2 = oci_parse($conn, $query);
            
                // Create unique id for ballot serial number
                $id = uniqid();

                // Binds variables for Oracle query
                oci_bind_by_name($stid2, ":id_bv", $id);
                oci_bind_by_name($stid2, ":electioncode_bv", $electionCode);
                oci_bind_by_name($stid2, ":electionevtid_bv", $electionEvtId);
                oci_bind_by_name($stid2, ":electorateid_bv", $globalElectorateID);
            
                oci_execute($stid2);

                $ballotCreated = True;
            }

            // Once the ballot has been entered, the ballot preferences are THEN inserted.
            $query = 'INSERT INTO ballotpreferences VALUES(:id_bv, :candidatename_bv, :candidatePref_bv)';
            $stid3 = oci_parse($conn, $query);

            $candidatePrefInt = (int)$currentCandidatePreference;

            oci_bind_by_name($stid3, ":id_bv", $id);
            oci_bind_by_name($stid3, ":candidatename_bv", $candidateName);
            oci_bind_by_name($stid3, ":candidatePref_bv", $candidatePrefInt);
            oci_execute($stid3);
        }
    }



    // Set the has voted flag once the vote is successful
    global $globalVoterId;

    $query = 'UPDATE hasVoted SET voteflag=1 WHERE voterid LIKE :voterid_bv';

    $stid4 = oci_parse($conn, $query);

    oci_bind_by_name($stid4, ":voterid_bv", $globalVoterId);

    oci_execute($stid4);
        
    ?>


<!-- Page contents -->
    <html>
        <div class="headerBlock"></div>
        <div class="mainForm">
            <h2 style=font-weight:normal>
                Voting Successful.
            </h2>
            <hr>
                <br>
                Thank you for voting.

        </div>
    </html>

    <!-- Footer -->
    <?php endif; ?>
    <html>
        <div class='footerBlock'>
            <br>
            Hugh Phung 3842508 <br>
            Database Applications Assignment 4 2022
        </div>
    </html>


<?php oci_close($conn); ?>