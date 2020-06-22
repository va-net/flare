<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

ini_set("session.gc_maxlifetime", 31622400);
ini_set("date.timezone", 'America/Costa_Rica');

session_start();
    // IMPORTANT: PUT YOUR CREDS HERE IN BETWEEN THE QUOTES ON EACH ITEM
    $server = 'localhost';
    $username = 'root';
    $password = 'Rebal155';
    $dbname = 'flare';
    // Don't change anything after here unless you know what you're doing AND have a backup of this file!!!

    $conn = new mysqli($server, $username, $password, $dbname, 3308);

    function sendEmail($recipient, $subject, $message) {
        $mail = new PHPMailer();
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.sendgrid.net';
            $mail->SMTPAuth = true;
            $mail->Username = 'apikey';
            $mail->Password = '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';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->setFrom('noreply@ifvirginvirtual.vip', 'Virgin Virtual Group Infinite Flight');
            if (is_array($recipient)) {
                foreach ($recipient as $email) {
                    $mail->addAddress($email);
                }
            } else {
                $mail->addAddress($recipient);
            }
            $mail->isHTML(true);
            $mail->Body = $message.'<br /><br /><small>This email is sent from an unmonitored inbox. Please do not reply.</small>';
            $mail->Subject = $subject;
            $mail->send();
            return true;
        } catch (Exception $e) {
            return $mail->ErrorInfo;
        }
    }

    function getHours($userid) {
        global $conn;
        $filed = $conn->query("SELECT SUM(flighttime) AS filedTime FROM pireps WHERE pilotid=$userid AND status=1")->fetch_assoc()["filedTime"];
        $trans = getUserInfo($userid)["transhours"];
        return secsToHrMin($filed + $trans);
    }

    function getFlights($userid) {
        global $conn;
        $filed = $result = $conn->query("SELECT COUNT(*) AS filedFlights FROM pireps WHERE pilotid=$userid AND status=1")->fetch_assoc()["filedFlights"];
        $trans = getUserInfo($userid)["transflights"];
        return $filed + $trans;
    }

    function getRank($userid) {
        global $conn;
        $filed = $conn->query("SELECT SUM(flighttime) AS filedTime FROM pireps WHERE pilotid=$userid AND status=1")->fetch_assoc()["filedTime"];
        $trans = getUserInfo($userid)["transhours"];
        $total = $filed + $trans;
        $ranks = selectMultiple("SELECT * FROM ranks WHERE hoursreq <= {$total} ORDER BY hoursreq ASC;");

        while ($z = $ranks->fetch_assoc()) {
            $return = $z["name"];
        }

        return $return;
    }

    function getFullRank($userid) {
        global $conn;
        $filed = $conn->query("SELECT SUM(flighttime) AS filedTime FROM pireps WHERE pilotid=$userid AND status=1")->fetch_assoc()["filedTime"];
        $trans = getUserInfo($userid)["transhours"];
        $total = $filed + $trans;
        $ranks = selectMultiple("SELECT * FROM ranks WHERE hoursreq <= {$total} ORDER BY hoursreq ASC;");

        while ($z = $ranks->fetch_assoc()) {
            $return = $z;
        }

        return $return;
    }

    function getACByLiv($livId) {
        global $conn;
        return select("SELECT * FROM aircraft WHERE ifliveryid=\"".mysqli_real_escape_string($conn, $livId)."\";");
    }

    function getAC($id) {
        global $conn;
        return select("SELECT * FROM aircraft WHERE id=\"".mysqli_real_escape_string($conn, $id)."\";");
    }

    function getTier($userid) {
        $pts = select("SELECT SUM(elevate_points) AS points FROM pireps WHERE pilotid={$userid} AND status=1")["points"];
        if ($pts == null) {
            $return = select("SELECT * FROM tiers WHERE pointsreq=0 LIMIT 1;")["name"];
        } else {
            $ranks = selectMultiple("SELECT * FROM tiers WHERE pointsreq <= {$pts} ORDER BY pointsreq ASC;");
            while ($z = $ranks->fetch_assoc()) {
                $return = $z["name"];
            }
        }
        return $return;
    }

    function getFullTier($userid) {
        $pts = select("SELECT SUM(elevate_points) AS points FROM pireps WHERE pilotid={$userid} AND status=1")["points"];
        if ($pts == null) {
            $return = select("SELECT * FROM tiers WHERE pointsreq=0 LIMIT 1;")["name"];
        } else {
            $ranks = selectMultiple("SELECT * FROM tiers WHERE pointsreq <= {$pts} ORDER BY pointsreq ASC;");
            while ($z = $ranks->fetch_assoc()) {
                $return = $z;
            }
        }
        return $return;
    }
    
    function getPoints($userid) {
        global $conn;
        $return = select("SELECT SUM(elevate_points) AS pts FROM pireps WHERE pilotid=$userid AND status=1")["pts"];
        if ($return == null) {
            $return = 0;
        }
        return $return;
    }

    function getRankInfo($rankid) {
        return select("SELECT * FROM ranks WHERE id=$rankid");
    }

    function generateMultiCode() {
        $y = selectMultiple("SELECT code FROM multipliers;");
        $codes = array();
        if ($y === FALSE) {
            return mt_rand(111111, 999999);
        } else {
            while ($x = $y->fetch_assoc()) {
                array_push($codes, $x["code"]);
            }
            $retcode = '';
            while (1==1) {
                $retcode = mt_rand(111111, 999999);
                if (!in_array($retcode, $codes)){return $retcode;}
            }
        }
    }

    function secsToHrMin($seconds) {
        $H = floor($seconds / 3600);
        $i = ($seconds / 60) % 60;
        return sprintf("%02d:%02d", $H, $i);
    }

    function getUserInfo($id) {
        $sql = 'SELECT * FROM pilots WHERE id='.$id.';';
        $ret = select($sql);
        return $ret;
    }

    function runQ($sql) {
        // Create connection
        global $conn;
        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        $ret = $conn->query($sql);
        if ($ret === TRUE) {
            return TRUE;
        } else {
            return mysqli_error($conn);
        }

        $conn->close();
    }

    function select($selSql) {
        global $conn;
        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        // Create connection
        

        $result = $conn->query($selSql);
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        } else {
            return FALSE;
        }
    }

    function reloadPilotInfo() {
        $temp = $_SESSION["pilotinfo"]["id"];
        session_unset();
        $pilot = select("SELECT * FROM pilots WHERE id={$temp};");
        $_SESSION["authed"] = true;
        $_SESSION["pilotinfo"] = $pilot;
    }

    function selectMultiple($selSql) {
        global $conn;
        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        // Create connection
        

        $result = $conn->query($selSql);
        if ($result != false && $result->num_rows > 0) {
            return $result;
        } else {
            return FALSE;
        }
    }

    function getUrl() {
        $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
        return $actual_link;
    }

    function getIP() {
        $ipaddress = '';
        if (getenv('HTTP_CLIENT_IP'))
            $ipaddress = getenv('HTTP_CLIENT_IP');
        else if(getenv('HTTP_X_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        else if(getenv('HTTP_X_FORWARDED'))
            $ipaddress = getenv('HTTP_X_FORWARDED');
        else if(getenv('HTTP_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        else if(getenv('HTTP_FORWARDED'))
        $ipaddress = getenv('HTTP_FORWARDED');
        else if(getenv('REMOTE_ADDR'))
            $ipaddress = getenv('REMOTE_ADDR');
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }