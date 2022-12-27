# PHP IMAP Connection Class


## TR
PHP programlama dilini kullanarak IMAP E-Posta kutusuna bağlanmak için kullanılacak bir sınıftır. Bu class zaman içinde geliştirilmeye devam edilecektir. Her türlü fikre açığım.


## EN
It is a class to be used to connect to an IMAP Email box using the PHP programming language. This class will continue to be developed over time. I'm open to any ideas.


## How To Use?

```
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





```
