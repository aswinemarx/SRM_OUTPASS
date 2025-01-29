 






<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">
<html lang="en">

    <head>
        <meta charset="utf-8"> 
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title>SRM Student Portal</title>
        <link href="../../resources/css/vendor/mainstyle.css" rel="stylesheet">
        <link href="../../resources/css/vendor/all.css" rel="stylesheet" type="text/css">
        <link href="../../resources/css/vendor/customstyle.css" rel="stylesheet">
        <link href='../../resources/Image/college_icon.jpeg' type='Image/x-icon' rel='icon'></link>
        <script src="../../resources/js/vendor/jquery.min.js"></script>
        <script>
            var appURL = "";
            var imageName;
            var d;
            $(function () {
                (function (global) {

                    if (typeof (global) === "undefined")
                    {
                        throw new Error("window is undefined");
                    }

                    var _hash = "!";
                    var noBackPlease = function () {
                        global.location.href += "#";
                        // making sure we have the fruit available for juice....
                        // 50 milliseconds for just once do not cost much (^__^)
                        global.setTimeout(function () {
                            global.location.href += "!";
                        }, 50);
                    };
                    // Earlier we had setInerval here....
                    global.onhashchange = function () {
                        if (global.location.hash !== _hash) {
                            global.location.hash = _hash;
                        }
                    };
                    global.onload = function () {

                        noBackPlease();
                        // disables backspace on page except on input fields and textarea..
                        document.body.onkeydown = function (e) {
                            var elm = e.target.nodeName.toLowerCase();
                            if (e.which === 8 && (elm !== 'input' && elm !== 'textarea')) {
                                e.preventDefault();
                            }
                            // stopping event bubbling up the DOM tree..
                            e.stopPropagation();
                        };
                    };
                })(window);
                $("#hidchkHostelOpen").val('');
                funShow(1, '../../students/report/studentProfile.jsp');
            });

            function funSetFormId(arg) {
                if (arg > 1001) {
                    $('#logoutModal').modal('show');
                } else {
                    $("#hdnFormId").val(arg);
                    $('#userHomePage').attr("action", "../../students/template/HRDSystem.jsp");
                    $('#userHomePage').submit();
                }
            }

            function funShow(arg, argURL) {
                appParameter = [];
                appParameter.push({name: "iden", value: arg});
                appParameter.push({name: "filter", value: ""});
                appParameter.push({name: "hdnFormDetails", value: $("#hdnFormDetails").val()});
                appParameter.push({name: "csrfPreventionSalt", value: $("#csrfPreventionSalt").val()});
                $(".navmenu").removeClass("active");
                $("#listId" + arg).addClass("active");
                if ($("#listId" + arg).parent().closest('nav').attr('class') == "sidenav-menu-nested nav") {
                    $("#" + $("#listId" + arg).parent().closest('nav').closest('div').attr('id')).addClass('show');
                }
                if (arg < 1001) {
                    $("#divMainDetails").html("<img src='../../resources/Image/wait.gif' alt='Loading... Please Wait!'>");
                    $("#divMainDetails").show();
                    $.post(argURL, appParameter, function (data) {
                        $("#divMainDetails").html(data);
                        $("#divMainDetails").show();
                    }, "html").fail(function (jqXHR, textStatus, errorThrown) {
                        alert(jqXHR.responseText);
                    })
                } else {
                    $('#logoutModal').modal('show');
                }
            }
        </script>
    </head>
    <body class="nav-fixed">
        <form id="userHomePage" name="userHomePage" method="post">
            <nav class="topnav navbar navbar-expand shadow justify-content-between justify-content-sm-start navbar-light bg-white" id="sidenavAccordion">
                <button class="btn btn-icon btn-transparent-dark order-1 order-lg-0 mr-lg-2" id="sidebarToggle"><i class="fa fa-bars"></i></button>
                <a class="navbar-brand" href="../../students/loginManager/UserHomePage.jsp">
                    <img src="../../resources/Image/srmist.png"  alt="SRM Logo"/> 
                    <br><font style="font-size: 9pt;">Student Portal</font>
                </a>
                <span class="text-custom d-none d-sm-block"><b>Faculty of Engineering and Technology, Ramapuram, Chennai</b></span>

                <ul class="navbar-nav align-items-center ml-auto text-center">

                    <a style='cursor:pointer;'>
                        <span class="dropdown-item" data-toggle="modal"  data-target="#logoutModal">
                            <div class="dropdown-item-icon"><i class="fas fa-sign-out-alt"></i></div>
                            Logout
                        </span>
                    </a>
                </ul>
            </nav>

            <div id="layoutSidenav">
                <div id="layoutSidenav_nav">
                    <nav class="sidenav shadow-right sidenav-light">
                        <div class="sidenav-menu">
                            <div class="nav accordion" id="accordionSidenav">

                                <!-- Sidenav Heading -->
                                <!--                                <div class="sidenav-menu-heading">Menus</div>-->

                                
                            <!-- 
                            Individual Menu Begin
                            -->
                            <a id="listId1" class="nav-link navmenu" style='cursor:pointer;' onclick="funSetFormId(1)">
                                <div class="nav-link-icon"><i class="fas fa-fw fa-tachometer-alt"></i></div>
                                    Dashboard 
                            </a>
                            <!-- 
                           Individual Menu End
                            -->
                            
                            <!-- 
                            Individual Menu Begin
                            -->
                            <a id="listId69" class="nav-link navmenu" style='cursor:pointer;' onclick="funSetFormId(69)">
                                <div class="nav-link-icon"><i class="fas fa-fw fa-newspaper"></i></div>
                                    Fee Payment 
                            </a>
                            <!-- 
                           Individual Menu End
                            -->
                            
                            <!-- 
                            Individual Menu Begin
                            -->
                            <a id="listId17" class="nav-link navmenu" style='cursor:pointer;' onclick="funSetFormId(17)">
                                <div class="nav-link-icon"><i class="fas fa-fw fa-user"></i></div>
                                    Personal Details 
                            </a>
                            <!-- 
                           Individual Menu End
                            -->
                            
                            <!-- 
                            Individual Menu Begin
                            -->
                            <a id="listId33" class="nav-link navmenu" style='cursor:pointer;' onclick="funSetFormId(33)">
                                <div class="nav-link-icon"><i class="fas fa-fw fa-newspaper"></i></div>
                                    End Sem Feedback 
                            </a>
                            <!-- 
                           Individual Menu End
                            -->
                            
                            <!-- 
                            Individual Menu Begin
                            -->
                            <a id="listId7" class="nav-link navmenu" style='cursor:pointer;' onclick="funSetFormId(7)">
                                <div class="nav-link-icon"><i class="fas fa-fw fa-book"></i></div>
                                    Course List 
                            </a>
                            <!-- 
                           Individual Menu End
                            -->
                            
                            <!-- 
                            Individual Menu Begin
                            -->
                            <a id="listId8" class="nav-link navmenu" style='cursor:pointer;' onclick="funSetFormId(8)">
                                <div class="nav-link-icon"><i class="fas fa-fw fa-newspaper"></i></div>
                                    Grade / Mark & Credit 
                            </a>
                            <!-- 
                           Individual Menu End
                            -->
                            
                            <!-- 
                            Individual Menu Begin
                            -->
                            <a id="listId24" class="nav-link navmenu" style='cursor:pointer;' onclick="funSetFormId(24)">
                                <div class="nav-link-icon"><i class="fas fa-fw fa-newspaper"></i></div>
                                    Exam Provisional Results 
                            </a>
                            <!-- 
                           Individual Menu End
                            -->
                            
                            <!-- 
                            Individual Menu Begin
                            -->
                            <a id="listId9" class="nav-link navmenu" style='cursor:pointer;' onclick="funSetFormId(9)">
                                <div class="nav-link-icon"><i class="fas fa-fw fa-calculator"></i></div>
                                    Attendance Details 
                            </a>
                            <!-- 
                           Individual Menu End
                            -->
                            
                            <!-- 
                            Individual Menu Begin
                            -->
                            <a id="listId27" class="nav-link navmenu" style='cursor:pointer;' onclick="funSetFormId(27)">
                                <div class="nav-link-icon"><i class="fas fa-fw fa-newspaper"></i></div>
                                    Exam Revaluation Results 
                            </a>
                            <!-- 
                           Individual Menu End
                            -->
                            
                            <!-- 
                            Individual Menu Begin
                            -->
                            <a id="listId10" class="nav-link navmenu" style='cursor:pointer;' onclick="funSetFormId(10)">
                                <div class="nav-link-icon"><i class="fas fa-fw fa-clock"></i></div>
                                    Timetable 
                            </a>
                            <!-- 
                           Individual Menu End
                            -->
                            
                            <!-- 
                            Individual Menu Begin
                            -->
                            <a id="listId13" class="nav-link navmenu" style='cursor:pointer;' onclick="funSetFormId(13)">
                                <div class="nav-link-icon"><i class="fas fa-fw fa-server"></i></div>
                                    Internal Mark Details 
                            </a>
                            <!-- 
                           Individual Menu End
                            -->
                            
                            <!-- 
                            Individual Menu Begin
                            -->
                            <a id="listId3" class="nav-link navmenu" style='cursor:pointer;' onclick="funSetFormId(3)">
                                <div class="nav-link-icon"><i class="fas fa-fw fa-receipt"></i></div>
                                    Finance Details 
                            </a>
                            <!-- 
                           Individual Menu End
                            -->
                            
                            <!-- 
                            Individual Menu Begin
                            -->
                            <a id="listId21" class="nav-link navmenu" style='cursor:pointer;' onclick="funSetFormId(21)">
                                <div class="nav-link-icon"><i class="fas fa-fw fa-book"></i></div>
                                    ABC Entry Request 
                            </a>
                            <!-- 
                           Individual Menu End
                            -->
                            
                            <!-- 
                            Individual Menu Begin
                            -->
                            <a id="listId42" class="nav-link navmenu" style='cursor:pointer;' onclick="funSetFormId(42)">
                                <div class="nav-link-icon"><i class="fas fa-fw fa-newspaper"></i></div>
                                    Exam HallTicket 
                            </a>
                            <!-- 
                           Individual Menu End
                            -->
                            
                            <!-- 
                            Individual Menu Begin
                            -->
                            <a id="listId46" class="nav-link navmenu" style='cursor:pointer;' onclick="funSetFormId(46)">
                                <div class="nav-link-icon"><i class="fas fa-fw fa-newspaper"></i></div>
                                    Photo for Degree certificate 
                            </a>
                            <!-- 
                           Individual Menu End
                            -->
                            
                            <!-- 
                            Individual Menu Begin
                            -->
                            <a id="listId44" class="nav-link navmenu" style='cursor:pointer;' onclick="funSetFormId(44)">
                                <div class="nav-link-icon"><i class="fas fa-fw fa-envelope"></i></div>
                                    Summer Term / Compensatory Registration  
                            </a>
                            <!-- 
                           Individual Menu End
                            -->
                            
                            <!-- 
                            Individual Menu Begin
                            -->
                            <a id="listId49" class="nav-link navmenu" style='cursor:pointer;' onclick="funSetFormId(49)">
                                <div class="nav-link-icon"><i class="fas fa-fw fa-newspaper"></i></div>
                                    Scribe Request 
                            </a>
                            <!-- 
                           Individual Menu End
                            -->
                            
                            <!-- 
                            Individual Menu Begin
                            -->
                            <a id="listId54" class="nav-link navmenu" style='cursor:pointer;' onclick="funSetFormId(54)">
                                <div class="nav-link-icon"><i class="fas fa-fw fa-newspaper"></i></div>
                                    Review/Revaluation/Retotaling Registration 
                            </a>
                            <!-- 
                           Individual Menu End
                            -->
                            
                            <!-- 
                            Individual Menu Begin
                            -->
                            <a id="listId60" class="nav-link navmenu" style='cursor:pointer;' onclick="funSetFormId(60)">
                                <div class="nav-link-icon"><i class="fas fa-fw fa-newspaper"></i></div>
                                    Transcript 
                            </a>
                            <!-- 
                           Individual Menu End
                            -->
                            
                            <!-- 
                            Individual Menu Begin
                            -->
                            <a id="listId57" class="nav-link navmenu" style='cursor:pointer;' onclick="funSetFormId(57)">
                                <div class="nav-link-icon"><i class="fas fa-fw fa-newspaper"></i></div>
                                    Name Change - Gazette 
                            </a>
                            <!-- 
                           Individual Menu End
                            -->
                            
                            <!-- 
                            Individual Menu Begin
                            -->
                            <a id="listId74" class="nav-link navmenu" style='cursor:pointer;' onclick="funSetFormId(74)">
                                <div class="nav-link-icon"><i class="fas fa-fw fa-file"></i></div>
                                    Community Certificate 
                            </a>
                            <!-- 
                           Individual Menu End
                            -->
                            
                            <!-- 
                            Individual Menu Begin
                            -->
                            <a id="listId83" class="nav-link navmenu" style='cursor:pointer;' onclick="funSetFormId(83)">
                                <div class="nav-link-icon"><i class="fas fa-fw fa-newspaper"></i></div>
                                    Placement Insight Dashboard 
                            </a>
                            <!-- 
                           Individual Menu End
                            -->
                            
                            <!-- 
                            Individual Menu Begin
                            -->
                            <a id="listId94" class="nav-link navmenu" style='cursor:pointer;' onclick="funSetFormId(94)">
                                <div class="nav-link-icon"><i class="fas fa-fw fa-user"></i></div>
                                    Student Review Feedback 
                            </a>
                            <!-- 
                           Individual Menu End
                            -->
                            
                            <a class="nav-link" data-toggle="modal" data-target="#logoutModal">
                                <div class="nav-link-icon"><i class="fas fa-sign-out-alt"></i></div>
                                Logout
                            </a>
                        </div>
                </div>
                <!-- Sidenav Footer-->
                <div class="sidenav-footer">
                    <div class="sidenav-footer-content">
                        <div class="sidenav-footer-subtitle">RA2311003020239</div>
                        <div class="sidenav-footer-subtitle">ASWIN ANAND E</div>
                        <div class="sidenav-footer-title">Fri 24-Jan-2025 16:15:48</div>
                    </div>
                    <input type="hidden" style="color:white;" id="hidchkHostelOpen" name="hidchkHostelOpen" size="10" value="''">
                </div>
                </nav>
            </div>
            <div id="layoutSidenav_content">
                <main>
                    <div id="divMainDetails" style="display: none;"></div>
                </main>
                <footer class="footer mt-auto footer-light">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-6 small"></div>
                        </div>
                    </div>
                </footer>
            </div>
        </div>
        <input type="hidden" id="hdnFormStatus" name="hdnFormStatus" value="0">
        <input type="hidden" id="hdnFormId" name="hdnFormId" value="1">
        <input type="hidden" id="hdnFormDetails" name="hdnFormDetails" value="1">
        <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
             aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Logout</h5>
                        <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                            <span class="text-white" aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        Are you sure to logout?
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-dark lift" type="button" data-dismiss="modal">Cancel</button>
                        <a class="btn btn-custom lift" href="../../students/template/Logout.jsp">Logout</a>
                    </div>
                </div>
            </div>
        </div>

        <input type="hidden" id="hdnFilename" name="hdnFilename" value="">
        <input type="hidden" name="csrfPreventionSalt" id="csrfPreventionSalt" value="WCKeAOewnkIiWEwrrvHD"/>
    </form>
    <script src="../../resources/js/vendor/bootstrap.bundle.min.js"></script>
    <script src="../../resources/js/vendor/jquery.easing.min.js"></script>
    <script src="../../resources/js/vendor/main.js"></script>                   
</body>
</html>


 