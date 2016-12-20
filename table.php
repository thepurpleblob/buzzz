<?php
/************************************
 * Buzzz Job Management System
 *
 * Copyright, Howard Miller 2008
 *
 * Table class
 ************************************/

define('NAVLIMIT',6);

require_once( 'config.php' );

class paginated_table {

    var $name = '';
    var $linesPerPage = 20;
    var $pagenum = 1;
    var $headings = array();
    var $fields = array();
    var $sortfields = array();
    var $records = array();
    var $sortby = '';
    var $sortalias = '';
    var $sortdir = 'asc';
    var $dataCallback = '';
    var $baseurl = '';
    var $decimals = 2;
    var $printable = false;

    function setLinesPerPage( $linesPerPage ) {
        $this->linesPerPage = $linesPerPage;
    }

    function setPagenum( $pagenum ) {
        $this->pagenum = $pagenum;
    } 

    function setHeadings( $headings ) {
        $this->headings = $headings;
    }

    function setFields( $fields ) {
        $this->fields = $fields;
    }

    function setSortfields( $sortfields ) {
        $this->sortfields = $sortfields;
    }

    function setRecords( $records ) {
        $this->records = $records;
    }

    function setRow( $record ) {
        $this->records[] = $record;
    }

    function setDataCallback( $dataCallback ) {
        $this->dataCallback = $dataCallback;
    }

    function setName( $name ) {
        $this->name = $name;
    }

    function setBaseUrl( $baseurl ) {
        $this->baseurl = $baseurl;
    }

    function setSort( $sortby, $sortdir='asc' ) {
        $this->sortby  = $sortby;
        $this->sortdir = $sortdir;
    }

    function setDecimals( $decimals ) {
        $this->decimals = $decimals;
    }

    function setPrintable( $printable ) {
        $this->printable = $printable;
    }

    function printNavigation() {
        // if linesperpage=0 then no nav
        if ($this->linesPerPage==0) {
            return;
        }

        // work out basic url
        if (empty($this->baseurl)) {
            $url = "?sort={$this->sortby}&amp;sortdir={$this->sortdir}&amp;page=";
        }
        else {
            $url = "{$this->baseurl}&amp;sort={$this->sortby}&amp;sortdir={$this->sortdir}&amp;page=";
        }

        $pages = ceil(count( $this->records ) / $this->linesPerPage);
        if ($pages<2) {
            return;
        }
        $start = $this->pagenum - NAVLIMIT;
        if ($start<1) {
            $start = 1;
        }
        $end = $this->pagenum + NAVLIMIT;
        if ($end > $pages) {
            $end=$pages;
        }

        echo "<div class=\"pt_navigation\" >\n";
        // previous
        if ($this->pagenum>1) {
            $prev = $this->pagenum-1;
            echo "<span class=\"pt_prevnext\">";
            echo "<a href=\"$url$prev\">prev</a></span>\n";
        } 
        // pre-elipses
        if ($start>2) {
            $prev = $start-1;
            echo "<span class=\"pt_elipses\">";
            echo "<a href=\"$url$prev\">...</a></span>\n";
        }
        // page numbers
        for ($i=$start;$i<=$end;$i++) {
            if ($i==$this->pagenum) {
                echo "<span class=\"pt_thispage\">";
                echo "$i</span>";
            }
            else {
                echo "<span class=\"pt_page\">";
                echo "<a href=\"$url$i\">$i</a></span>";
            }
        }
        // post-elipses
        if (($pages-$end)>1) {
            $next = $end+1;
            echo "<span class=\"pt_elipses\">";
            echo "<a href=\"$url$next\">...</a></span>\n";
        }
        // next 
        if ($pages>$this->pagenum){
            $next = $this->pagenum+1;
            echo "<span class=\"pt_prevnext\">";
            echo "<a href=\"$url$next\">next</a></span>\n";
        } 
    }

    function getURL() {
        if (empty($baseurl)) {
            $url = '?';
        }
        else {
            $url = "$baseurl&amp;";
        }
        $url .= "page={$this->pagenum}&amp;sort={$this->sortby}";
        return $url; 
    }

    function display() {
        // get params
        $this->pagenum = lib::optional_param('page',1);
        $this->sortby = lib::optional_param('sort',$this->sortby);
        $this->sortdir = lib::optional_param('sortdir',$this->sortdir);

        // check sortfields (the database fields we will sort on if not the same)
        if (empty($this->sortfields)) {
            $this->sortfields = $this->fields;
        }

        // if callback, ask for the data
        $callback = $this->dataCallback;
        if (function_exists( $callback )) {
            $extrasql = '';
            if (!empty($this->sortby)) {
                $extrasql = " order by {$this->sortby} {$this->sortdir}";
            }
            $this->records = $callback( $this, $extrasql );
        }

        // check if there is actually any data to display
        if (empty($this->records)) {
            echo "<div class=\"pt_nodata\">No records found</div>\n";
            return;
        }

        // get basic url
        if (empty($this->baseurl)) {
            $url = "?page={$this->pagenum}&amp;sort=";
        }
        else {
            $url = "{$this->baseurl}&amp;page={$this->pagenum}&amp;sort=";
        }

        // calculate start end end items
        $start = ($this->pagenum-1) * $this->linesPerPage + 1;
        $end = $start + $this->linesPerPage;
        $rcount = count( $this->records );

        // if linesPerPage is 0 then end is just count
        if ($this->linesPerPage==0) {
            $end = $rcount;
        }

        // navigation
        $this->printNavigation();

        // table start
        echo "<table class=\"pt_table\">\n";

        // table headers
        echo "<tr class=\"pt_headers\">\n";
        foreach ($this->headings as $key => $heading) {
            if (empty($this->sortfields[$key]) or $this->printable) {
                echo "<th>$heading</th>";
            }
            elseif ($this->sortfields[$key]==$this->sortby) {
                $newdir = ($this->sortdir == 'asc') ? 'desc' : 'asc';
                $arr = ($this->sortdir == 'asc') ? '&darr;' : '&uarr;';
                echo "<th><a href=\"$url{$this->sortfields[$key]}&amp;sortdir=$newdir\">$heading $arr</a></th>";
            }
            else {
                echo "<th><a href=\"$url{$this->sortfields[$key]}&amp;sortdir=asc\">$heading</a></th>";
            }
        }
        echo "</tr>\n";

        // data
        $count = 0;
        foreach ($this->records as $record) {
            $count++;

            // miss the stuff we don't want
            if (($count<$start) or ($count>$end)) {
                continue;
            }

            $class = ($count % 2)==0 ? 'tr_even' : 'tr_odd';
            echo "<tr class=\"tr_record $class\">\n";
            $fields = (array)$record;
            foreach ($this->fields as $heading) {
                if (isset($fields[$heading])) {
                    $value = $fields[$heading];
                    echo "<td>$value</td>";
                }
                else {
                    echo "<td>&nbsp;</td>";
                }
            }
            echo "</tr>\n";
        }

        // table end
        echo "</table>\n";

        // navigation again
        $this->printNavigation();
    }

}
?>
