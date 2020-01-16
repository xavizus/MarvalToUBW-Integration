<?php

/**
 * Creating a Config Class.
 * @var stdClass
 */
$config = new \stdClass;

/**
 * NVOA Kostnadsst�llen
 * @var array
 */

$config->NVOA = array(
    28000,
    28100,
    28101,
    28110,
    28111,
    28115,
    28200,
    28201,
    28211,
    28212,
    28213,
    28214,
    28220,
    28300,
    28310,
    28320,
    28400,
    28401,
    28410,
    28420,
    28430,
    28440,
    28500,
    28600,
    28610,
    18200
);

/**
 * Seperator for the content of the file.
 * @var const string
 */

define("SEPERATOR", ";");

/**
 * Stores all found costcenter. This array will contain the class CostCenter
 * @var array
 */
$costcenters = array();

/**
 * CostCenter class handles the information storage of all the bills that need
 * to be created as an invoice against one costcenter.
 * 
 * @param integer
 * @param string
 * @param string
 * @param string
 * @param integer
 * @param boolean
 * @return null
 */

class CostCenter
{

    /**
     *  Stores a single value of the costcenter
     * @var NULL
     */
    private $costcenter = NULL;

    /**
     * Stores an array of casenumbers
     * @var array
     */
    private $caseNumber = array();

    /**
     * Stores an array of names
     * @var array
     */
    private $Names = array();

    /**
     * Stores an array of descriptions
     * @var array
     */
    private $descriptions = array();

    /**
     * Stores an array of costs
     * @var array
     */
    private $costs = array();

    /**
     * Stores a boolen value of either if it's a Nacka Vatten och Avfall costcenter or not.
     */
    private $isNVOA = NULL;

    /**
     * This construct requires all parameters to be set and will then store the value in their right
     * holder.
     * @param integer
     * @param string
     * @param string
     * @param string
     * @param integer
     * @param boolean
     * @return null
     */
    public function __construct($costcenter, $caseNumber, $Names, $descriptions, $costs, $isNVOA)
    {
        $this->isNVOA = $isNVOA;
        $this->costcenter = $costcenter;
        $this->caseNumber[] = $caseNumber;
        $this->Names[] = $Names;
        $this->descriptions[] = $descriptions;
        $this->costs[] = (int) $costs;
    }

    /**
     * Returns the costcenter of the class
     * @return int
     */
    public function getCostcenter()
    {
        return $this->costcenter;
    }

    /**
     * Adds a new row for each array in the class.
     * @param string
     * @param string
     * @param string
     * @param integer
     * @return NULL
     */
    public function addRow($caseNumber, $Names, $descriptions, $costs)
    {
        $this->caseNumber[] = $caseNumber;
        $this->Names[] = $Names;
        $this->descriptions[] = $descriptions;
        $this->costs[] = (int) $costs;
    }

    /**
     * Returns all information as an array
     * @return array
     */
    public function getData()
    {
        $data = array();
        $data['costcenter'] = $this->costcenter;
        $data['caseNumber'] = $this->caseNumber;
        $data['Names'] = $this->Names;
        $data['descriptions'] = $this->descriptions;
        $data['costs'] = $this->costs;
        $data['isNVOA'] = $this->isNVOA;
        return $data;
    }
}

echo "<head> <meta charset=\"ISO-8859-1\"><head>";
/**
 * Open the file to read.
 */
foreach (glob('nk01*') as $file) {
    echo $file . "</br>";

    /**
     * Check if there are any files
     */
    if ($file == null || $file == "") {
        die("Det finns ingen fil att läsa in.");
    }

    /**
     * Check if the folder exisits.
     */
    if (!file_exists('inlästfaktura')) {
        mkdir('inlästfaktura', 0777, true);
    }

    /**
     * Try to open the file
     */
    $fp = fopen($file, "r") or die("Kunde inte öppna filen");

    /**
     * Storage for each three lines.
     */
    $lines = array();
    /**
     * Read orginal files until end of file.
     */
    while (!feof($fp)) {
        
        /**
         * Make sure that 3 lines have been read.
         */
        if (count($lines) == 3) {
            /**
             * Found is used when checking if costcenter already exists.
             */
            $found = false;
            /**
             * Save each line as it's own variable and seperate the string
             * to an array.
             */
            $line1 = explode(SEPERATOR, $lines[0]);
            $line2 = explode(SEPERATOR, $lines[1]);
            $line3 = explode(SEPERATOR, $lines[2]);

            // Get costcenter
            $costcenter = (int) $line1[1];

            /**
             * Why do Marval suck, and are not persistent in their seperators?
             */
            $newLine = explode("-", $line2[4], 3);

            // Get costcenter
            $caseNumber = trim($newLine[0] . "-" . $newLine[1]);

            /**
             * Why do people have multiple dashes in their names?
             * 
             * Checks if there are two dashes
             */
            if (substr_count($newLine[2], " - ") == 2) {
                /**
                 * Seperate once again...
                 */
                $newLine = explode(" - ", $newLine[2], 3);
                /**
                 * Get the name and remove spaces at the start and at the end
                 */
                $name = rtrim(ltrim($newLine[0])) . " - " . rtrim(ltrim($newLine[1]));
                /**
                 * Get description. Clear both left and right side of spaces. Should be a function...
                 */
                $description = rtrim(ltrim($newLine[2]));
            } else {
                /**
                 * Same as above but with only one dash.
                 */
                $newLine = explode(" - ", $newLine[2], 2);
                $name = rtrim(ltrim($newLine[0]));
                $description = rtrim(ltrim($newLine[1]));
            }
            /**
             * Get cost for the specific post.
             */
            $cost = (int) $line2[6];

            /**
             * Used to check if the costcenter belongs to Nacka Vatten och Avfall AB.
             */
            $isNVOA = false;
            /**
             * Searches for the costcenter in all NVOA costcenters.
             */
            if (in_array($costcenter, $config->NVOA)) {
                $isNVOA = true;
            }
            /**
             * If this is the first costcenter we enconter.
             */
            if (empty($costcenters)) {
                /**
                 * Add a new costcenter to the array.
                 */
                $costcenters[] = new CostCenter($costcenter, $caseNumber, $name, $description, $cost, $isNVOA);
            } else {
                /**
                 * Let's check if a specific costcenter already exists.
                 */
                foreach ($costcenters as $CostCenter) {
                    if ($CostCenter->getCostcenter() == $costcenter) {
                        /**
                         * If it exisist, add a row to the invoice to that costcenter.
                         */
                        $CostCenter->addRow($caseNumber, $name, $description, $cost);
                        $found = true;
                    }
                }
                /**
                 * If it's not found, then create a new costcenter.
                 */
                if (!($found)) {
                    $costcenters[] = new CostCenter($costcenter, $caseNumber, $name, $description, $cost, $isNVOA);
                }
            }
            /**
             * Clear lines variable from old data.
             */
            $lines = array();
        }
        /**
         * Read data to the lines variable.
         */
        $lines[] = fgets($fp);
    }
    /**
     * Close the file
     */
    fclose($fp);

    /**
     * Rename, used to move the file to another directory. (Great name of the function!)
     */
    rename($file, "inl�stfaktura/$file");
}

/**
 * Let's check if we got a file called Below400.
 * The file stores all invoices below 400 kr.
 */
try {
    $fp = @fopen("Below400", "r");
} catch (Exception $e) {
    $fp = false;
}
/**
 * if we got a file called Below400
 */
if ($fp) {
    $line = NULL;
    /**
     * While it's not end of file and file is still open.
     * 
     * I thank god that variables are keep their values even in while loop.
     */
    while (!feof($fp) && $fp) {
        /**
         * if our line contain a H as first letter.
         * This row contains costcenter and customernumber.
         */
        if (substr($line, 0, 1) === 'H') {
            $isNVOA = false;
            $line = explode(SEPERATOR, $line);
            $costcenter = (int) $line[1];
            /**
             * Check if customer number 3011214 exisist. 
             */
            $isNVOA = ((int) $line[7] == 3011214 ? true :  false);
            $found = false;
            
        } 
        /**
         * Else if the line contain an I as first letter.
         * This is practically a copy paste from before.
         */
        elseif (substr($line, 0, 1) === 'I') {
            $line = explode(SEPERATOR, $line);
            $description = $line[4];
            $cost = $line[6];
            $name = $line[7];
            $caseNumber = $line[8];
            foreach ($costcenters as $CostCenter) {
                if ($CostCenter->getCostcenter() == $costcenter) {
                    $CostCenter->addRow($caseNumber, $name, $description, $cost);
                    $found = true;
                }
            }
            if (!($found)) {
                $costcenters[] = new CostCenter($costcenter, $caseNumber, $name, $description, $cost, $isNVOA);
            }
        }

        /**
         * Get next line
         */
        $line = fgets($fp);
    }
    /**
     * Close file
     */
    fclose($fp);
    /**
     * Delete the file.
     */
    unlink("Below400");

} else {
    echo mb_convert_encoding("Filen Below400 existerar inte. D�r av existerar inga tidigare fakturor under 400!<br>", 'UTF-8', 'ISO-8859-1');
}
/**
 * Get current date in yyyy-mm-dd format.
 */
$date = date("ymd");

/**
 * Create Nacka Vatten och Avfall AB invoice file in write mode
 */
$NVOAFP = fopen("K60" . $date . "01.txt", 'w');
/**
 * Create Nacka kommun invoice file in write mode
 */
$NKFP = fopen("K48" . $date . "01.txt", 'w');
/**
 * Create or open Error file in write mode
 */
$errFP = fopen("Error", "w");
/**
 * Create Below400 file in write mode
 */
$below400FP = fopen("Below400", "w");

/**
 * Set default values
 * Used to count Nacka kommun and Nacka Vatten och Avfall AB total cost and total invoices.
 */
$NK48Cost = 0;
$NK48Count = 0;
$NK60Cost = 0;
$NK60Count = 0;

/**
 * Time to loop through all the cost centers.
 */
foreach ($costcenters as $cs) {
    /**
     * Default values
     */
    $line1 = NULL;
    $line2 = NULL;
    $line3 = NULL;
    /**
     * Get all data from costcenter.
     */
    $data = $cs->getData();

    /**
     * get costcenter
     */
    $cc = $data['costcenter'];

    /**
     * A variable for check of total cost for invoice to that costcenter.
     */
    $over400 = false;
    /**
     * Temporary holder for totalCost.
     */
    $tempTotalCost = 0;
    /**
     * Count all costs.
     * Break if it's over 400 kr.
     */
    for ($i = 0; $i < count($data['costs']); $i++) {
        $tempTotalCost += $data['costs'][$i];
        if ($tempTotalCost >= 400) {
            $over400 = true;
            break;
        }
    }

    /**
     * The only difference is the customer number...
     * Why does it need to be a customer number when they are in the same system?... Well well.
     */
    if ($data['isNVOA']) {
        $line1 = "H;$cc;;Samlingsfaktura fr�n Kundserviceenheten;Fakturan avser best�llningar som har utf�rts av Kundserviceenheten;;K60KONTAKT;3011214;\n";
        /**
         * if it's over 400 kr
         * Write the cost to the nk60 file.
         * else write the cost to the below40 file.
         */
        if ($over400) {
            fwrite($NVOAFP, $line1);
            $NK60Count++;
        } else {
            fwrite($below400FP, $line1);
        }
    } else {
        $line1 = "H;$cc;;Samlingsfaktura fr�n Kundserviceenheten;Fakturan avser best�llningar som har utf�rts av Kundserviceenheten;;K48KONTAKT;;\n";
        /**
         * If costcenter is 0.
         * Someone didn't do their job and set a costcenter in Marval.
         * To the error file.
         */
        if ($cc == 0) {
            fwrite($errFP, $line1);
        } else {
            if ($over400) {
                fwrite($NKFP, $line1);
                $NK48Count++;
            } else {
                fwrite($below400FP, $line1);
            }
        }
    }

    for ($i = 0; $i < count($data['costs']); $i++) {
        //å ä ö does not work in UBW in UTF-8, so I am convering it to ANIS from UTF-8 (wtf?)....
        $data['Names'][$i] = mb_convert_encoding($data['Names'][$i], 'ISO-8859-1', 'UTF-8');
        $data['descriptions'][$i] = mb_convert_encoding($data['descriptions'][$i], 'ISO-8859-1', 'UTF-8');
        /**
         * In the invoice, each text row may not be larger than 90 letters.... so it's getting cut off.
         */
        $line2 = "I;" . ($i + 1) . ";;;" . substr($data['descriptions'][$i], 0, 61) . ";1;" . $data['costs'][$i] . ";" . $data['Names'][$i] . ";" . $data['caseNumber'][$i] . ";\n";
        $line3 = "P;" . ($i + 1) . ";;36400;20080;93160;\n";

        /**
         * Writes to file depending of multiple reasons.
         */
        if ($data['isNVOA']) {
            if ($over400) {
                fwrite($NVOAFP, $line2);
                fwrite($NVOAFP, $line3);
                $NK60Cost += $data['costs'][$i];
            } else {
                fwrite($below400FP, $line2);
                fwrite($below400FP, $line3);
            }
        } else {
            if ($cc == 0) {
                fwrite($errFP, $line2);
                fwrite($errFP, $line3);
            } else {
                if ($over400) {
                    fwrite($NKFP, $line2);
                    fwrite($NKFP, $line3);
                    $NK48Cost += $data['costs'][$i];
                } else {
                    fwrite($below400FP, $line2);
                    fwrite($below400FP, $line3);
                }
            }
        }
    }
}

/**
 * Close all files
 */
fclose($below400FP);
fclose($NKFP);
fclose($errFP);
fclose($NVOAFP);
/**
 * If size is zero, remove the file.
 */
if (filesize("K60" . $date . "01.txt") == 0) {
    unlink("K60" . $date . "01.txt");
}
if (filesize("K48" . $date . "01.txt") == 0) {
    unlink("K48" . $date . "01.txt");
}
/**
 * print info.
 */
echo mb_convert_encoding("K48: Antal fakturor: $NK48Count, Totalsumma: $NK48Cost \n", 'UTF-8', 'ISO-8859-1');
echo mb_convert_encoding("K60: Antal fakturor: $NK60Count, Totalsumma: $NK60Cost \n", 'UTF-8', 'ISO-8859-1');

$totalCost = $NK48Cost + $NK60Cost;

echo mb_convert_encoding("\nJugge fakturerar nu f�r: $totalCost", 'UTF-8', 'ISO-8859-1');
