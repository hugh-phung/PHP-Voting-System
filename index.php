<!-- 
    Name: HUGH TRUNG-HIEU PHUNG
    StudentNo: s3842508
    Course: ISYS1101 / 1102 DATABASE APPLICATIONS
    Project: ASSIGNMENT 4: WEB DATABASE APPLICATIONS
    Year: SEMESTER 2 2022
    
    Page Summary: Provides a form for electoral role lookup.

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

        </style>
   
</head>

<html> 
    <!-- Header block acts as a bar along the top of the page -->
    <body >
    <div class="headerBlock">
    
    <!-- Main contents of the form -->
    </div>
        <div class="mainForm">
            <h2 style=font-weight:normal>
                Electoral Role Search
            </h2>

            <hr>
            <br>

            <form action="voteForm.php" method="post">
            <div class = 'formContents'>
                <div>
                    Full Name
                    <br>
                    <input required id='full_name' type="text" placeholder='Enter full name here...' name="full_name" style="margin-top:5px; margin-top:5px; width:400px; height:25px">
                </div>

                <div style=padding-top:20px;>
                    Address line 1
                    <br>
                    <input required class="address-field af-hidden-autofill-icon" autocomplete="off" name='address_1' id="address_1" placeholder="Enter address here" type="text" style="margin-top:5px; width:400px; height:25px"> 
                </div>

                <div style=padding-top:20px;>
                    Address line 2 (optional)
                    <br>
                    <input type="text" id="address_2" name="address_2" style="margin-top:5px; width:400px; height:25px">
                </div>

                <div style=padding-top:20px;>
                    Suburb
                    <br>
                    <input required type="text" id="suburb" name="suburb" style="margin-top:5px; width:400px; height:25px">
                </div>

                <!-- Use combo box for state to avoid unnecessary bad input -->
                <div style=padding-top:20px;>
                    State
                    <br>
                    <select name='state' id='state' style="margin-top:5px; width:408px;  height:30px">
                        <option value='NSW'>New South Wales</option>
                        <option value='NT'>Northern Territory</option>
                        <option value='QLD'>Queensland</option>
                        <option value='SA'>South Australia</option>
                        <option value='TA'>Tasmania</option>
                        <option value='VIC'>Victoria</option>
                        <option value='WA'>Western Australia</option>

                    </select>
                </div>

                <div style=padding-top:20px;>
                    Postcode
                    <br>
                    <input required type="text" id="postcode" name="postcode" style="margin-top:5px; width:400px;  height:25px">
                </div>
                <br>
                <p> Have you voted before in THIS election? <br>(Tick if already voted)</p>
                <input type="checkbox" id="hasVoted" name="hasVoted">

                <br>
                <input value="NEXT" type="submit" style="background-color:#EC8B78; margin-top:10%; width: 100px; height: 30px; font-size:14px; color:white; float:right;">

                </div> 

                <!-- FOLLOWING CODE IS REFERENCED FROM https://addressfinder.com.au/docs/guide-custom-website-integration/
                     Uses Address Finder API to complete the autocomplete for the address search input box -->
                <script>
            (function () {
                var widget, initAF = function () {
                    widget = new AddressFinder.Widget(
                    document.getElementById('address_1'),
                    'RGKVCPYW6U9AQFT4L3DH',
                    'AU',
                    {
                        "address_params": {
                        "gnaf": "1"
                        }
                    }
                    );
                    widget.on('result:select', function (fullAddress, metaData) {
                    document.getElementById('address_1').value = metaData.address_line_1;
                    document.getElementById('address_2').value = metaData.address_line_2;
                    document.getElementById('suburb').value = metaData.locality_name;
                    document.getElementById('state').value = metaData.state_territory;
                    document.getElementById('postcode').value = metaData.postcode;
                    document.getElementById('gps-metadata').value = metaData.longitude + ', ' + metaData.latitude;
                    document.getElementById('meshblock-metadata').value = metaData.meshblock;
                    document.getElementById('SA1').value = metaData.sa1_id;
                    });
                };

            function downloadAF() {
                var script = document.createElement('script');
                script.src = 'https://api.addressfinder.io/assets/v3/widget.js';
                script.async = true;
                script.onload = initAF;
                document.body.appendChild(script);
            };

            document.addEventListener('DOMContentLoaded', downloadAF);
            })();
        </script>
            </form>

        </div>
    </body>


    <!-- Footer -->
    <div class='footerBlock'>
        <br>
        Hugh Phung 3842508 <br>
        Database Applications Assignment 4 2022
    </div>

</html> 