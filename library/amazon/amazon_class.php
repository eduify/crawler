<?php
/*

    filename:            amazon_class.php
    created:            7/17/2002, © 2002 php9.com Calin Uioreanu
    descripton:        webservice class definition for Amazon Parser

*/

// lock mechanism for amazon webservices servers
define('AWS_LOCK_FILE', 'aws_lock');
// if a query fails, we will not bother amazon webservices servers for a given time
define('AWS_LOCK_TIME', 30);

class Amazon_WebService {

    var $sData;
    var $arAtribute;
    var $iNumResults;
    var $sTemplate;

    /**
        void Amazon_WebService(void)
    
        constructor
    */
    function Amazon_WebService()
    {
        $this->parser = xml_parser_create();
    } // end func
    
    
    /**
        boolean parse(void)
    
        parses a XML file
    */
    function parse() {
        if (!$this->testLock()) {
            // prevent html caching
            $_GLOBALS['bFeedError'] = true;
            return false;
        }

        // set the handlers
        xml_set_object($this->parser, $this);
        xml_set_element_handler($this->parser, 'startHandler', 'endHandler');
        xml_set_character_data_handler($this->parser, 'cdataHandler');

        if (!is_resource($this->fp)) {
            $this->createLock();
            return false;
        }

        while ($data = fread($this->fp, 2048)) {
            $err = xml_parse($this->parser, $data);
            if (!$err) {
                fclose($this->fp);
                return $err;
            }
        }
        fclose($this->fp);

        // free the parser resource
        xml_parser_free($this->parser);

        return true;
    } // end func


    /**
        void startHandler (obj , str , arr)

        Event handler called by the expat library when an element's begin tag is encountered.    
    */
    function startHandler($parser, $sTag, $arAttr) {

       //  Start with empty sData string.
       $this->sData = '';

       //  Put each attribute into the Data array.
       foreach ($arAttr as $Key=> $Val) {
          $this->arAtribute["$sTag:$Key"] = trim($Val);
       }
    }

    /**
        void cdataHandler (obj, str)

        Event handler called by the expat library when Character Data are encountered.    
    */
    function cdataHandler($parser, $sTag) {

       $this->sData .= $sTag;

    }

    /**
        void endHandler (obj, str)

        Event handler called by the expat library when an element's end tag is encountered.    
    */
    function endHandler($parser, $sTag) {

        static
            $MODE,
            $ASIN, 
            $PRODUCTNAME, 
            $CATALOG,
            $AUTHORS,
        $ARTISTS,
        $STARRING,
        $DIRECTORS,
            $THEATRICALRELEASEDATE,
            $RELEASEDATE,
            $MANUFACTURER,
            $IMAGEURLSMALL,
            $IMAGEURLMEDIUM,
            $IMAGEURLLARGE,
            $LISTPRICE,
            $OURPRICE,
            $USEDPRICE,
            $REFURBISHEDPRICE,
            $THIRDPARTYNEWPRICE,
            $SALESRANK,
        $LISTS,
        $TRACKS,
        $BROWSELIST,
            $MEDIA,
            $NUMMEDIA,
            $ISBN,
        $FEATURES,
            $MPAARATING,
            $PLATFORM,
            $AVAILABILITY,
            $UPC,
        $ACCESSORIES,
            $PRODUCTDESCRIPTION,
        $REVIEWS,
        $THIRDPARTYPRODUCTINFO,
        $AVGCUSTOMERRATING,
        $CUSTOMERREVIEW,
        $THIRDPARTYPRODUCTDETAILS,
        $SIMILARPRODUCTS,
            $TOTALRESULTS
        ;

       // put the $this->sData into a string.
       $sData = $this->sData;

        switch (strtoupper ($sTag)) {

            case 'ACTOR':
                // build the list
                $STARRING[] = $sData;
                break;
            case 'ARTIST':
                // build the list
                $ARTISTS[] = $sData;
                break;
            case 'DIRECTOR':
                // build the list
                $DIRECTORS[] = $sData;
                break;
            case 'AUTHOR':
                // build the Authors list
                $AUTHORS .= ($AUTHORS?', ':'') . $sData;
                break;
            case 'LISTID':
                // build the list
                $LISTS[] = $sData;
                break;
            case 'TRACK':
                // build the list
                $TRACKS[] = $sData;
                break;
            case 'BROWSENAME':
                // build the list
                $BROWSELIST[] = $sData;
                break;
            case 'FEATURE':
                // build the list
                $FEATURES[] = $sData;
                break;
            case 'ACCESSORY':
                // build the list
                $ACCESSORIES[] = $sData;
                break;
            case 'PRODUCT':
                // build the list
                $SIMILARPRODUCTS[] = $sData;
                break;
            case 'AVGCUSTOMERRATING':
                // build the list
                $AVGCUSTOMERRATING = $sData;
                break;
            case 'CUSTOMERREVIEW':
                // build the list
                $REVIEWS[] = $CUSTOMERREVIEW;
                break;
            case 'THIRDPARTYPRODUCTDETAILS':
                // build the list
                $THIRDPARTYPRODUCTINFO[] = $THIRDPARTYPRODUCTDETAILS;
                break;
            case 'SELLERID':
            case 'SELLERNICKNAME':
            case 'EXCHANGEID':
            case 'OFFERINGPRICE':
            case 'CONDITION':
            case 'CONDITIONTYPE':
            case 'EXCHANGEAVAILABILITY':
            case 'SELLERCOUNTRY':
            case 'SELLERSTATE':
            case 'SELLERRATING':
                // build the list
                $THIRDPARTYPRODUCTDETAILS[$sTag] = $sData;
                break;
            case 'RATING':
                // build the list
                $CUSTOMERREVIEW['Rating'] = $sData;
                break;
            case 'SUMMARY':
                // build the list
                $CUSTOMERREVIEW['Summary'] = $sData;
                break;
            case 'COMMENT':
                // build the list
                $CUSTOMERREVIEW['Comment'] = $sData;
                break;
            case 'ASIN':
            case 'MODE':
            case 'PRODUCTNAME':
            case 'CATALOG':
            case 'THEATRICALRELEASEDATE':
            case 'MPAARATING':
            case 'RELEASEDATE':
            case 'MANUFACTURER':
            case 'IMAGEURLSMALL':
            case 'IMAGEURLMEDIUM':
            case 'IMAGEURLLARGE':
            case 'LISTPRICE':
            case 'OURPRICE':
            case 'USEDPRICE':
            case 'REFURBISHEDPRICE':
            case 'THIRDPARTYNEWPRICE':
            case 'SALESRANK':
            case 'MEDIA':
            case 'PRODUCTDESCRIPTION':
            case 'NUMMEDIA':
            case 'ISBN':
            case 'PLATFORM':
            case 'AVAILABILITY':
            case 'UPC':
                $$sTag = $sData;
                break;

            case 'ERRORMSG':
                // if this is a global error, avoid caching the module
                if (!$this->iNumResults) {
                    global $bParseError;
                    $bParseError = true;    
                }
                break;

            case 'DETAILS':
                // offer some details
                $sBookUrl = PRODUCT_DETAIL_URL . $ASIN;
                //Details finished
                require($this->sTemplate);

            // empty the product related information
            $STARRING= array ();
            $ARTISTS= array ();
            $DIRECTORS= array ();
            $AUTHORS = '';
            $REVIEWS = array ();
            $THIRDPARTYPRODUCTINFO = array();
            $BROWSELIST= array ();
            $FEATURES= array ();
            $ACCESSORIES = array ();
            $AVGCUSTOMERRATING = array ();
            $CUSTOMERREVIEW = array ();
            $SIMILARPRODUCTS = array ();

                $ASIN='';
                $PRODUCTNAME='';
                $CATALOG='';
                $AUTHORS='';
                $THEATRICALRELEASEDATE='';
                $RELEASEDATE='';
                $MANUFACTURER='';
                $IMAGEURLSMALL='';
                $IMAGEURLMEDIUM='';
                $IMAGEURLLARGE='';
                $LISTPRICE='';
                $OURPRICE='';
                $USEDPRICE='';
                $REFURBISHEDPRICE='';
                $THIRDPARTYNEWPRICE='';
                $SALESRANK='';
                $MEDIA='';
                $PRODUCTDESCRIPTION = '';
                $NUMMEDIA='';
                $ISBN='';
                $MPAARATING='';
                $PLATFORM='';
                $AVAILABILITY='';
                $UPC='';

                // increase global counter 
                $this->iNumResults++;
                break;

            case 'TOTALRESULTS':
                $this->arAtribute['TotalResults'] = $sData;
                break;

            case 'PRODUCTINFO':
                break;
        }
    }


    // }}}
    // {{{ setInputUrl()

    /**
    * Defines
    *
    * @param    string     sUrl (full url)
    * @param    integer      iTimeout (url open timeout)
    * @return   resource    fsockopen handle of the given file
    * @throws   XML_Parser_Error
    * @see      setInput(), setInputFile()
    * @access   public
    */

    
    /**
        bool setInputUrl(str, int)
    
        tries to open a connection to the given url with the given timeout
    */
    function setInputUrl($sUrl, $iTimeout) {

        if (!$this->testLock()) {
            // prevent html caching
            $_GLOBALS['bFeedError'] = true;
            return false;
        }

        $arUrl = parse_url($sUrl);
        $sHost = $arUrl['host'];
        if (!@$iPort = $arUrl['port']) {
            $iPort = 80; // default HTTP port
        }
        $sFullPath = $arUrl['path'] . '?' . $arUrl['query'];

        $fp = fsockopen($sHost, $iPort, $errno, $errstr, $iTimeout);
        if(!is_resource($fp)) {
            // return an error
        echo '<!-- '. $sUrl . " issued: $errstr ($errno)\n" .' -->@';
            $this->createLock();
            return false;
        } else {
            fputs($fp,"GET $sFullPath HTTP/1.0\r\nHost: " . $sHost. "\r\n\r\n");

            // win32
            if (function_exists('socket_set_timeout')) {
                @socket_set_timeout($fp, $iTimeout);            
            }
            // in blocking mode it will wait for data to become available on the socket
            socket_set_blocking($fp, true);

            // get the first line and determine the answer-code
            $sLine = fgets($fp , 1024);
            $iCode = preg_replace("/.*(\d\d\d).*/i" , "\\1" , $sLine);

            // an error occurred if code is not between 200 and 399
            $error = null;
            if ($iCode != 200)
            {
                // prevent html caching
                $_GLOBALS['bFeedError'] = true;
                error_log (
                    "\n". time() .':'. $sUrl .':'. $sLine .':'. $_SERVER['REQUEST_URI'],
                    3, 
                    'htmlcache/search/return_code_failures.log'
                );
                fclose($fp);
                $this->createLock();
                return false;
            }

            $sHeader = '';
            // no error - now determine start of data (skipping header)
            while (!feof($fp))
            {
                $sLine = fgets($fp , 1024);
                if (strlen($sLine) < 3)
                {
                    break;
                }
            }

            $this->fp = $fp;
            return true;
        }
    } // end func

    /**
    createLock()

    lock
    */
    function createLock()
    {
//error_log ("\n". date ('D M j G:i:s', time() + 6*3600) .' : AWS Failed'  , 3, 'htmlcache/search/aws_uptime.log'); 
        if (!$handle = fopen(AWS_LOCK_FILE, 'w')) {
            echo 'cannot open lock';
            return false;
        }
        fclose($handle);
        return true;
    } // end func

    /**
    testLock()

    test lock for a given time, eventually release it
    */
    function testLock()
    {
        if (!file_exists(AWS_LOCK_FILE)) {
            return true;
        }
        if ((time() - filemtime(AWS_LOCK_FILE)) < AWS_LOCK_TIME) {
//error_log ("\n". date ('D M j G:i:s', time() + 6*3600) .' : AWS call avoided'  , 3, 'htmlcache/search/aws_uptime.log'); 
            return false;
        }
        // release lock
        if (!$handle = unlink(AWS_LOCK_FILE)) {
            error_log ("\n cannot release lock: ". time() .'-'. filemtime(AWS_LOCK_FILE) .'='. (time() - filemtime(AWS_LOCK_FILE)).'<'. AWS_LOCK_TIME, 3, 'release_lock.log');
            return false;
        }
//error_log ("\n". date ('D M j G:i:s', time() + 6*3600) .' : AWS Recovered'  , 3, 'htmlcache/search/aws_uptime.log'); 
        return true;
    } // end func

} // end class definition

?> 