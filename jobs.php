<?php 
ini_set ('display_errors', 1); 
ini_set ('display_startup_errors', 1); 
error_reporting(E_ALL | E_STRICT); 
include('simplehtmldom/simple_html_dom.php');

/**
 * Telecommuting Jobs Scrapper
 * 2012-02-22
 * Author: fredduarte@gmail.com
 */
class Job extends simple_html_dom_node{

    const logger = "errors.log";
    private $ycombinator = array('name'=>'Y Combinator','logo'=>'ycombinator.jpg', 'url'=>'http://news.ycombinator.com/jobs');
    private $craigslist = array('name'=>'Craigslist', 'logo'=>'craigslist.png', 'url'=>'https://www.google.com/search?q=%22Telecommuting+is+ok.%22+PHP+site:craigslist.org');
    private $signals = array('name'=>'37signals', 'logo'=>'37signals.jpg', 'url'=>'http://jobs.37signals.com/jobs/search?term=anywhere');
    private $dice = array('name'=>'Dice', 'logo'=>'dice.jpg', 'url'=>'https://www.google.com/search?q=%22telecommute%22+PHP+site:seeker.dice.com');
    private $twitter = array('name'=>'Twitter', 'logo'=>'twitter.jpg', 'url'=>'https://www.google.com/search?q=telecommute+%22%23php%22++site%3Atwitter.com');

    /* Not working */
    /*
    private $stackOverflow = array('name'=>'Stack Overflow', 'logo'=>'stackoverflow.jpg', 'url'=>'');
    private $github = array('name'=>'github', 'logo'=>'github.jpg', 'url'=>'');
     */


    public function __construct()
    {
        echo '<p>' . date('Y-m-d', time()) . '</p>';

        // ycombinator
        $ycombinatorLinks = $this->ycombinatorParser($this->ycombinator['url']);
        $this->write($this->ycombinator, $ycombinatorLinks);

        // craiglist
        $craigslistLinks = $this->googleParser($this->craigslist['url']);
        $this->write($this->craigslist, $craigslistLinks);

        // 37 signals
        $signalsLinks = $this->signalsParser($this->signals['url']);
        $this->write($this->signals, $signalsLinks);

        // twitter
        $twitterLinks = $this->googleParser($this->twitter['url']);
        $this->write($this->twitter, $twitterLinks);

        // dice
        $diceLinks = $this->googleParser($this->dice['url']);
        $this->write($this->dice, $diceLinks);

    }

    private function twitterParser($url)
    {
        try {
            $html = file_get_html($url); 
            foreach($html->find() as $e) {
                echo $e . '<br>';
            }

            $html->clear();
            unset($html);
        } catch (Exception $e) {
            $this->log($e);
            var_dump('<pre>'.$e->getMessage().'</pre>');
        }

        return $links;
    }

    private function signalsParser($url)
    {
        try {
            $html = file_get_html($url); 

            foreach($html->find('div#job_list a') as $e) {
                // if title is empty it's an unwanted link
                $title =  $e->find('span.title', 0);
                if (!empty($title)) {
                    //$links[] = array($title->plaintext, 'http://jobs.37signals.com'.$e->href);
                    $links[$title->plaintext] = 'http://jobs.37signals.com'.$e->href;
                }
            }

            $html->clear();
            unset($html);
        } catch (Exception $e) {
            $this->log($e);
            var_dump('<pre>'.$e->getMessage().'</pre>');
        }

        return $links;
    }

    private function googleParser($url)
    {
        try {
            $html = file_get_html($url); 

            foreach($html->find('div#ires a') as $e) {
                if ($e->plaintext !== 'Similar' && $e->plaintext !== 'Cached') {
                    $href = explode('q=', $e->href);
                    $href2 = explode('&', $href[1]);
                    $links[$e->plaintext] = $href2[0];
                }
            }

            $html->clear();
            unset($html);
        } catch (Exception $e) {
            $this->log($e);
            var_dump('<pre>'.$e->getMessage().'</pre>');
        }

        return $links;
    }

    private function ycombinatorParser($url)
    {
        try {
            $html = file_get_html($url); 
            $links = array();

            foreach($html->find('td.title a') as $e) {
                if (strstr($e->href, 'http')) {
                    $links[$e->plaintext]  = $e->href;
                } else {
                    $links[$e->plaintext] = 'http://news.ycombinator.com/' . $e->href;
                } 
            }

            $html->clear();
            unset($html);
        } catch (Exception $e) {
            $this->log($e);
            var_dump('<pre>'.$e->getMessage().'</pre>');
        }

        $links = !empty($links) ? $links : null; 
        return $links;
    }

    private function write($data, $links)
    {
        if (empty($links)) {
            return false;
        }

        try {
            if (isset($data['logo'])) {
                echo '<img class="logo" alt="'. $data['name'] . '" src="/images/' . $data['logo'] . '"/>';
            } else {
                echo '<h3>' . $data['name'] . '</h3>';
            }
            echo '<ul>';

            foreach ($links as $title=>$url) {
                echo '<li><a href="' . $url . '">' . $title . '</a></li>';
            } 
            echo '</ul>';
        } catch (Exception $e) {
            $this->log($e);
        }
    }

    private function log($exception) 
    {
        $data = $exception->getFile() . '\n\n' . $exception->getLine() . '\n\n' . $exception->getMessage() . '\n\n';
        error_log('Exception:\n\n' . $data, 3, self::logger);
    }

}

$job = new Job();
