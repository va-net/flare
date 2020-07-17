<!DOCTYPE html>
<html>
<head>
    <?php include './templates/header.php'; ?>
</head>
<body>
    <style>
        #loader {
        position: absolute;
        left: 50%;
        top: 50%;
        z-index: 1;
        width: 150px;
        height: 150px;
        margin: -75px 0 0 -75px;
        width: 120px;
        height: 120px;
        }
    </style>

    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #E4181E;">
        <?php include './templates/navbar.php'; ?>
    </nav>

    <div class="container-fluid">
        <div class="container-fluid mt-4 text-center" style="overflow: auto;">
            <div class="main-content">
                <div id="loader" class="spinner-border spinner-border-sm text-danger"></div>
                    <div class="tab-content" id="tc">
                        <div class="tab-pane container active" id="home" style="display: none;">
                            <h3>Success!</h3>
                            <p>Yay! Flare has been setup correctly. You may now <a href="../index.php">login</a> with the credentials you entered earlier, to setup your staff members, permissions, and other handy features that Flare has to offer. Good luck!</p>
                            <br>
                            <p>If you need any help with anything, be sure to visit the docs.</p>
                        </div>
                    </div>
                </div>
            </div>
            <footer class="container-fluid text-center">
                <?php include './templates/footer.php'; ?>
            </footer>
        </div>
    </div>
</body>
</html>