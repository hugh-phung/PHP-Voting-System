<!-- 
    Name: HUGH TRUNG-HIEU PHUNG
    StudentNo: s3842508
    Course: ISYS1101 / 1102 DATABASE APPLICATIONS
    Project: ASSIGNMENT 4: WEB DATABASE APPLICATIONS
    Year: SEMESTER 2 2022
    
    Page Summary: Handles page rendering of ballot that candidates in an electorate.

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
            height:'100vh';

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

// Checks if the hasVoted checkbox was checked
if (isset($_POST["hasVoted"])){
    $str1 = $_POST["hasVoted"];
}
else {
    $str1 = "off";
}
$str2 = "on";
$currentState = $_POST["state"];

?>

<?php
$username = 's3842508';
$password = 'Monkey123';
$servername = 'talsprddb01.int.its.rmit.edu.au';
$servicename = 'CSAMPR1.ITS.RMIT.EDU.AU';
$connection = $servername."/".$servicename;

$currentVoterId = null;
$currentElectorateID = null;
$currentElectorateName= null;



// Trims the edge of whitespace
function trimEdgeSpace(string $input) {
    $input = rtrim($input, ' ');
    $input = ltrim($input, ' ');

    return $input;
    
}

// Takes all the inputs from the form and builds one line of a string to match the database
function buildAddressString(string $residentialaddress){
    // Builds address string for query
    $address_1 = $_POST["address_1"];
    $suburb = $_POST['suburb'];
    $state = $_POST['state'];
    $postCode = $_POST['postcode'];

    // Cuts unnecessary white space from left and right side of the input
    $address_1 = trimEdgeSpace($address_1);
    $suburb = trimEdgeSpace($suburb);
    $postCode = trimEdgeSpace($postCode);

    // If the second address line is filled, include it in the string
    if (isset($_POST["address_2"])){
        $address_2 = $_POST["address_2"];
        $address_2 = trimEdgeSpace($address_2);

        // Checks if the address_2 field is empty, if not include it in the full address string
        if (strcmp($address_2, '') != 0)
        {
            $residentialaddress = $address_1 . ', ' . $address_2 . ', ' . strtoupper($suburb) . ', ' . $state . ', ' . $postCode;

        }
        else{
            $residentialaddress = $address_1 . ', ' . strtoupper($suburb) . ', ' . $state . ', ' . $postCode;

        }

    }
    else{
        $residentialaddress = $address_1 . ', ' . strtoupper($suburb) . ', ' . $state . ', ' . $postCode;

    }

    return $residentialaddress;
    
}

// Checks whether the user has voted according to the vote flag in the database
function checkHasVoted(string $firstName, string $lastName) {
    $hasVotedReturn = False;
    global $username, $password, $connection;

    $conn = oci_connect($username, $password, $connection);
    if(!$conn) 
    {
        $e = oci_error();
        trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
    }
    else{
        $query = 'SELECT COUNT(*)
                  FROM hasVoted JOIN voter_registry ON voter_registry.voterid = hasvoted.voterid
                  WHERE hasvoted.voteflag=1 AND lower(voter_registry.firstname) LIKE :firstName_bv 
                                            AND lower(voter_registry.lastname) LIKE :lastName_bv';

        $stid = oci_parse($conn, $query);

        // Convert form inputs to lower-case
        $firstName = strtolower($firstName);
        $lastName = strtolower($lastName);

        // Run-time binding of PHP variables to Oracle bind variables.
        oci_bind_by_name($stid, ":firstName_bv", $firstName);
        oci_bind_by_name($stid, ":lastName_bv", $lastName);

        oci_execute($stid);

        $count = 0;
        while ($row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS)) {
            $count = $row['COUNT(*)'];
            if ($count == 1) {
                $hasVotedReturn = True;
            }

        }
        return $hasVotedReturn;
    }
}

$nameAndAddressExists = False;

// Establishing connection to database
$conn = oci_connect($username, $password, $connection);
if(!$conn) 
{
    $e = oci_error();
    trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
}
else{
    // Cuts off any additional white space in front or at the end of the input
    $original_str = $_POST["full_name"];
    $original_str = trimEdgeSpace($original_str);

    // Extracts contents of the full name into individual first, middle and last names for query.
    $fullNameArray = explode(' ', $original_str);
    $firstName = '';
    $middleName = '';
    $lastName = '';
    $residentialaddress = '';

    // Checks if a middle name is present
    if (count($fullNameArray) === 3 AND isset($_POST["address_1"])) 
    {
        $firstName = $fullNameArray[0];
        $middleName = $fullNameArray[1];
        $lastName = $fullNameArray[2];

        $residentialaddress = buildAddressString($residentialaddress);
        
        // echo $firstName . $middleName . $lastName . ' ' . $residentialaddress;
        // IMPORANT *****************************************************************************
        // Checks if the database returns a result for the given name and address.
        // Checks both at the same time since it's more convenient than having two separate queries.
        $query = 'SELECT voterid, electorateid, electorate.electoratename, COUNT(*)
                  FROM voter_registry JOIN electorate ON voter_registry.electorate=electorate.electoratename 
                  WHERE LOWER(firstName) LIKE :firstName_bv 
                  AND LOWER(middleName) LIKE :middleName_bv
                  AND LOWER(lastName) LIKE :lastName_bv
                  AND LOWER(residentialaddress) LIKE :residentialaddress_bv
                  GROUP BY voterid, electorateid, electoratename';

        $stid = oci_parse($conn, $query);

        // Convert form inputs to lower-case
        $firstName = strtolower($firstName);
        $middleName = strtolower($middleName);
        $lastName = strtolower($lastName);
        $residentialaddress = strtolower($residentialaddress);

        // Run-time binding of PHP variables to Oracle bind variables.
        oci_bind_by_name($stid, ":firstName_bv", $firstName);
        oci_bind_by_name($stid, ":middleName_bv", $middleName);
        oci_bind_by_name($stid, ":lastName_bv", $lastName);
        oci_bind_by_name($stid, ":residentialaddress_bv", $residentialaddress);
        oci_define_by_name($stid, 'VOTERID',$voterId);
        oci_define_by_name($stid, 'ELECTORATEID',$electorateId);
        oci_define_by_name($stid, 'ELECTORATENAME',$electorateName);

        oci_execute($stid);

        // Checks if the database returns any rows, any results
        $count = 0;
        while ($row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS)) {
            $count = $row['COUNT(*)'];

            if ($count == 1) {
                $nameAndAddressExists = True;
            }
            $currentVoterId = $voterId;
            $currentElectorateID = $electorateId;
            $currentElectorateName = $electorateName;
        }

        
    }
    else if (count($fullNameArray) === 2 AND isset($_POST["address_1"]) )
    {
        $firstName = $fullNameArray[0];
        $lastName = $fullNameArray[1];
        $middleName = null; 
        $residentialaddress = buildAddressString($residentialaddress);

        // IMPORANT *****************************************************************************
        // Checks if the database returns a result for the given name and address.
        // Checks both at the same time since it's more convenient than having two separate queries.
        $query = 'SELECT voterid, electorateid, electorate.electoratename, COUNT(*)
                  FROM voter_registry JOIN electorate ON voter_registry.electorate=electorate.electoratename 
                  WHERE LOWER(firstName) LIKE :firstName_bv 
                  AND LOWER(lastName) LIKE :lastName_bv
                  AND LOWER(residentialaddress) LIKE :residentialaddress_bv
                  GROUP BY voterid, electorateid, electoratename';

        $stid = oci_parse($conn, $query);

        // Convert form inputs to lower-case
        $firstName = strtolower($firstName);
        $lastName = strtolower($lastName);
        $residentialaddress = strtolower($residentialaddress);

        // Run-time binding of PHP variables to Oracle bind variables.
        oci_bind_by_name($stid, ":firstName_bv", $firstName);
        oci_bind_by_name($stid, ":lastName_bv", $lastName);
        oci_bind_by_name($stid, ":residentialaddress_bv", $residentialaddress);
        oci_define_by_name($stid, 'VOTERID',$voterId);
        oci_define_by_name($stid, 'ELECTORATEID',$electorateId);
        oci_define_by_name($stid, 'ELECTORATENAME',$electorateName);

        oci_execute($stid);

        $count = 0;

        // Checks if the database returns any rows, any results
        while ($row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS)) {
            $count = $row['COUNT(*)'];
            if ($count == 1) {
                $nameAndAddressExists = True;
            }
            $currentVoterId = $voterId;
            $currentElectorateID = $electorateId;
            $currentElectorateName = $electorateName;

        }


    }
}
?>



<!-- Header and Title -->
<html>
<body >
    <div class="headerBlock"></div>
    <div class="mainForm">
        <h2 style=font-weight:normal>
            <?php
            if ($currentElectorateName){
                echo $currentState . '<BR>';
                echo 'Electoral Division of ' . $currentElectorateName;
            }
            else {
                echo 'Australian Electroral Commission<BR>';
            }

            ?>
        </h2>
        <hr>
    </div>
    </html>
<!-- First checks the existence of a name and address match in the database.
     String compare returns 0 if both values are the same 
     If the user has already voted it will prompt and voter fraud message.
     If not, then continue with the ballot.
-->
<?php if ($nameAndAddressExists) :?>
    <?php if (strcmp($str1, $str2) == 0 || checkHasVoted($firstName, $lastName)) :?>
        <html>
            <div class='mainForm'>
                <h2 style=font-weight:normal>Thank you for voting</h2> 
                You have already voted. <br>
                Do not submit any additional votes. <br> <br>
                Multiple voting is considered electoral fraud under section 339 of the Commonwealth Electoral Act 1918. <br>
                
                <button onClick="window.location='https://titan.csit.rmit.edu.au/~s3842508/dba/asg4/index.php'" 
                style="background-color:#EC8B78; margin-top:10%; width: 100px; height: 30px; font-size:14px; color:white; float:right;"> 
                  Return 
                </button>

            </div>
        </html>

        <!-- Conduct ballot voting -->
        <!-- The query gathers all the candidates in that particular elective. -->
    <?php else : 

        $query = 'SELECT candidatename, candidate.partycode, partyname, partylogo, electorateid, electioncode, electevtid
                  FROM candidate JOIN political_party 
                  ON political_party.partycode = candidate.partycode
                  WHERE lower(electorateid) = :electorateid_bv';

        $stid = oci_parse($conn, $query);

        // Convert form inputs to lower-case
        $currentElectorateID = strtolower($currentElectorateID);

        // Run-time binding of PHP variables to Oracle bind variables.
        oci_bind_by_name($stid, ":electorateid_bv", $currentElectorateID);
        oci_define_by_name($stid, 'CANDIDATENAME',$candidateName);
        oci_define_by_name($stid, 'PARTYNAME',$partyName);
        oci_define_by_name($stid, 'PARTYCODE',$partyCode);
        oci_define_by_name($stid, 'PARTYLOGO',$partyLogo);




        oci_execute($stid);


        ?> 
            <!-- The following HTML renders out the candidates in that elective and inputs for voting. -->
            <!-- Additionally, this sends the electorateid and voter id on the next page for respective queries. -->
            <html>
                <div class='mainForm' style="margin-top:3vh;">
                <form action='voteComplete.php' method='post'>
                <?php echo "<input value=$currentElectorateID id='currentElectorateId' name='currentElectorateId' type='hidden'></input>" ?>
                <?php echo "<input value=$currentVoterId id='currentVoterId' name='currentVoterId' type='hidden'></input>" ?>


                <!-- Populate the table with data fetched from the Oracle table -->
                <!-- Loops through all the ballot entries and posts them to be submitted onto the next form -->
                <?php while (oci_fetch($stid)) : 
                global $currentElectorateID;
                [$candidateFirstName, $candidateLastName] = explode(' ', $candidateName);
                ?>
                    <div style="display:flex; max-height:50px; margin-top:7%;">
                    <span style="">
                        <?php echo "<img style='max-width:60px; max-height:90%; margin-right:10px; position:relative; top:20%;'src=$partyLogo>" ?>
                    </span>
                        <?php echo "<input id=$partyCode name=$partyCode type='text' maxlength='1' class='ballotInput'></input>" ?>
                        <div style="margin-left:15px;">
                            <?php echo "<div style='font-size:20px;'>" . strtoupper($candidateLastName) . ", " . $candidateFirstName . "</div>"; ?>
                            <?php echo "<div style='padding-top:8px;'>" . strtoupper($partyName) . "</div>"; ?>
                        </div>
                    </div>

        <?php endwhile; ?>
  
                    <input value="Submit" type="submit" style="background-color:#EC8B78; margin-top:15%; width: 100px; height: 30px; font-size:14px; color:white; float:right;">

                </form>
            </div>
        </html>




    <?php endif; ?>

<!-- If the Oracle query returns no results, then the name and address input does not match the system -->
<?php else : ?>
    <html>
        <div class='mainForm'>
            Name or address does not exist in our system. <br> <br>
            Please enrol by clicking the button below. <br>
            <button onClick="window.location='https://www.aec.gov.au/enrol/'" 
            style="background-color:#EC8B78; margin-top:10%; width: 100px; height: 30px; font-size:14px; color:white; float:right;"> 
            Enrol 
        </button>
        </div>

    </html>
<?php endif; ?>

<!-- Footer -->
<html>
    <div class='footerBlock'>
        <br>
        Hugh Phung 3842508 <br>
        Database Applications Assignment 4 2022
    </div>
</html>


<?php oci_close($conn); ?>
