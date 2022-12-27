<?php

/**
 * M-IMAP  Class.
 * PHP Version 5.5
 *
 * @see https://github.com/MustafaBKLZ/PHP-IMAP-Connection-Class The M-IMAP GitHub project
 *
 * @author    Mustafa BÜKÜLMEZ <mustafabukulmez3446@gmail.com>
 * @copyright 2023 Mustafa BÜKÜLMEZ
 * @license   
 * @note      This program is distributed in the hope that it will be useful - WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 */



class IMAP
{
    /**
     * The IMAP M-IMAP Version number.
     *
     * @var string
     */
    const VERSION = '0.3';

    /**
     * HOST for IMAP connection. Süslü parantezler ve fazlası olmadan. - imap.yandex.com.tr:993/imap/ssl
     *
     * @var string
     */
    var $ImapHost = "";

    /**
     * Username for IMAP connection - mailaddress@domain.com
     *
     * @var string
     */
    var $Username = "";

    /**
     * User Password adress for IMAP connection - password
     *
     * @var string
     */
    var $Password = "";

    /**
     * Connected IMAP Address
     *
     * @var IMAP\Connection
     */
    var $IMAP;

    /**
     * Getted Emails
     *
     * @var array
     */
    var $emails;

    /**  __construct method
     * @param string    $host       Not use '{' , '}' and folder name. Just like this = imap.yandex.com.tr:993/imap/ssl
     * @param string    $username   Wanted to connected mail address
     * @param string    $passowrd   Wanted to connected mail address's password
     * @param string    $passowrd   Wanted to connected mail address's IMAP folders. 
     *          - NOTE: You must check first IMAP folders with getIMAPFolders method.
     */
    public function __construct($host, $username, $passowrd, $folder = "INBOX")
    {
        $this->ImapHost = $host;
        $this->Username = $username;
        $this->Password = $passowrd;
        $this->IMAP = $this->connectIMAP($folder);
    }

    /**
     * Connect and Open IMAP Folder.
     * @param string    $IMAP_FOLDER  Wanted to opened IMAP FOLDER
     *
     * @return IMAP\Connected call in __construct
     */
    protected function connectIMAP($IMAP_FOLDER = "INBOX")
    {
        $IMAP = imap_open("{" . $this->ImapHost . "}" . $IMAP_FOLDER, $this->Username, $this->Password) or die('Cannot connect to email: ' . imap_last_error());
        return  $IMAP;
    }

    /** Get all IMAP Folders you have.
     * 
     * @param int $how int if 0 get full path ( {imap.yandex.com.tr:993/imap/ssl}INBOX ), if 1 get just folder name ( INBOX )
     * 
     * @return array IMAP Folder list
     */
    public function getIMAPFolders($how)
    {
        if ($how == 0) {
            // __construct çalışınca olması gerektiği değeri alacağı için sorun yok.
            return imap_list($this->IMAP, "{" . $this->ImapHost . "}", "*");
        }
        if ($how == 1) {
            // __construct çalışınca olması gerektiği değeri alacağı için sorun yok.
            return str_replace("{" . $this->ImapHost . "}", "", imap_list($this->IMAP, "{" . $this->ImapHost . "}", "*"));
        }
    }

    /**
     * Save sended mail in a IMAP folder
     * 
     * @param string    $mailMessage    Sended Mail itself.  $this->mail = new PHPMailer(); Need $this->mail...
     * @param string    $folderName     Which folder. You can see folders with getIMAPFolders method.
     * 
     */
    public function saveMailIMAPFolder($mailMessage, $folderName)
    {
        $message = $mailMessage->MIMEHeader . $mailMessage->MIMEBody;
        $path =  (isset($folderName) && !is_null($folderName) ?  $folderName : "Sent");
        // __construct çalışınca olması gerektiği değeri alacağı için sorun yok.
        imap_append($this->IMAP, "{" . $this->ImapHost . "}" . $path, $message);
    }

    /**
     * Get mails list in a folder. Just return mails msgno's.
     * 
     * @param string    $criteria Wanted to which mail criteria. You can get more information from here https://www.php.net/manual/en/function.imap-search.php
     * @param bool      If wanted to DESC sort send true
     * 
     * @return array    Return all mails msgno properties. Not Mail itself just msgno's.
     */
    public function getAllMails($criteria = "ALL", $sort = true)
    {
        $this->emails = imap_search($this->IMAP, $criteria);
        if (is_array($this->emails)) {
            if ($sort) {
                rsort($this->emails);
            }
        }
        return $this->emails;
    }

    /**
     * Get a single mail's overviews. This method Turn returns object(stdClass) to array
     * 
     * @param int      Type the msgno value of the mail to get the overview information.
     * @param int      Sequence will contain a sequence of message indices or UIDs, if this parameter is set to FT_UID.
     * 
     * @return array    Mail's all overview infos. https://www.php.net/manual/en/function.imap-fetch-overview.php
     */
    public function getMailOverview($msgno, $flags = 0)
    {
        $overview = imap_fetch_overview($this->IMAP, $msgno, $flags);

        // Turn $overview  object(stdClass) to array
        $overview = json_encode($overview[0], true);
        $overview = json_decode($overview, true);
        // Turn $overview  object(stdClass) to array

        // quoted_printable_decode() method decode UTF-8 HEX. Example: if you see in subject in like =C5=9E term, this mean "LATIN CAPITAL LETTER S WITH CEDILLA". 
        // You can look for more information https://www.utf8-chartable.de/unicode-utf8-table.pl?start=256
        foreach ($overview as $key => $value) {
            $overview[$key] = quoted_printable_decode($value);
        }

        return $overview;
        // Example Output
        //array (size=1)
        //   0 => 
        //     object(stdClass)[6]
        //       public 'subject' => string 'MailSubject' (length=106)
        //       public 'from' => string 'HubSpot <from@mail.com>' (length=29)
        //       public 'to' => string 'to@mail.com' (length=25)
        //       public 'date' => string 'Sun, 29 May 2022 16:03:09 -0400' (length=31)
        //       public 'message_id' => string '<1653854588928.33dad449-5fc4-4894-9de9-f3f8e47d3067@notifybf1.eu1.hubspot.com>' (length=78)
        //       public 'size' => int 18785
        //       public 'uid' => int 11
        //       public 'msgno' => int 5
        //       public 'recent' => int 0
        //       public 'flagged' => int 0
        //       public 'answered' => int 0
        //       public 'deleted' => int 0
        //       public 'seen' => int 1
        //       public 'draft' => int 0
        //       public 'udate' => int 1653854590
    }

    /**
     * Get a single mail's seen or unseen status information.
     * 
     * @param int      Type the msgno value of the mail to get the seen/unseen information.
     * @param int      Sequence will contain a sequence of message indices or UIDs, if this parameter is set to FT_UID.
     * 
     * @return string   Will If mail seen, return "seen" ortherwise "unseen"
     */
    public function getMailSeen($msgno, $flags = 0)
    {
        $overview =  $this->getMailOverview($msgno, $flags);
        return  $overview["seen"] == 1 ? "seen" : "unseen";
    }

    /**
     * Get a single mail's Header information.
     * 
     * @param int      Type the msgno value of the mail to get the header information.
     *      
     * @return array    Mail's header informations
     */
    public function getMailHeaderInfo($msgno)
    {
        $header =   imap_headerinfo($this->IMAP, $msgno);

        // Turn $header object(stdClass) to array
        $header =  json_encode($header, true);
        $header =  json_decode($header, true);
        // Turn $header object(stdClass) to array

        // quoted_printable_decode() method decode UTF-8 HEX. Example: if you see in subject in like =C5=9E term, this mean "LATIN CAPITAL LETTER S WITH CEDILLA". 
        // You can look for more information https://www.utf8-chartable.de/unicode-utf8-table.pl?start=256
        foreach ($header as $key => $value) {
            if (!is_array($value)) {
                $header[$key] = quoted_printable_decode(strval($value));
            } else {
                foreach ($value as $key_s1 => $value_s1) {
                    if (!is_array($value_s1)) {
                        $value[$key_s1] = quoted_printable_decode(strval($value_s1));
                    } else {
                        foreach ($value_s1 as $key_s2 => $value_s2) {
                            if (!is_array($value_s2)) {
                                $value_sub[$key_s2] = quoted_printable_decode(strval($value_s2));
                            } else {
                                foreach ($value_s2 as $key_s3 => $value_s3) {
                                    $value_s2[$key_s3] = quoted_printable_decode(strval($value_s3));
                                }
                            }
                        }
                    }
                }
            }
        }

        return $header;
        // example output

        // object(stdClass)[7]
        // public 'date' => string 'Sun, 25 Dec 2022 21:21:21 -0000' (length=31)
        // public 'Date' => string 'Sun, 25 Dec 2022 21:21:21 -0000' (length=31)
        // public 'subject' => string 'mail subject' (length=64)
        // public 'Subject' => string 'mail subject' (length=64)
        // public 'message_id' => string '<20221225212121.3AB58938007C@passport-s27.passport.yandex.net>' (length=62)
        // public 'toaddress' => string 'to@mail.com' (length=26)
        // public 'to' => 
        //   array (size=1)
        //     0 => 
        //       object(stdClass)[6]
        //         public 'mailbox' => string 'to' (length=7)
        //         public 'host' => string 'mail.com' (length=18)
        // public 'fromaddress' => string 'Yandex ID <noreply@id.yandex.com.tr>' (length=36)
        // public 'from' => 
        //   array (size=1)
        //     0 => 
        //       object(stdClass)[9]
        //         public 'personal' => string 'Yandex ID' (length=9)
        //         public 'mailbox' => string 'noreply' (length=7)
        //         public 'host' => string 'id.yandex.com.tr' (length=16)
        // public 'reply_toaddress' => string 'Yandex ID <noreply@id.yandex.com.tr>' (length=36)
        // public 'reply_to' => 
        //   array (size=1)
        //     0 => 
        //       object(stdClass)[10]
        //         public 'personal' => string 'Yandex ID' (length=9)
        //         public 'mailbox' => string 'noreply' (length=7)
        //         public 'host' => string 'id.yandex.com.tr' (length=16)
        // public 'senderaddress' => string 'Yandex ID <noreply@id.yandex.com.tr>' (length=36)
        // public 'sender' => 
        //   array (size=1)
        //     0 => 
        //       object(stdClass)[11]
        //         public 'personal' => string 'Yandex ID' (length=9)
        //         public 'mailbox' => string 'noreply' (length=7)
        //         public 'host' => string 'id.yandex.com.tr' (length=16)
        // public 'Recent' => string ' ' (length=1)
        // public 'Unseen' => string ' ' (length=1)
        // public 'Flagged' => string ' ' (length=1)
        // public 'Answered' => string ' ' (length=1)
        // public 'Deleted' => string ' ' (length=1)
        // public 'Draft' => string ' ' (length=1)
        // public 'Msgno' => string '  23' (length=4)
        // public 'MailDate' => string '25-Dec-2022 21:21:21 +0000' (length=26)
        // public 'Size' => string '29625' (length=5)
        // public 'udate' => int 1672003281
    }

    /**
     * Get a single mail's Structure information. Some mails have "parts" value. PLAIN AND HTML.
     * 
     * @param int      Type the msgno value of the mail to get the Message Structure information.
     * 
     * @return array    Mail's Structure informations
     */
    public function getMailStructure($msgno)
    {
        $structure = imap_fetchstructure($this->IMAP, $msgno);
        // Turn $structure  object(stdClass) to array
        $structure = json_encode($structure, true);
        $structure = json_decode($structure, true);
        // Turn $structure  object(stdClass) to array

        return $structure;
        // example output
        //  object(stdClass)[6]
        //   public 'type' => int 1
        //   public 'encoding' => int 0
        //   public 'ifsubtype' => int 1
        //   public 'subtype' => string 'ALTERNATIVE' (length=11)
        //   public 'ifdescription' => int 0
        //   public 'ifid' => int 0
        //   public 'ifdisposition' => int 0
        //   public 'ifdparameters' => int 0
        //   public 'ifparameters' => int 1
        //   public 'parameters' => 
        //     array (size=1)
        //       0 => 
        //         object(stdClass)[11]
        //           public 'attribute' => string 'boundary' (length=8)
        //           public 'value' => string '----=_Part_146568_1689208950.1653854588957' (length=42)
        //   public 'parts' => 
        //     array (size=2)
        //       0 => 
        //         object(stdClass)[10]
        //           public 'type' => int 0
        //           public 'encoding' => int 4
        //           public 'ifsubtype' => int 1
        //           public 'subtype' => string 'PLAIN' (length=5)
        //           public 'ifdescription' => int 0
        //           public 'ifid' => int 0
        //           public 'lines' => int 5
        //           public 'bytes' => int 271
        //           public 'ifdisposition' => int 0
        //           public 'ifdparameters' => int 0
        //           public 'ifparameters' => int 1
        //           public 'parameters' => 
        //             array (size=1)
        //               ...
        //       1 => 
        //         object(stdClass)[8]
        //           public 'type' => int 0
        //           public 'encoding' => int 4
        //           public 'ifsubtype' => int 1
        //           public 'subtype' => string 'HTML' (length=4)
        //           public 'ifdescription' => int 0
        //           public 'ifid' => int 0
        //           public 'lines' => int 230
        //           public 'bytes' => int 14741
        //           public 'ifdisposition' => int 0
        //           public 'ifdparameters' => int 0
        //           public 'ifparameters' => int 1
        //           public 'parameters' => 
        //             array (size=1)
        //              ...
    }

    /**
     * Get a single mail's Parts information. Some mails have "parts" value. PLAIN AND HTML. If this mail be multipart, we need know this for getting body
     * 
     * @param int      Type the msgno value of the mail to get the Multiparts information.
     * 
     * @return array    Mail's Parts informations. If multiparts True then False
     * 
     */
    public function checkMailHaveParts($msgno)
    {
        $structure = $this->getMailStructure($msgno);
        $value =  array_key_exists("parts",  $structure);
        return   $value;
    }

    /**
     * Get a single mail's a single Part. If mail is multiparts, then this method will give what you want otherwise give what mail have. 
     * If mail is multiparts but you dont select any part, this method will give you part of HTML.
     * 
     * @param int      Type the msgno value of the mail to get the parts information.
     * 
     * @return string    Mail's Parts. If multiparts then you choose PLAIN or HTML else ypu get what mail's have.
     * 
     */
    public function getMailBody($msgno, $whichPart = "HTML")
    {
        $body = "";
        $flag = 0;
        if ($this->checkMailHaveParts($msgno)) {
            if (isset($whichPart) && !empty($whichPart)) {
                if ($whichPart == "HTML")
                    $flag = "2";
                if ($whichPart == "PLAIN")
                    $flag = "1";
            }
        } else {
            $structure =  $this->getMailStructure($msgno);
            $value =  $structure["subtype"];

            if ($value == "HTML")
                $flag = "2";
            if ($value == "PLAIN")
                $flag = "1";
        }


        $body = imap_fetchbody($this->IMAP, $msgno,  $flag); // Body Part Of HTML

        // quoted_printable_decode() method decode UTF-8 HEX. Example: if you see in subject in like =C5=9E term, this mean "LATIN CAPITAL LETTER S WITH CEDILLA". 
        // You can look for more information https://www.utf8-chartable.de/unicode-utf8-table.pl?start=256
        return   quoted_printable_decode($body);
    }
}
