<?
    require_once(dirname(__FILE__) . "/config-2.php");

    // output XML
    header("Content-Type: text/xml; charset=UTF-8");
    
    // output UTF-8
    echo "<?xml version='1.0' encoding='UTF-8'?>\n";

    // infer pubDate
    $pubDate = "2011-02-11-01T00:00:00-05:00"; // default
    $path = parse_url($CDN, PHP_URL_PATH);
    if (preg_match("{/(\d+)/(.+)/}", $path, $matches)) {
        if ($matches[2] == "fall")
            $pubDate = "{$matches[1]}-09-01T00:00:00+00:00";
        else if ($matches[2] == "spring")
            $pubDate = "{$matches[1]}-02-01T00:00:00+00:00";
    }

?>

<bundles>
    <?  
        // get all bookmarks (because SimpleXML doesn't support XPath 2.0's ends-with function)
        $dom = simplexml_load_file("http://cs50.tv/2011/fall/?output=xbel");
        $bookmarks =& $dom->xpath("//bookmark");

        // global array for subtitles and mp4 links
        $subtitles = array(); 
        $keepers = array();
        $days = array();
        $content_id = 0;

        foreach ($bookmarks as &$b) {
            // if files are links with extension .mp4 or .srt, sort to arrays
            if (preg_match("{^$CDN}", $b["href"]) && preg_match("/\.mp4$/", $b["href"]))
                $keepers[] =& $b;
                $days[] =& $b->title;
            if (preg_match("{^$CDN2}", $b["href"]) && preg_match("/\.srt$/", $b["href"]))
                // store the subtitle links themselves
                $subtitles[] =& $b["href"];
        }

        foreach ($keepers as &$k) {

            $link_pieces = explode("/", $k["href"]); 

            $id = $link_pieces[5];
            // get the piece with the week number
            $week_number = $link_pieces[6];

            // get the day 
            $day = substr($link_pieces[7], -5, 1);

            // gets titles
            $steps = ($dom->title) ? array($dom->title) : array(ucwords($id));
            // every instance of <desc>, we get the info
            foreach ($k->xpath("ancestor::folder") as $f)
                $steps[] = $f->title;
            $steps[] = $k->title;

            // this is where actual title text is stored in the array
            $title = $steps[2];

            // get type 
            $id = $steps[1];

            // get the pieces of the link with explode
            
            if ($id == "Problem Sets") {
                $id = "Walkthroughs";
            }    
            
            // begin outputting stuff here
            
            echo "<bundle>";
            // echo "<item sdImg='' hdImg = ''>";
            echo "<labels>";

            // getting the day word from $day
            $dayword = "";
            switch ($day) {
                case "m":
                    $dayword = "Monday";
                    break;
                case "f":
                    $dayword = "Friday";
                    break;
                case "w":
                    $dayword = "Wednesday";
                    break;
                default:
                    $dayword = "Monday";
                    break;
            }

            echo "<label name='week'>".$title."</label>";
            echo "<label name='type'>".ucfirst($id)."</label>";
            echo "<label name='subject'>Computer Science</label>";
            echo "<label name='year'>2011</label>";
            echo "</labels>";
            echo "<title>".$title;
            if ($id == "Lectures") {
                echo ", ".$dayword."</title>";
            }
            else {
                echo "</title>";
            }
            echo "<subtitle/>";
            echo "<description/>";

            echo "<thumbnail>http://cdn.cs50.net/2011/fall/";
            if ($id == "Lectures") {
                echo "lectures/".$week_number."/week".$week_number.$day.".png";
            }
            if ($id == "Seminars") {
                $seminar_title = str_replace(" ", "_", $title);
                echo "seminars/".$week_number."/".$week_number.".png";
            }
            if ($id == "Walkthroughs") {
                echo "psets/".$week_number."/walkthrough".$week_number.".png";
            }
            if ($id == "Sections") {
                echo "sections/".$week_number."/section".$week_number.".png";
            }
            if ($id == "Quizzes") {
                echo "quizzes/".$week_number."/review".$week_number.".png";
            }
            echo "</thumbnail>";
            echo "<video><path>http://cdn.cs50.net/" . htmlspecialchars(substr($k["href"], 15)) . "</path></video>";
            echo "<duration>".$k["duration"]."</duration>";
            echo "<date>MM-DD-YYYY</date>";
            echo "<authors>";
            echo "<author>";
            echo "<first>David</first>";
            echo "<last>Malan</last>";
            echo "</author>";
            echo "</authors>";
            echo "<captions>";
            if ($id == "Lectures") {
                echo "<caption lang='en'>";
                // could not find a good way to determine whether something has subtitles or not
                if ($content_id < 20) {
                    echo $subtitles[$content_id];
                }
                else if ($content_id  > 22) {
                    echo $subtitles[$content_id - 3];
                }
                echo "</caption>";
            }
            echo "</captions>";
            echo "<overlays>";
            if ($id == "Lectures") {
                echo "<overlay type='application/pdf'>";
                echo "<title>Lecture Slides</title>";
                echo "<path>http://cdn.cs50.net/2011/fall/lectures/".$week_number."/week".$week_number.$day.".pdf</path>";
                echo "</overlay>";
                if ($week_number <= 10) {
                    echo "<overlay type='application/pdf'>";
                    echo "<title>Lecture Notes</title>";
                    echo "<path>http://cdn.cs50.net/2011/fall/lectures/".$week_number."/notes".$week_number.$day.".pdf</path>";
                    echo "</overlay>";
                }
                if ($week_number > 0 && $week_number <= 10) {
                    echo "<overlay type='application/pdf'>";
                    echo "<title>Source Code</title>";
                    echo "<path>http://cdn.cs50.net/2011/fall/lectures/".$week_number."/src".$day.".pdf</path>";
                    echo "</overlay>";
                }
            }
            
            if ($id == "Sections") {
                echo "<overlay type='application/pdf'>";
                echo "<title>Section Slides</title>";
                echo "<path>http://cdn.cs50.net/2011/fall/sections/".$week_number."/section".$week_number.".pdf</path>";
                echo "</overlay>";
            }

            if ($id == "Walkthroughs") {
                echo "<overlay type='application/pdf'>";
                echo "<title>standard edition</title>";
                echo "<path>http://cdn.cs50.net/2011/fall/psets/".$week_number."/pset".$week_number.".pdf</path>";
                echo "</overlay>";
                echo "<overlay type='application/pdf'>";
                echo "<title>Walkthrough Slides</title>";
                echo "<path>http://cdn.cs50.net/2011/fall/psets/".$week_number."/walkthrough".$week_number.".pdf</path>";
                echo "</overlay>";
                if ($week_number < 6) {
                    echo "<overlay type='application/pdf'>";
                    echo "<title>Hacker Edition</title>";
                    echo "<path>http://cdn.cs50.net/2011/fall/psets/".$week_number."/hacker".$week_number.".pdf</path>";
                    echo "</overlay>";
                }
            }

            if ($id == "Quizzes") {
                echo "<overlay type='application/pdf'>";
                echo "<title>questions</title>";
                echo "<path>http://cdn.cs50.net/2011/fall/quizzes/".$week_number."/quiz".$week_number.".pdf</path>";
                echo "</overlay>";
                echo "<overlay type='application/pdf'>";
                echo "<title>answers</title>";
                echo "<path>http://cdn.cs50.net/2011/fall/quizzes/".$week_number."/key".$week_number.".pdf</path>";
                echo "</overlay>";
                echo "<overlay type='application/pdf'>";
                echo "<title>Review Session</title>";
                echo "<path>http://cdn.cs50.net/2011/fall/quizzes/".$week_number."/review".$week_number.".pdf</path>";
                echo "</overlay>";
            }

            if ($id == "Seminars") {
                echo "<overlay type='application/pdf'>";
                echo "<title>Review Session</title>";
                echo "<path>http://cdn.cs50.net/2011/fall/seminars/".$week_number."/".$week_number.".pdf</path>";
                echo "</overlay>";
            }
            
            echo "</overlays>";

            echo "</bundle>";

            // increment identifier
            $content_id++;
        }
    ?>
</bundles>
