<?php
/************************************
 * Buzzz Job Management System
 *
 * Copyright, Howard Miller 2008
 *
 * Library functions
 ************************************/

class lib {

    // the functions here should be static
    // class is realy just a namespace

    /**
     * display header
     */
     function header( $trail ) {
         require( 'header.php' );
     }

    /**
     * display footer
     */
    function footer() {
        require( 'footer.php' );
    }

    /** 
     * display notice with optional continue link
     */
    function notice( $message, $url=false ) {
        echo "<div class=\"notice\">\n";
        echo "$message\n";
        if (!empty($url)) {
            echo "<form action=\"$url\">\n";
            echo "<input type=\"submit\" value=\"Click to continue...\" />\n";
            echo "</form>";
        }
        echo "</div>\n";
    }

    /**
     * redirect to url
     */
    function redirect( $url ) {
        header( 'HTTP/1.1 303 See Other' );
        header( "Location:$url" );
    }

    /**
     * random string
     */
    function random_string ($length=15) {
        $pool  = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $pool .= 'abcdefghijklmnopqrstuvwxyz';
        $pool .= '0123456789';
        $poollen = strlen($pool);
        mt_srand ((double) microtime() * 1000000);
        $string = '';
        for ($i = 0; $i < $length; $i++) {
            $string .= substr($pool, (mt_rand()%($poollen)), 1);
        }
        return $string;
    }

    /**
     * generate or return sesskey
     */
    function sesskey() {
        if (empty($_SESSION['sesskey'])) {
            $_SESSION['sesskey'] = lib::random_string(10);
        }
        return $_SESSION['sesskey'];
    }

    /**
     * debug a variable
     */
    function dumpvar( $mixed, $die=false ) {
        echo "<pre>"; print_r( $mixed ); echo "</pre>";
        if ($die) {
            die;
        }
    }

    /**
     * because I always get it wrong
     */
    function vardump( $mixed, $die=false ) {
        lib::dumpvar( $mixed, $die );
    }

    /** 
     * clean parameter
     * (not much to it at the moment)
     */
    function clean( $param ) {
        $param = strip_tags( $param );
        return $param;
    }


    /**
     * get an optional parameter
     */
    function optional_param( $name, $default ) {
        if (isset($_POST[$name])) {
            $value = $_POST[$name];
        }
        else if (isset($_GET[$name])) {
            $value = $_GET[$name];
        }
        else {
            return $default;
        }

        // a bit of a clean up
        return lib::clean( $value );
    }

    /**
     * get a required parameter
     */
    function required_param( $name ) {
        if (isset($_POST[$name])) {
            $value = $_POST[$name];
        }
        else if (isset($_GET[$name])) {
            $value = $_GET[$name];
        }
        else {
            throw new Exception( "Missing parameter - $name" );
        }

        // a bit of a clean up
        return lib::clean( $value );
    }

    /** 
     * display breadcrumb from array of links
     */
    function breadcrumb( $links ) {
        echo "<div id=\"breadcrumb\">\n";
        $first = true;
        foreach ($links as $link) {
            if (!$first) {
                echo "&nbsp;|&nbsp;";
            }
            $first = false;
            echo "$link\n";
        }
        echo "</div>\n";
    }

    /**
     * display an action button
     */
    function button( $text, $url, $target='' ) {
        if (!empty($target)) {
            $target=" target=\"$target\" ";
        }        
        echo "<div class=\"actionbutton\">\n";
        echo "<form action=\"$url\" method=\"post\" $target>\n";
        echo "<input type=\"submit\" value=\"$text\" />";
        echo "</form>";
        echo "</div>";
    }

    /*
     * confirm action
     */
    function confirm( $text, $yeslink, $nolink, $yestext='Yes', $notext='No' ) {
        echo "<div class=\"confirm\">\n";
        echo "$text<br />";
        echo "<form action=\"$yeslink\" method=\"post\">";
        echo "<input type=\"submit\" value=\"$yestext\" /></form>\n";
        echo "<form action=\"$nolink\" method=\"post\">";
        echo "<input type=\"submit\" value=\"$notext\" /></form>\n";
        echo "</div>";
    }

    /*
     * get config settings
     */
    function getconfig() {
        global $db;
        global $config;

        $sql = "select * from config";
        $settings = $db->query( $sql,false,true,'name' );
        foreach ($settings as $setting) {
            $name = $setting->name;
            $config->$name = $setting->value;
        }
    }

    /*
     * set config
     */
    function setconfig( $name, $value ) {
        global $db;
        global $config; 
   
        $value=addslashes($value);

        // is it already there?
        if (isset($global->$name)) {
            $sql = "update config set value='$value' where name='$name'";
        }
        else {
            $sql = "insert into config set value='$value', name='$name'";
        } 

        $db->update($sql);
    }

    /*
     * convert mysql date to d/m/y
     */
    function decodemysqldate( $mysqldate ) {
        $da = explode( '-',$mysqldate );
        list( $year,$month,$day ) = $da;
        return "$day/$month/$year";
    }

    /*
     * convert d/m/y to mysql
     * TODO: check format is valid
     */
    function encodemysqldate( $ukdate ) {
        $da = explode( '/',$ukdate );
        list( $day,$month,$year ) = $da;
        if ($year<50) {
            $year += 2000;
        }
        return "$year-$month-$day";
    }

    /*
     * get today's date in uk format
     */
    function ukdate() {
        return date( 'd/m/Y' ); 
    }

    /*
     * Text to HTML
     */
    function txt2html( $text ) {
        $html = str_replace( "\n", "<br />", $text );
        return $html;
    }

    /*
     * Linnear menu
     * array( 'label'=>'url' )
     */
    function linmenu( $items ) {
        echo "<div class=\"linmenu\"\n";
        echo "<ul>\n";
            foreach( $items as $label => $url ) {
                echo "<li><a href=\"$url\">$label</a></li>\n";
            }
        echo "</ul>";
        echo "</div>";
    }

    /*
     * options from records
     * make db records into options array
     */
    function records2options( $records,$keyfield,$valuefield,$default='' ) {
        if (empty($default)) {
            $options = array();
        }
        else {
            $options = array( 0 => $default );
        }
        foreach ($records as $record) {
            $options[$record->$keyfield] = $record->$valuefield;
        }
        return $options;
    }

    /*
     * number format to handle currency
     */
    function money( $amount, $decimals=2, $currency='British Pound',$rate=1 ) {
        global $currcodes; 
        global $symbols;

        // convert amount for currency
        $amount = $rate * $amount;

        // get currency symbol
        if ($code = $currcodes[ $currency ]) {
            $symbol = $symbols[$code];
        }
        else {
            $symbol = '';
        }

        $formatted = number_format( $amount, $decimals );
        return $symbol . $formatted; 
    }

}

//=========================
// EXCEPTION HANDLER
//=========================

function exception_handler($exception) {
    echo "<div class=\"exception\">\n";
    echo "<h3>".$exception->getMessage()."</h3>\n";
    echo "<ul>\n";
    $lines = $exception->getTrace();
    foreach ($lines as $line) {
        echo "<li>{$line['file']}:{$line['line']}</li>\n";
    }
    echo "</ul>\n";
    echo "</div>\n";
}
?>
