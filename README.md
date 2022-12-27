# PHP IMAP Connection and Save Mails IMAP Folders Class


## TR
PHP programlama dilini kullanarak IMAP E-Posta kutusuna bağlanmak için kullanılacak bir sınıftır. Ek olarak gönderdiğiniz mailleri IMAP klasörlerinizde saklayabilme işlemi eklenmiştir. Bu class zaman içinde geliştirilmeye devam edilecektir. Her türlü fikre açığım.
* Şuan sadece YANDEX ile test edildi


## EN
It is a class to be used to connect to an IMAP Email box using the PHP programming language. Additionally, the process of storing the e-mails you send in your IMAP folders has been added. This class will continue to be developed over time. I'm open to any ideas.
* Currently only tested with YANDEX


## To Do List
- [ ]  Read & get Mail's Attachments 
- [ ]  Save mail's attachment in a hosting


## How To Use?

I hope the explanations were sufficient.

```php
//add class your project and call

$hostIMAP = "imap.yandex.com.tr:993/imap/ssl";
$username = "your_user_name@domain.com";
$password = "your_passowrd";

// Connect IMAP Folders
$IMAP = new \IMAP($hostIMAP, $username, $password);

// Get IMAP folders List
$folders = $IMAP->getIMAPFolders(0);

// Get IMAP mail List - Return just mail's msgno properties not mail itself
$emails = $IMAP->getAllMails();

// Get a single mail overviews
$overview = $IMAP->getMailOverview(5); 

// Get a single mail seen or unseen status
$seen = $IMAP->getMailSeen(5); 

// Get a single mail Header information
$header = $IMAP->getMailHeaderInfo(5);

// Get a single mail Structure information. 
$structure = $IMAP->getMailStructure(1);
                    
// Checking mail Structure, are have "parts" properties?
$parts = $IMAP->checkMailHaveParts(1);

// Get a single mail body. Some mails be multiparts (you can check it with checkMailHaveParts method ) and you must choose which want to one seeing.
// if mail no multiparts then this method give you, what is mail having.

// Get a single mail HTML body parts
$body = $IMAP->getMailBody(5, "HTML");

// Get a single mail PLAIN body parts
$body = $IMAP->getMailBody(5, "PLAIN");


// If you want, you can save sended mail in a IMAP Folder you choosing. If dont choose, mails save "Sent" folder. 
// You can see Folders List with getIMAPFolders method.
$IMAP->saveMailIMAPFolder($this->mail, "MySendedFolder");
// NOTE: For this method, $this->mail is a PHPMailer's mail. And must be like 

// $this->mail = new PHPMailer(); 
// ....
// $this->mail->Send();
// $IMAP->saveMailIMAPFolder($this->mail, "MySendedFolder");

// Then you's sended mail, can be saved IMAP Folders you choosing.


```





